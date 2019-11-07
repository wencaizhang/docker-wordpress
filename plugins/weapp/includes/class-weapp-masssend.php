<?php
add_action('weapp_scheduled_send_template_job', function($job_id){
	WEAPP_MasssendJob::cron($job_id);
	if(!is_admin()){
		exit;	
	}
});

Class WEAPP_MasssendJob extends WPJAM_Model{
	use WEAPP_Trait;

	const STATUS_CREATED	= 0; // 等待群发
	const STATUS_SENDING	= 1; // 正在群发
	const STATUS_FINISHED	= 2; // 发送完成
	const STATUS_CANCELED	= 3; // 发送取消

	public static function finish($job_id){
		$data['status']		= self::STATUS_FINISHED;
		$data['end_time']	= time();

		return self::update($job_id, $data);
	}

	public static function cron($job_id){
		$job	= self::get($job_id);
		if(empty($job) || $job['status'] != self::STATUS_SENDING){
			return;
		}

		$appid	= $job['appid'];
		$template_data	= wpjam_json_decode($job['template_data']);

		// if($template_data['page'] && strpos($template_data['page'], 'masssend') === false){ 
		// 	if(strpos($template_data['page'], '?')){
		// 		$template_data['page']	.= '&vendor=masssend-'.$job_id;
		// 	}else{
		// 		$template_data['page']	.= '?vendor=masssend-'.$job_id;
		// 	}
		// }

		if($template_data['page']){
			$template_data['page']	= str_replace('/pages/', 'pages/', $template_data['page']);
		}

		$datas	= WEAPP_MasssendLog::get_sends($job_id);

		if($datas){
			$failed		= $job['failed'];
			$success	= $job['success'];

			foreach ($datas as $data){
				$form_id	= $data['form_id']; 
				$openid		= $data['openid']; 

				if($data['time'] < time()-DAY_IN_SECONDS*7+10){
					$form_id	= weapp_get_form_id($openid, $appid);
				}

				if($form_id){
					$template_data['form_id']	= $form_id;
					$template_data['touser']	= $openid;

					$result		= weapp_send_template_message($template_data, $appid);
					if(is_wp_error($result)){
						$failed++;
					}else{
						$success++;
					}
				}else{
					$failed++;
				}
			}

			self::update($job_id, compact('failed', 'success'));

			if(count($datas) >= 10){
				wp_schedule_single_event(time()+5,'weapp_scheduled_send_template_job',array($job_id));
			}else{
				self::finish($job_id);
			}
		}else{
			self::finish($job_id);
		}
	}

	protected static $handler;
	protected static $appid;

	public static function get_handler(){
		if(empty(static::$handler)){
			static::$handler =	new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'cache_group'		=> 'weapp_masssend_jobs',
				'searchable_fields'	=> [],
				'filterable_fields'	=> ['status'],
			));
		}

		return static::$handler;
	}

	public static function get_table(){
		global $wpdb;
		
		return $wpdb->base_prefix . 'weapp_masssend_jobs';
	}

	public static function create_table(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;
		
		$table	= self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` bigint(20) NOT NULL auto_increment,
				`title` text NOT NULL,
				`appid` VARCHAR(32) NULL,
				`count` int(10) NOT NULL,
				`success` int(10) NOT NULL,
				`failed` int(10) NOT NULL,
				`template_key` varchar(63) NOT NULL,
				`template_data` text NOT NULL,
				`status` int(1) NOT NULL,
				`start_time` int(10) NOT NULL,
				`end_time` int(10) NOT NULL,
				PRIMARY KEY	(`id`),
				KEY `status` (`status`),
				KEY `appid` (`appid`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";

			dbDelta($sql);
		}
	}
}

Class WEAPP_MasssendLog extends WPJAM_Model{
	use WEAPP_Trait;

	public static function get_sends($job_id, $number=10){
		$datas = self::Query()->where('job_id', $job_id)->limit($number)->get_results('id, openid, form_id, time');

		if($datas){
			self::Query()->delete_multi(array_column($datas, 'id'));
			return $datas;
		}else{
			return array();
		}
	}

	public static function cancel($job_id){
		return self::Query()->where('job_id', $job_id)->delete();
	}

	protected static $handler;
	protected static $appid;
	public static function get_handler(){
		if(empty(static::$handler)){
			static::$handler =	new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'cache_group'		=> 'weapp_masssend_log',
				'cache'				=> false,
				'searchable_fields'	=> [],
				'filterable_fields'	=> [],
			));
		}

		return static::$handler;
	}

	public static function get_table(){
		global $wpdb;
		
		return $wpdb->base_prefix . 'weapp_masssend_log';
	}

	public static function create_table(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;
		
		$table	= self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` bigint(20) NOT NULL auto_increment,
				`job_id` bigint(20) NOT NULL,
				`form_id` varchar(64) NOT NULL,

				`appid` VARCHAR(32) NOT  NULL,
				`openid` VARCHAR(30) NOT  NULL,
				`template_key` varchar(63) NOT NULL,
				`template_data` text NOT NULL,
				`related_id` bigint(20) NOT NULL,

				`time` int(10) NOT NULL,			-- form_id 时候为 form_id 时间，其他为发送时间
				PRIMARY KEY	(`id`),
				KEY `job_id` (`job_id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";

			dbDelta($sql);
		}
	}
}

class WEAPP_TemplateMessage extends WEAPP_MasssendLog{
	public static function add($data){
		$list_cache	= self::get_list_cache();
		$list_cache->add($data);
	}

	public static function remove_multi($related_ids, $template_key){
		$uncached_related_ids = [];
		foreach ($related_ids as $related_id) {
			self::remove_from_cache($related_id, $template_key);
		}

		return self::Query()->where('template_key', $template_key)->where_in('related_id', $related_ids)->delete();
	}	

	public static function remove($related_id, $template_key){
		if(!self::remove_from_cache($related_id, $template_key)){
			return self::Query()->where('template_key', $template_key)->where('related_id', $related_id)->delete();
		}
	}

	public static function remove_from_cache($related_id, $template_key){
		$list_cache	= self::get_list_cache();
		$items		= $list_cache->get_all();

		$items		= array_filter($items, function($item) use($related_id, $template_key){
			return ($item['related_id'] == $related_id && $item['template_key'] == $template_key);
		});

		if($items){
			$list_cache->remove(array_keys($items)[0]);
			return true;
		}

		return false;
	}

	public static function send($data, $appid=''){
		$data['touser']	= $data['touser'] ?? $data['openid'];

		if(empty($data['touser'])){
			return new WP_Error('empty_touser', 'touser 或者 openid 为空。');
		}

		$data['template_id']	= $data['template_id'] ?? weapp_get_template_id($data['template_key']);

		if(empty($data['template_id'])){
			return new WP_Error('empty_template_id', 'template_id 为空。');
		}

		$data['form_id']	= $data['form_id'] ?? weapp_get_form_id($data['touser'], $appid);

		if(empty($data['form_id'])){
			return new WP_Error('empty_form_id', 'form_id 为空。');
		}

		$weapp	= weapp($appid);

		if(is_wp_error($weapp)){
			return $weapp;
		}

		if(isset($data['template_key'])){
			unset($data['template_key']);
		}

		if(isset($data['openid'])){
			unset($data['openid']);
		}

		if($status = apply_filters('pre_weapp_send_template_message', false, $data, $appid)){
			return $status;
		}
		
		return $weapp->send_template_message($data);
	}

	public static function send_template_messages($datas=null){
		if(empty($datas)){
			$list_cache	= self::get_list_cache();
			$items		= $list_cache->empty();

			if($items){
				self::insert_multi($items);
			}

			$datas = self::Query()->where('job_id', 0)->where_lt('time', time())->group_by('openid')->limit(50)->get_results('id, openid, appid, template_key, template_data');	// 一个用户5分钟内的模板消息只发一次

			if($datas){
				self::Query()->delete_multi(array_column($datas, 'id'));
			}
		}

		// wpjam_print_r($datas);

		if($datas){
			
			// wpjam_print_r($datas);

			if($status = apply_filters('pre_weapp_send_template_messages', false, $datas)){
				return $status;
			}

			$appids		= array_column($datas, 'appid');
			$openids	= array_column($datas, 'openid');

			$form_ids_list	= WEAPP_UserFormId::get_multi_form_ids($appids, $openids);

			$template_datas	= [];

			// self::Query()->where('job_id', 0)->where_lt('time', time())->delete();
			foreach ($datas as $data) {
				$appid		= $data['appid'];
				$openid		= $data['openid'];

				if($appid && $openid){
					// $form_id	= weapp_get_form_id($openid, $appid);

					$form_ids	= array_values(array_filter($form_ids_list, function($form_id_list) use($appid, $openid){
						return ($form_id_list['appid'] == $appid && $form_id_list['openid'] == $openid);
					}));

					if($form_ids){
						$form_id		= $form_ids[0]['form_id'];
						$template_data	= wpjam_json_decode($data['template_data']);

						$template_data['form_id']	= $form_id;
						$template_data['touser']	= $openid;
						$template_data['appid']		= $appid;

						$template_datas[]	= $template_data;

						$result		= weapp_send_template_message($template_data, $appid);
						if(is_wp_error($result)){
							trigger_error($appid.' '. var_export($result, true). ' '. var_export($template_data, true));
						}
					}
				}
			}
		}
	}
} 