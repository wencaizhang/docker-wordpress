<?php
class WEIXIN_Message extends WPJAM_Model {
	use WEIXIN_Trait;

	public static function prepare($message, $Response=''){
		$appid	= static::get_appid();
		$data	= array(
			'MsgId'			=>	isset($message['MsgId'])?$message['MsgId']:'',
			'MsgType'		=>	($message['MsgType'])??'',
			'FromUserName'	=>	($message['FromUserName'])??'',
			'CreateTime'	=>	($message['CreateTime'])??'',
			'Content'		=> '',
			'Event'			=> '',
			'EventKey'		=> '',
			'Title'			=> '',
			'Url'			=> '',
			'MediaId'			=> '',
			'Response'		=>	$Response,
			// 'ip'			=>	wpjam_get_ip(),
		);

		$openid	= ($message['FromUserName'])??'';
		$msgType		= isset($message['MsgType'])?strtolower($message['MsgType']):'';


		if($msgType == 'text'){
			$data['Content']	= ($message['Content'])?(string)$message['Content']:'';
		}elseif($msgType == 'image'){
			$data['Url']		= $message['PicUrl'];
			$data['MediaId']	= $message['MediaId'];
		}elseif($msgType == 'location'){
			$location	= array(
				'Location_X'	=>	$message['Location_X'],
				'Location_Y'	=>	$message['Location_Y'],
				'Scale'			=>	$message['Scale'],
				'Label'			=>	$message['Label']
			);
			$data['Content']	= maybe_serialize($location);
			wp_cache_set($openid, $location, 'weixin_location', 600);	// 缓存用户地理位置信息
		}elseif($msgType == 'link'){
			$data['Title']		= $message['Title'];
			$data['Content']	= ($message['Description'])?:'';
			$data['Url']		= $message['Url'];
		}elseif($msgType == 'voice'){
			$data['Url']		= $message['Format'];
			$data['MediaId']	= $message['MediaId'];
			$data['Content']	= !empty($message['Recognition'])?$message['Recognition']:'';
		}elseif($msgType == 'video' || $msgType == 'shortvideo'){
			$data['MediaId']	= $message['MediaId'];
			$data['Url']		= $message['ThumbMediaId'];
		}elseif($msgType == 'event'){
			$data['Event']		= $message['Event'];
			$Event 				= strtolower($message['Event']);
			$data['EventKey']	= !empty($message['EventKey'])?$message['EventKey']:'';
			if($Event == 'location'){
				$location	= array(
					'Location_X'	=>	$message['Latitude'],
					'Location_Y'	=>	$message['Longitude'],
					'Precision'		=>	$message['Precision'],
				);
				$data['Content']	= maybe_serialize($location);
			}elseif ($Event == 'templatesendjobfinish') {
				$data['EventKey']	= $message['Status'];
			}elseif ($Event == 'masssendjobfinish') {
				$data['EventKey']	= $message['Status'];
				$data['MsgId']		= ($message['MsgId'])??(($message['MsgID'])??'');
				// file_put_contents(WP_CONTENT_DIR.'/debug/masssendjobfinish.log',var_export($message,true),FILE_APPEND);
				$data['Content']	= maybe_serialize(array(
					'Status'		=> $message['Status'],
					'TotalCount'	=> $message['TotalCount'],
					'FilterCount'	=> $message['FilterCount'],
					'SentCount'		=> $message['SentCount'],
					'ErrorCount'	=> $message['ErrorCount']
				));	
			}elseif($Event == 'scancode_push' || $Event == 'scancode_waitmsg'){
				$ScanCodeInfo 		= $message['ScanCodeInfo'];
				$data['Title']		= (string)$ScanCodeInfo['ScanType'];
				$data['Content']	= (string)$ScanCodeInfo['ScanResult'];
			}elseif($Event == 'location_select'){
				$SendLocationInfo	= $message['SendLocationInfo'];
				$location	= array(
					'Location_X'	=>	$message['Location_X'],
					'Location_Y'	=>	$message['Location_Y'],
					'Scale'			=>	$message['Scale'],
					'Label'			=>	$message['Label'],
					'Poiname'		=>	$message['Poiname'],
				);
				$data['content']	= maybe_serialize($location);
				wp_cache_set($openid, $location, 'weixin_location', 600);	// 缓存用户地理位置信息
			}elseif($Event == 'pic_sysphoto' || $Event == 'pic_photo_or_album' || $Event == 'pic_weixin'){
				$SendPicsInfo		= $message['SendPicsInfo'];
				$Count 				= (string)$SendPicsInfo['Count'];
				$PicList			= (string)$SendPicsInfo['PicList'];
			}elseif ($Event == 'card_not_pass_check' || $Event == 'card_pass_check') {
				$data['EventKey']	= $message['CardId'];
			}elseif ($Event == 'user_get_card') {
				$data['EventKey']	= $message['CardId'];
				$data['Title']		= $message['UserCardCode'];
				$data['MediaId']	= $message['OuterId'];
				$data['Url']		= $message['IsGiveByFriend'];
				$card	= array(
					'FriendUserName'	=>	$message['FriendUserName'],
					'OldUserCardCode'	=>	$message['OldUserCardCode'],
				);
				$data['content']	= maybe_serialize($card);
			}elseif ($Event == 'user_del_card') {
				$data['EventKey']	= $message['CardId'];
				$data['Title']		= $message['UserCardCode'];
			}elseif ($Event == 'user_view_card') {
				$data['EventKey']	= $message['CardId'];
				$data['Title']		= $message['UserCardCode'];
			}elseif ($Event == 'user_enter_session_from_card') {
				$data['EventKey']	= $message['CardId'];
				$data['Title']		= $message['UserCardCode'];
			}elseif ($Event == 'user_consume_card') {
				$data['EventKey']	= $message['CardId'];
				$data['Title']		= $message['UserCardCode'];
				$data['MediaId']	= $message['ConsumeSource'];
				$card	= array(
					'OutTradeNo'	=>	$message['OutTradeNo'],
					'TransId'		=>	$message['TransId'],
					'LocationName'	=>	$message['LocationName'],
					'StaffOpenId'	=>	$message['StaffOpenId'],
				);
				$data['content']	= maybe_serialize($card);
			}elseif($Event == 'submit_membercard_user_info'){
				$data['EventKey']	= $message['CardId'];
				$data['Title']		= $message['UserCardCode'];
			}elseif ($Event == 'wificonnected') {
				$data['EventKey']	= $message['PlaceId'];
				$data['Title']		= $message['DeviceNo'];
				$data['MediaId']	= $message['ConnectTime'];
				$wificonnected	= array(
					'ExpireTime'	=>	$message['ExpireTime'],
					'VendorId'		=>	$message['VendorId'],
				);
				$data['content']	= maybe_serialize($wificonnected);
			}elseif ($Event == 'shakearoundusershake') {
				$data['Title']		= maybe_serialize($message['ChosenBeacon']);
				$data['Content']	= maybe_serialize($message['AroundBeacons']);
			}elseif ($Event == 'poi_check_notify') {
				$data['EventKey']	= $message['UniqId'];
				$data['Title']		= $message['PoiId'];
				$data['MediaId']	= $message['Result'];
				$data['Content']	= $message['Msg'];
			}elseif($Event == 'qualification_verify_success' || $Event == 'naming_verify_success' || $Event == 'annual_renew' || $Event == 'verify_expired'){
				$data['Title']		= $message['ExpiredTime'];
			}elseif($Event == 'qualification_verify_fail' || $Event == 'naming_verify_fail'){
				$data['Title']		= $message['FailTime'];
				$data['Content']	= $message['FailReason'];
			}elseif($Event == 'kf_create_session' || $Event == 'kf_close_session'){
				$data['Title']		= $message['KfAccount'];
			}elseif($Event == 'kf_switch_session' || $Event == 'kf_close_session'){
				$data['Title']		= $message['FromKfAccount'];
				$data['Content']	= $message['ToKfAccount'];
			}
		}

		return $data;
	}
	
	public static function insert($data){
		$appid		= static::get_appid();

		$data['appid']	= $appid;

		if(!wp_using_ext_object_cache() || count($data) <= 5){
			return parent::insert($data); 
		}

		$messages	= wp_cache_get('weixin_messages','weixin_messages');
		$messages	= ($messages === false)?[]:$messages;
		
		$messages[]	= $data;

		if(count($messages) < 10){
			wp_cache_set('weixin_messages', $messages, 'weixin_messages', 3600);
		}else{	// 达到了 10 个用户或者过了5分钟再去写数据库，
			wp_cache_delete('weixin_messages', 'weixin_messages');
			parent::insert_multi($messages);
		}
	}

	public static function update($id, $data){
		return parent::update($id, $data);
	}

	public static function delete($id){
		return parent::delete($id);
	}

	public static function bulk_delete($ids){
		return self::delete_multi($ids);
	}

	public static function send($openid, $content, $type='text', $kf_account=''){
		if(empty($content)) return;

		if($type == 'img'){
			$counter = 0;

			$articles = $article	= array();

			$img_reply_query 	= new WP_Query(array('post__in'=>explode(',', $content),'orderby'=>'post__in','post_type'=>'any'));

			if($img_reply_query->have_posts()){
				while ($img_reply_query->have_posts()) {
					$img_reply_query->the_post();

					$article['title']		= apply_filters('weixin_title', get_the_title()); 
					$article['description']	= apply_filters('weixin_description', get_post_excerpt( '',apply_filters( 'weixin_description_length', 150 ) ) );
					$article['url']			= add_query_arg('weixin_openid', $openid, apply_filters('weixin_url', get_permalink()));

					if($counter == 0){
						$article['picurl'] = wpjam_get_post_thumbnail_url('', array(640,320));
					}else{
						$article['picurl'] = wpjam_get_post_thumbnail_url('', array(80,80));
					}
					$counter ++;
					$articles[] = $article;
				}
				$type		= 'news';
				$content	= $articles;
			}
			wp_reset_query();
		}elseif($type == 'img2'){
			$articles = $article	= array();

			$items = explode("\n\n", str_replace("\r\n", "\n", $content));
			foreach ($items as $item ) {
				$lines = explode("\n", $item);
				$article['title']		= isset($lines[0])?$lines[0]:'';
				$article['description']	= isset($lines[1])?$lines[1]:'';
				$article['picurl']		= isset($lines[2])?$lines[2]:'';
				$article['url']			= isset($lines[3])?$lines[3]:'';

				$articles[] = $article;
			}
			$type		= 'news';
			$content	= $articles;
		}elseif($type == 'news'){
			$material	= weixin()->get_material($content, 'news');
			if(is_wp_error($material)){
				return $material;
			}else{
				$articles = $article	= array();
				
				foreach ($material as $news_item) {
					$article['title']		= $news_item['title'];
					$article['description']	= $news_item['digest'];
					$article['picurl']		= $news_item['thumb_url'];
					$article['url']			= $news_item['url'];

					$articles[] = $article;
				}
				$type		= 'news';
				$content	= $articles;
			}
		}elseif($type == 'wxcard'){
			$items 		= explode("\n", $content);
			$card_id	= ($items[0])??'';
			$outer_id	= ($items[1])??'';
			$code		= ($items[2])??'';

			$card_ext	= weixin_robot_generate_card_ext(compact('card_id','outer_id','code','openid'));

			$data	= [
				'touser'	=>$openid,
				'msgtype'	=>'wxcard',
				'wxcard'	=>compact('card_id','card_ext')
			];
		}elseif($type == 'text'){
			$content	= compact('content');
		}

		$data	= [
			'touser'	=> $openid,
			'msgtype'   => $type,
			$type 		=> $content,
		];

		if($kf_account){
			$data['customservice']	= compact('kf_account');
		}

		return weixin()->send_custom_message($data);
	}

	public static function can_send($openid){
		if(self::Query()->where('appid',static::get_appid())->where('FromUserName',$openid)->where_gt('CreateTime', WEIXIN_CUSTOM_SEND_LIMIT)->get_row()){
			return true;
		}else{
			return false;
		}
	}

	public static function get_can_send_users(){
		return self::Query()->where('appid',static::get_appid())->where_gt('CreateTime', time()-HOUR_IN_SECONDS)->group_by('FromUserName')->get_col('FromUserName');
	}

	public static function get_user_location($openid){	// 获取用户的最新的地理位置并缓存10分钟。
		$appid		= static::get_appid();
		$location	= wp_cache_get($openid, 'weixin_location_'.$appid);
		if($location === false){
			$location	= self::Query()->where_not('Content', '')->where('FromUserName',$openid)->where_gt('CreateTime', time()-HOUR_IN_SECONDS)->where_fragment("MsgType='Location' OR (MsgType ='Event' AND Event='LOCATION')")->order_by('CreateTime')->order('DESC')->get_var('Content');

			// $sql = $wpdb->prepare("SELECT  Content FROM {$wpdb->weixin_messages} WHERE Content != '' AND (MsgType='Location' OR (MsgType ='Event' AND Event='LOCATION')) AND FromUserName=%s AND CreateTime>%d ORDER BY CreateTime DESC LIMIT 0,1;",$openid,$timestamp);
			// file_put_contents(WP_CONTENT_DIR.'/debug/location.log',$sql);

			// $location	= $wpdb->get_var($sql);

			$location	= maybe_unserialize($location);
			wp_cache_set($openid, $location, 'weixin_location', 600);
		}
		return $location;
	}

	protected static $handler;
	protected static $appid;

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_messages';
		// return $wpdb->base_prefix.'weixin_'.static::get_appid().'_messages';
		// return $wpdb->prefix.'weixin_messages';
	}

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'cache'				=> false,
				'field_types'		=> ['id'=>'%d','MsgId'=>'%d','CreateTime'=>'%d'],
				'searchable_fields'	=> [],
				'filterable_fields'	=> ['MsgType','Response','FromUserName'],
			));
		}

		return static::$handler;
	}

	public static function create_table(){
		global $wpdb;

		$table = static::get_table();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if($wpdb->get_var("show tables like '".$table."'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS ".$table." (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` varchar(32) NOT NULL,
				`MsgId` bigint(20) NOT NULL,
				`FromUserName` varchar(30) NOT NULL,
				`MsgType` varchar(10) NOT NULL,
				`CreateTime` int(10) NOT NULL,
				`Content` longtext NOT NULL,
				`Event` varchar(50) NOT NULL,
				`EventKey` varchar(50) NOT NULL,
				`Title` text NOT NULL,
				`Url` varchar(255) NOT NULL,
				`MediaId` varchar(500) NOT NULL,
				`Response` varchar(255) NOT NULL,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("ALTER TABLE `".$table."`
				ADD KEY `MsgType` (`MsgType`),
				ADD KEY `CreateTime` (`CreateTime`),
				ADD KEY `Event` (`Event`);");
		}
	}
}