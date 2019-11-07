<?php
class WEIXIN_ReplySetting extends WPJAM_Model{
	use WEIXIN_Trait;

	public static function get_custom_replies($match=null){
		if($match == 'all'){
			$custom_replies	= get_transient('weixin_custom_replies');
			if($custom_replies === false){
				$custom_replies	= array(
					'full'		=> self::get_custom_replies('full'),	// 完全匹配
					'prefix'	=> self::get_custom_replies('prefix'),	// 前缀匹配
					'fuzzy'		=> self::get_custom_replies('fuzzy')	// 模糊匹配
				); 

				set_transient('weixin_custom_replies', $custom_replies, DAY_IN_SECONDS);
			}

			return $custom_replies;
		}elseif($match == null){
			$custom_replies = self::get_custom_replies('all');
			return array_merge(...array_values($custom_replies));
		}

		$custom_replies_original	= self::Query()->where('appid', static::get_appid())->where('status', 1)->where('match', $match)->get_results();
		
		$custom_replies = array(); 
		if($custom_replies_original){
			foreach ($custom_replies_original as $custom_reply ) {
				
				$key = strtolower(trim($custom_reply['keyword']));
				if(strpos($key,',')){
					foreach (explode(',', $key) as $new_key) {
						$new_key = strtolower(trim($new_key));
						if($new_key !== ''){
							$custom_replies[$new_key][] = $custom_reply;
						}
					}
				}else{
					$custom_replies[$key][] = $custom_reply;
				}
			}
		}

		if($match == 'full'){
			$builtin_replies	= self::get_builtin_replies($match);

			foreach (array('[too-long]','[default]') as $keyword) {	// 将这两个作为函数回复写入到自定义回复中
				if(isset($custom_replies[$keyword])) continue;

				if(isset($builtin_replies[$keyword])){
					$custom_reply = [];

					$custom_reply['keyword']	= $keyword;
					$custom_reply['reply']		= $builtin_replies[$keyword]['function'];
					$custom_reply['type']		= 'function';

					$custom_replies[$keyword][]	= $custom_reply;
				}
			}
		}

		// 按照键的长度降序排序
		uksort($custom_replies, function ($v, $w){
			return (mb_strwidth($v) <=> mb_strwidth($w));
		});

		return $custom_replies;
	}

	public static function get_builtin_replies($type = 'all'){
		$builtin_replies = get_transient('weixin_builtin_replies');

		if($builtin_replies === false){
			$builtin_replies = array();
			
			foreach ([
				'[voice]', 
				'[location]', 
				'[image]', 
				'[link]', 
				'[video]', 
				'[shortvideo]',
				'[emotion]'
			] as $keyword){
				$builtin_replies[$keyword]	= array(
					'type'		=>'full',	
					'reply'		=>'默认回复',	
					'method'	=>'default_reply'
				);
			}

			foreach ([
				'[view]', 
				'[view_miniprogram]',
				'[scancode_push]', 
				'[scancode_waitmsg]', 
				'[location_select]', 
				'[pic_sysphoto]', 
				'[pic_photo_or_album]',
				'[pic_weixin]',
				'[templatesendjobfinish]',

				'[kf_create_session]',
				'[kf_close_session]',
				'[kf_switch_session]',
				
				'[user_get_card]', 
				'[user_del_card]', 
				'[card_pass_check]', 
				'[card_not_pass_check]', 
				'[user_view_card]', 
				'[user_enter_session_from_card]', 
				'[card_sku_remind]', 
				'[user_consume_card]',
				'[submit_membercard_user_info]',

				'[masssendjobfinish]',
				'[templatesendjobfinish]',

				'[poi_check_notify]',
				'[wificonnected]',
				'[shakearoundusershake]'

			] as $keyword){
				$builtin_replies[$keyword]	= [
					'type'		=> 'full',	
					'reply'		=> '',	
				];
			}

			if(weixin_get_type() == 4){
				$builtin_replies['event-location']	= [
					'type'		=>'full',	
					'reply'		=>'获取用户地理位置',
					'method'	=>'location_event_reply'
				];
			}

			$builtin_replies['subscribe']	= [
				'type'		=>'full',	
				'reply'		=>'用户订阅',
				'method'	=>'subscribe_reply'
			];

			$builtin_replies['unsubscribe']	= [
				'type'		=>'full',	
				'reply'		=>'用户取消订阅',
				'method'	=>'unsubscribe_reply'
			];

			$builtin_replies['scan']			= [
				'type'		=>'full',
				'reply'		=>'扫描带参数二维码',
				'method'	=>'scan_reply'
			];

			foreach ([
				'[qualification_verify_success]',
				'[qualification_verify_fail]',
				'[naming_verify_success]',
				'[naming_verify_fail]',
				'[annual_renew]',
				'[verify_expired]'
			] as $keyword) {
				$builtin_replies[$keyword]	= [
					'type'		=>'full',	
					'reply'		=>'微信认证回复',	
					'method'	=>'verify_reply'
				];
			}

			$builtin_replies = apply_filters('weixin_builtin_reply', $builtin_replies);

			set_transient('weixin_builtin_replies',	$builtin_replies, HOUR_IN_SECONDS);
		}

		$type = (trim($type))?trim($type):'all';

		if($type == 'all'){
			return $builtin_replies;
		}else{
			return array_filter($builtin_replies, function($builtin_reply) use($type){
				return $builtin_reply['type'] == $type;
			});
		}
	}

	public static function get_default_replies(){
		return array(
			'[subscribe]'		=> ['title'=>'用户关注时',	'value'=>'欢迎关注！'],
			'[event-location]'	=> ['title'=>'进入服务号',	'value'=>'欢迎再次进来！'],
			'[default]'			=> ['title'=>'没有匹配时',	'value'=>'抱歉，没有找到相关的文章，要不你更换一下关键字，可能就有结果了哦 :-)'],
			'[too-long]'		=> ['title'=>'文本太长时',	'value'=>'你输入的关键字太长了，系统没法处理了，请等待公众账号管理员到微信后台回复你吧。'],
			'[emotion]'			=> ['title'=>'发送表情',		'value'=>'已经收到你的表情了！'],
			'[voice]'			=> ['title'=>'发送语音',		'value'=>''],
			'[location]'		=> ['title'=>'发送位置',		'value'=>''],
			'[image]'			=> ['title'=>'发送图片',		'value'=>''],
			'[link]'			=> ['title'=>'发送链接',		'value'=>'已经收到你分享的信息，感谢分享。'],
			'[video]'			=> ['title'=>'发送视频',		'value'=>'已经收到你分享的信息，感谢分享。'],
			'[shortvideo]'		=> ['title'=>'发送短视频',	'value'=>'已经收到你分享的信息，感谢分享。'],
		);
	}

	public static function get($id){
		$reply	= parent::get($id);
		
		if($reply){
			$type	= $reply['type'] ?? 'text';

			$reply[$type]	= maybe_unserialize($reply['reply']);
		}

		return $reply;
	}

	public static function delete($id){
		delete_transient('weixin_custom_replies');
		delete_transient('weixin_builtin_replies');
		return parent::delete($id);
	}

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_replies';
	}

	protected static $handler;
    protected static $appid;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'cache_key'			=> 'appid',
				'field_types'		=> ['id'=>'%d'],
				'searchable_fields'	=> ['keyword', 'reply'],
				'filterable_fields'	=> ['match','type','status'],
			));
		}
		
		return static::$handler;
	}

	public static function create_table(){
		global $wpdb;

		$table = self::get_table();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if($wpdb->get_var("show tables like '".$table."'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS {$table} (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` varchar(32) NOT NULL,
				`keyword` varchar(255) NOT NULL,
				`match` varchar(10) NOT NULL default 'full',
				`reply` text NOT NULL,
				`status` int(1) NOT NULL default '1',
				`time` datetime NOT NULL default '0000-00-00 00:00:00',
				`type` varchar(10) NOT NULL default 'text',
				PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `match` (`match`),
				ADD KEY `status` (`status`),
				ADD KEY `type` (`type`);");
		}
	}
}

