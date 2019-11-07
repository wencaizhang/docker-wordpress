<?php
class WEIXIN_MessageStats extends WPJAM_Model {
	use WEIXIN_Stats;
	use WEIXIN_Trait;
	
	public static function sync($appid=''){
		$appid	= ($appid)?:static::get_appid();
		$dates	= self::get_dates(7, $appid);
		if(is_wp_error($dates)){
			return $dates;
		}

		$begin_date	= $dates['begin_date'];
		$end_date	= $dates['end_date'];

		$response	= weixin()->get_up_stream_msg($begin_date, $end_date);

		return self::save_data($begin_date, $end_date, $appid, $response);
	}

	Public static $fields = array(
		'msg_user'	=> '消息发送人数',
		'msg_count'	=> '消息发送次数',
		'average'	=> '人均发送次数'
	);

	Public static $types = array(
		'1'	=>'文字',
		'2'	=>'图片',
		'3'	=>'语音',
		'4'	=>'视频',
		'6'	=>'链接',
	);

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_message_stats';
	}

	protected static $handler;
	protected static $appid;
	
	public static function get_handler(){
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'field_types'		=> ['id'=>'%d'],
				'searchable_fields'	=> [],
				'filterable_fields'	=> [],
			));
		}
		
		return self::$handler;
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
				`ref_date` date NOT NULL,
				`msg_type` int(10) NOT NULL,
				`msg_user` int(10) NOT NULL,
				`msg_count` int(10) NOT NULL,
				PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `appid` (`appid`),
				ADD KEY `ref_date` (`ref_date`),
				ADD KEY `msg_type` (`msg_type`);");

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD UNIQUE( `appid`, `ref_date`, `msg_type`);");
		}
	}
}