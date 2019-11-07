<?php
Class WEAPP_User extends WPJAM_Model{
	use WEAPP_Trait;
	
	public static function sync_user($data){
		$data	= array(
			'openid'	=> $data['openId'],
			'unionid'	=> isset($data['unionId'])?$data['unionId']:'',
			'nickname'	=> $data['nickName'],
			'gender'	=> $data['gender'],
			'city'		=> $data['city'],
			'province'	=> $data['province'],
			'country'	=> $data['country'],
			'avatarurl'	=> $data['avatarUrl'],
		);

		$openid	= $data['openid'];

		$user	= parent::get($openid); 

		if($user){
			$data['modified']	= time();
			$result = parent::update($openid, $data);
		}else{
			$data['time']	= $data['modified']	= time();
			$result = parent::insert($data);
		}

		if(is_wp_error($result)){
			return $result;
		}

		return parent::get($openid);
	}

	public static function parse_for_json($weapp_user){
		if(!is_array($weapp_user)){
			$weapp_user = static::get($weapp_user);
		}else{
			$weapp_user = static::get($weapp_user['openid']);
		}

		if(!$weapp_user){
			return [];
		}
		
		$weapp_user_json					= [];
		$weapp_user_json['openid']			= $weapp_user['openid'];
		$weapp_user_json['nickname']		= $weapp_user['nickname'];
		$weapp_user_json['gender']			= intval($weapp_user['gender']);
		$weapp_user_json['avatarurl']		= str_replace('/0', '/132', $weapp_user['avatarurl']);

		$weapp_user_json['language']		= $weapp_user['language'];
		$weapp_user_json['country']			= $weapp_user['country'];
		$weapp_user_json['province']		= $weapp_user['province'];
		$weapp_user_json['city']			= $weapp_user['city'];
		$weapp_user_json['receiver_phone']	= $weapp_user['receiver_phone'] ?? '';
		$weapp_user_json['time']			= intval($weapp_user['time']);
		$weapp_user_json['modified']		= intval($weapp_user['modified']);

		if(empty($weapp_user['nickname']) || empty($weapp_user['avatarurl']) || (time() - $weapp_user['modified'] > DAY_IN_SECONDS * 3)){
			$weapp_user_json['expired']	= true;
		}else{
			$weapp_user_json['expired']	= false;
		}

		if(doing_filter('weapp_user_json')){
			return $weapp_user_json;
		}else{
			return apply_filters('weapp_user_json', $weapp_user_json, $weapp_user['openid']);
		}
	}

	public static function prepare($user){
		global $current_admin_url; 

		if(!is_array($user)){
			$user = self::get($user);
		}

		if(!$user)	return [];
		
		$user['username'] = $user['nickname'];
		if($user['avatarurl']) {
			$avatarurl = str_replace('/0', '/64', $user['avatarurl']);
			$user['username'] = '<img src="'.$avatarurl.'" width="32" />'.$user['username'];
		}

		return $user;
	}

	public static function get_openid_by_access_token($access_token, $type='access_token'){
		$appid = WEAPP_Auth::get_appid($access_token, $type);

		if(is_wp_error($appid))	return $appid;

		if(empty($appid) || ($appid != static::get_appid())){
			return new WP_Error('illegal_appid', 'appid 不匹配！');
		}

		return WEAPP_Auth::get_openid($access_token, $type);
	}

	public static function get_expired_time_by_access_token($access_token, $type='access_token'){
		$appid = WEAPP_Auth::get_appid($access_token, $type);

		if(is_wp_error($appid))	return $appid;

		if(empty($appid) || ($appid != static::get_appid())){
			return new WP_Error('illegal_appid', 'appid 不匹配！');
		}

		return WEAPP_Auth::get_expired_time($access_token, $type);
	}

	public static function get_openid_by_refresh_token($refresh_token){
		return self::get_openid_by_access_token($refresh_token, 'refresh_token');
	}

	public static function generate_access_token($openid, $type='access_token'){
		return WEAPP_Auth::generate($openid, static::get_appid(), $type);
	}

	public static function generate_refresh_token($openid){
		return self::generate_access_token($openid, 'refresh_token');
	}

	protected static $handlers;
	protected static $appid;

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix . 'weapp_' . static::get_appid() . '_users';	
	}

	public static function get_handler(){
		global $wpdb;

		static::$handlers	= (static::$handlers)??array();
		$appid				= static::get_appid();

		if(empty(static::$handlers[$appid])){
			static::$handlers[$appid] =	new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'openid',
				'cache_group'		=> 'weapp_'.static::get_appid().'_users',
				'field_types'		=> array('gender'=>'%d'),
				'searchable_fields'	=> ['nickname','openid'],
				'filterable_fields'	=> ['gender','city','province','country', 'receiver_state', 'receiver_city', 'receiver_district'],
			));
		}

		return static::$handlers[$appid];
	}

	public static function create_table(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;
		
		$table	= self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {

			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`openid` varchar(30)	NOT NULL,
				`unionid` varchar(30)	NOT NULL,
				`nickname` varchar(255)	NOT NULL,
				`gender` int(1) NOT NULL,
				`language` varchar(32) NOT NULL,
				`country` varchar(100)	NOT NULL,
				`province` varchar(100)	NOT NULL,
				`city` varchar(100)	NOT NULL,
				`avatarurl` varchar(511)	NOT NULL,
				`user_id` bigint(20) NOT NULL,
				`phone` varchar(15) NOT NULL,
				`time` int(10) NOT NULL,
				`modified` int(10) NOT NULL,
				PRIMARY KEY	(`openid`),
				KEY `user_idx` (`user_id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}

		do_action('weapp_user_table_created', $table);
	}
}

class WEAPP_Auth{
	public static function generate($openid, $appid, $type='access_token'){
		$access_token	= wp_cache_get($openid, 'weapp_user_'.$type);

		if($access_token !== false){
			wp_cache_delete($openid, 'weapp_user_'.$type);
			wp_cache_delete($access_token, 'weapp_user_'.$type);
		}

		$access_token	= md5(uniqid($openid.$appid));
		$cache_time		= ($type == 'access_token')?DAY_IN_SECONDS:DAY_IN_SECONDS*30;
		$expired_time	= time() + $cache_time;

		wp_cache_set($access_token, compact('openid','appid','expired_time'), 'weapp_user_'.$type, $cache_time);
		wp_cache_set($openid, $access_token, 'weapp_user_'.$type, $cache_time);

		return $access_token;
	}

	public static function get_data($access_token, $type='access_token'){
		$data = wp_cache_get($access_token, 'weapp_user_'.$type);

		if($data === false){
			return new WP_Error('illegal_'.$type, 'Token 非法或已过期！');
		}

		return $data;
	}

	public static function get_openid($access_token, $type='access_token'){
		$data = self::get_data($access_token, $type);

		if(is_wp_error($data)){
			return $data;
		}

		return $data['openid'];
	}

	public static function get_appid($access_token, $type='access_token'){
		$data = self::get_data($access_token, $type);

		if(is_wp_error($data)){
			return $data;
		}

		return $data['appid'];
	}

	public static function get_expired_time($access_token, $type='access_token'){
		$data = self::get_data($access_token, $type);

		if(is_wp_error($data)){
			return $data;
		}

		return $data['expired_time'];
	}



	// public static function revoke($access_token){
	// 	$openid = self::get_openid($access_token);

	// 	if(!is_wp_error($openid)){
	// 		wp_cache_delete($openid, self::$cache_group);
	// 	}
		
	// 	wp_cache_delete($access_token, self::$cache_group);
	// }
}