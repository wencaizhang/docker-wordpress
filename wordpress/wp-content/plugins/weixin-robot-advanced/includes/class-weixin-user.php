<?php
class WEIXIN_User extends WPJAM_Model {
	use WEIXIN_Trait;

	public static function get($openid='', $force=false){
		if(!$openid ) $openid = self::get_current_openid();				// 如果没有提供 openid 从 cookie 里面获取

		if(is_wp_error($openid)){
			return $openid;
		}

		if(!is_string($openid)){
			trigger_error(var_export($openid, true));
		}

		if(!$openid || strlen($openid) < 28 || strlen($openid) > 34)  return false;

		$weixin_user	= parent::get($openid);

		$force			= $force && (weixin_get_type() >=3);	// 只有认证订阅号和认证服务号才有 force 选项
		if($weixin_user && !$force){
			$weixin_subscribes	= wp_cache_get('weixin_subscribes', self::get_cache_group());
			if($weixin_subscribes){
				$weixin_subscribe_users	= $weixin_subscribes['users'];
				if(isset($weixin_subscribe_users[$openid])){
					$weixin_user['subscribe']	= $weixin_subscribe_users[$openid]['subscribe'];
				}
			}
			return $weixin_user;
		}

		if(weixin_get_type() >= 3){

			if(wp_cache_get($openid, 'weixin_user_lock') === false){
				wp_cache_set($openid, 1, 'weixin_user_lock', 1);		// 1 秒的内存锁，防止重复远程请求微信用户资料
				
				$user_info = weixin()->get_user_info($openid);

				wp_cache_delete($openid, self::get_cache_group());

				if(!is_wp_error($user_info)){
					if($user_info['subscribe'] == 1){
						$user_info	= self::prepare_data($user_info);

						self::insert($user_info);
					}else{
						if($weixin_user){
							self::update($openid, ['subscribe'=>0]);
						}
					}
				}
			}
		}

		return parent::get($openid); 
	}

	public static function sync($data){
		if(!wp_using_ext_object_cache()) {
			return parent::insert($data);
		}

		$openid	= $data['openid'];

		wp_cache_delete($openid, self::get_cache_group());

		$users	= wp_cache_get('weixin_subscribes', self::get_cache_group());

		if($users === false){
			$users	= ['time'=>time(),'users'=>[]];	
		}
		
		$users['users'][$openid]	= $data;

		if(count($users['users']) < 20 && (time()-$users['time'] < 300)){
			wp_cache_set('weixin_subscribes', $users, self::get_cache_group(), DAY_IN_SECONDS);
		}else{	// 达到了 20 个用户或者过了5分钟再去写数据库
			
			wp_cache_delete('weixin_subscribes', self::get_cache_group());

			parent::insert_multi(array_values($users['users']));
		}
	} 

	public static function subscribe($openid){
		self::sync(['subscribe'=>1, 'openid'=>trim($openid), 'unsubscribe_time'=>0]);
		do_action('weixin_user_subscribe', $openid);
	}

	public static function unsubscribe($openid){
		self::sync(['subscribe'=>0, 'openid'=>trim($openid), 'unsubscribe_time'=>time()]);
		do_action('weixin_user_unsubscribe', $openid);
	}

	public static function batch_get_user_info($openids, $force=false){
		$openids = array_unique($openids);
		$openids = array_filter($openids);
		$openids = array_values($openids);

		if($force === false){	// 先从内存和数据库中取
			$timestamp	= time() - MONTH_IN_SECONDS*3;

			$users	= self::get_ids($openids);
			$users	= array_filter($users, function($user){
				return (empty($user['subscribe']) || ($user['subscribe'] && isset($user['nickname'])));
			});

			if(count($users) >= count($openids)){
				$nonupdated_users	= array_filter($users, function($user)use($timestamp){
					return (empty($user['last_update']) || $user['last_update'] < $timestamp);
				});

				if(!$nonupdated_users){
					return $users;
				}
			}
		}

		$users = weixin()->batch_get_user_info($openids);	// 只要一个没有，或者太久，就全部到微信服务器取一下，反正都是一次 http request 

		if(is_wp_error($users)){
			return $users;
		}
		
		if($users && isset($users['user_info_list'])){
			$users	= $users['user_info_list'];

			$users	= array_map("self::prepare_data", $users);

			if($subscribe_users	= array_filter($users, function($user){ return $user['subscribe']; })){
				parent::insert_multi($subscribe_users);
			}

			// wpjam_print_R($subscribe_users);

			if($unsubscribe_users	= array_filter($users, function($user){ return !$user['subscribe']; })){
				parent::insert_multi($unsubscribe_users);
			}

			// wpjam_print_R($unsubscribe_users);
		}

		return self::get_ids($openids);
	}

	public static function get_blacklist(){
		return weixin()->get_blacklist();
	}

	public static function prepare_data($data){

		if($data && $data['subscribe'] == 1){
			$data['nickname']	= wpjam_strip_invalid_text(substr($data['nickname'], 0, 254));
			$data['city']		= wpjam_strip_invalid_text(substr($data['city'], 0, 254));
			$data['province']	= wpjam_strip_invalid_text(substr($data['province'], 0, 254));
			$data['country']	= wpjam_strip_invalid_text(substr($data['country'], 0, 254));
		}

		$data['last_update']	= time();

		if(isset($data['tagid_list']) && is_array($data['tagid_list'])){
			$data['tagid_list']	= implode(',', $data['tagid_list']);
		}else{
			$data['tagid_list']	= '';
		}

		unset($data['groupid']);
		// unset($data['subscribe_scene']);
		// unset($data['qr_scene']);
		// unset($data['qr_scene_str']);

		return $data;
	}

	public static function get_user_location($openid){	// 获取用户的最新的地理位置并缓存10分钟。
		
		$location	= wp_cache_get($openid, 'weixin_location');
		if($location === false){
			$location	= self::Query()->where_not('Content', '')->where('FromUserName',$openid)->where_gt('CreateTime', time()-HOUR_IN_SECONDS)->where_fragment("MsgType='Location' OR (MsgType ='Event' AND Event='LOCATION')")->order_by('CreateTime')->order('DESC')->get_var('Content');

			$location	= maybe_unserialize($location);
			wp_cache_set($openid, $location, 'weixin_location', 600);
		}
		return $location;
	}

	public static function generate_access_token($openid, $setcookie=false){
		$access_token	= wp_cache_get($openid, 'weixin_user_access_token');

		if($access_token !== false){
			wp_cache_delete($openid, 'weixin_user_access_token');
			wp_cache_delete($access_token, 'weixin_user_access_token');
		}

		$appid			= static::get_appid();
		$access_token	= md5(uniqid($openid.$appid));

		wp_cache_set($access_token, ['appid'=>$appid, 'openid'=>$openid], 'weixin_user_access_token', DAY_IN_SECONDS);
		wp_cache_set($openid, $access_token, 'weixin_user_access_token', DAY_IN_SECONDS);

		if($setcookie){
			self::set_access_token_cookie($access_token);
		}

		return $access_token;
	}

	public static function add_access_token($url){

		if(weixin_get_type() == 4) return $url;
		
		global $weixin_reply;

		return add_query_arg('weixin_access_token', WEIXIN_User::generate_access_token($weixin_reply->get_openid()), $url);
	}

	public static function get_current_openid(){
		$access_token	= '';
		
		if(weixin_get_type() < 4 && isset($_GET['weixin_access_token'])){
			$access_token	= $_GET['weixin_access_token'];
		}elseif (isset($_COOKIE['weixin_access_token'])) {
			$access_token	= $_COOKIE['weixin_access_token'];
		}
		
		return self::get_openid_by_access_token($access_token);
	}

	public static function get_openid_by_access_token($access_token=''){
		if(empty($access_token)){
			return new WP_Error('empty_access_token', 'Access Token 不能为空！');
		}

		$data	= wp_cache_get($access_token, 'weixin_user_access_token');
		
		if($data === false){
			return new WP_Error('illegal_access_token', 'Access Token 非法或已过期！');
		}

		$appid	= static::get_appid();
		if($data['appid'] != $appid){
			return new WP_Error('illegal_appid', 'appid 不匹配！');
		}

		return $data['openid'];
	}

	public static function set_access_token_cookie($access_token){
		if(empty($access_token)){
			return;
		}

		$expiration	= time() + DAY_IN_SECONDS;
		$secure		= is_ssl();

		setcookie('weixin_access_token', $access_token, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure, true);

	    if ( COOKIEPATH != SITECOOKIEPATH ){
	        setcookie('weixin_access_token', $access_token, $expiration, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, true);
	    }

	    $_COOKIE['weixin_access_token'] = $access_token;
	}

	public static function update_user_by_oauth($oauth){
		$access_token	= $oauth['access_token'];
		$openid			= $oauth['openid'];

		$oauth_userifo	= weixin()->get_oauth_userifo($openid, $access_token);

		if(is_wp_error($oauth_userifo)){
			return false;
		}

		$user	= self::get($openid, true);
		$user	= $user ?: ['subscribe'=>0, 'openid'=>trim($openid)];

		$user['nickname']	= $oauth_userifo['nickname'];
		$user['sex']		= $oauth_userifo['sex'];
		$user['province']	= $oauth_userifo['province'];
		$user['city']		= $oauth_userifo['city'];
		$user['country']	= $oauth_userifo['country'];
		$user['headimgurl']	= $oauth_userifo['headimgurl'];
		$user['privilege']	= serialize($oauth_userifo['privilege']);
		$user['unionid']	= $oauth_userifo['unionid'] ?? ($oauth['unionid'] ?? '');

		return self::insert($user);
	}

	public static function get_oauth($code=''){
		if($code){
			$oauth	= weixin()->get_oauth_access_token($code);
			if(is_wp_error($oauth)){
				return $oauth;
			}

			$openid = $oauth['openid'];
		}else{
			$openid	= self::get_current_openid();

			if(is_wp_error($openid)){
				return false;
			}

			$oauth = wp_cache_get($openid, 'weixin_user_oauth');
			if($oauth !== false){
				return $oauth;	// 内存中直接返回
			}

			if($oauth['expires_in'] < time()){	// 内存中已经过期
				if(empty($oauth['refresh_token'])){
					return false;
				}

				$oauth	= weixin()->refresh_oauth_access_token($openid, $oauth['refresh_token']); // 	刷新 access token
				if(is_wp_error($oauth)){
					return false;
				}
			}
		}

		self::generate_access_token($openid, $setcookie=true);

		if($oauth['scope'] == 'snsapi_userinfo'){
			self::update_user_by_oauth($oauth);

			$oauth['expires_in']	= $oauth['expires_in'] + time() - 600;
			wp_cache_set($openid, $oauth, 'weixin_user_oauth', DAY_IN_SECONDS*25);
		}

		return $oauth;
	}

	public static function require_oauth($scope='snsapi_base'){
		$openid	= self::get_current_openid();

		if(is_wp_error($openid)){
			return true;
		}

		if($scope == 'snsapi_base'){
			return false;
		}elseif($scope == 'snsapi_userinfo'){
			if($oauth = self::get_oauth()){
				return false;
			}else{
				return true;
			}
		}
	} 

	public static function oauth_request($scope=''){
		global $weixin_did_oauth;
		
		if(isset($weixin_did_oauth)){	// 防止重复请求
			return;
		}
			
		$weixin_did_oauth	= true;

		if(!isset($_SESSION)){
			session_start();
		}

		if(isset($_GET['code']) && isset($_GET['state']) ){		// 微信 OAuth 请求

			if($_GET['code'] == 'authdeny'){
				wp_die('用户拒绝');
			}

			if(isset($_SESSION['weixin_scope'])){

				if (!wp_verify_nonce($_GET['state'], $_SESSION['weixin_scope'] ) ) {
					wp_die("非法操作");
				}

				if(self::require_oauth($_SESSION['weixin_scope'])){

					$oauth	= self::get_oauth($_GET['code']);

					if(is_wp_error($oauth)){
						wp_die($oauth);
					}
				}
			}

			wp_redirect(self::get_current_page_url($for_oauth=true));

			exit;		
		}else{
			$scope = $scope ?: (isset($_GET['get_userinfo'])?'snsapi_userinfo':'snsapi_base');

			if(self::require_oauth($scope)){
				$_SESSION['weixin_scope'] = $scope;

				wp_redirect(weixin()->get_oauth_redirect_url($scope, self::get_current_page_url($for_oauth=true)));

				exit;	
			}
		}
	}

	public static function redirect(){	// 微信活动跳转，用于支持第三方活动
		if(empty($_GET['weixin_force_subscribe']) && empty($_GET['weixin_redirect']) ){
			return;
		}

		self::oauth_request('snsapi_userinfo'); 

		$openid		= self::get_current_openid();
		$user		= WEIXIN_User::get($openid);
		$subscribe	= ($user && $user['subscribe'])?1:0;

		if(!$subscribe && isset($_GET['weixin_force_subscribe'])){
			wp_die('必须关注微信号','未关注');
		}

		if(!empty($_GET['weixin_redirect'])){
			$redirect		= $_GET['weixin_redirect'];
			$redirect_host	= parse_url($redirect, PHP_URL_HOST);
			$campaign_hosts	= get_option('weixin_'.static::get_appid().'_campaigns');

			if($campaign_hosts){
				$campaign_hosts	= array_map(function($campaign_host){ return parse_url($campaign_host, PHP_URL_HOST); }, $campaign_hosts);
			}

			if(!$campaign_hosts || !in_array($redirect_host, $campaign_hosts)){
				wp_die('该域名未授权，不能跳转！','未授权');
			}

			if(isset($_GET['verify'])){
				$verify		= md5(static::get_appid().$openid);
				$redirect	= str_replace('[openid]', $openid, $redirect);		// 替换 openid
				$redirect	= add_query_arg(compact('subscribe','verify'), $redirect);	// 告诉第三方当前用户是否订阅
			}else{
				$nickname	= urlencode($user['nickname']);
				$headimgurl	= urlencode($user['headimgurl']);
				$sex		= $user['sex'];
				$redirect	= add_query_arg(compact('subscribe','openid','nickname','headimgurl','sex'), $redirect);
			}
			
			wp_redirect($redirect);
			exit;
		}
	}

	public static function get_current_page_url($oauth=false){
		return self::remove_query_arg(wpjam_get_current_page_url(), $oauth);
	}

	public static function remove_query_arg($url, $oauth=false){
		if($oauth){
			$query_args	= array('code', 'state', 'get_userinfo', 'get_openid', 'weixin_oauth', 'nsukey');
		}else{
			$query_args	= array('weixin_openid', 'weixin_access_token', 'isappinstalled', 'from', 'weixin_refer','nsukey');
		}
		return remove_query_arg( $query_args, $url ); 
	}

	protected static $handlers;
	protected static $appid;

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_'.static::get_appid().'_users';
	}

	public static function get_handler(){
		static::$handlers	= (static::$handlers)??array();
		$appid				= static::get_appid();

		if(empty(static::$handlers[$appid])){
			static::$handlers[$appid] = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'openid',
				'cache_group'		=> 'weixin_user_'.static::get_appid(),
				'field_types'		=> ['subscribe'=>'%d','subscribe_time'=>'%d','unsubscribe_time'=>'%d','sex'=>'%d','credit'=>'%d','exp'=>'%d','last_update'=>'%d'],
				'searchable_fields'	=> ['openid', 'nickname'],
				'filterable_fields'	=> ['country','province','city','sex','subscribe_scene'],
			));
		}
		
		return static::$handlers[$appid];
	}

	public static function create_table(){
		global $wpdb;

		$table = self::get_table();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if($wpdb->get_var("show tables like '".$table."'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS {$table} (
				`openid` varchar(30) NOT NULL,
				`nickname` varchar(255) NOT NULL,
				`subscribe` int(1) NOT NULL default '1',
				`subscribe_time` int(10) NOT NULL,
				`unsubscribe_time` int(10) NOT NULL,
				`sex` int(1) NOT NULL,
				`city` varchar(255) NOT NULL,
				`country` varchar(255) NOT NULL,
				`province` varchar(255) NOT NULL,
				`language` varchar(255) NOT NULL,
				`headimgurl` varchar(255) NOT NULL,
				`tagid_list` text NOT NULL,
				`privilege` text NOT NULL,
				`unionid` varchar(30) NOT NULL,
				`remark` text NOT NULL,
				`subscribe_scene` varchar(32) NOT NULL,
				`qr_scene` int(6) NOT NULL,
				`qr_scene_str` varchar(64) NOT NULL,
				`credit` int(10) NOT NULL,
				`exp` int(10) NOT NULL,
				`last_update` int(10) NOT NULL,
				PRIMARY KEY  (`openid`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `subscribe_time` (`subscribe_time`),
				ADD KEY `subscribe` (`subscribe`),
				ADD KEY `last_update` (`last_update`);");
		}
	}
}