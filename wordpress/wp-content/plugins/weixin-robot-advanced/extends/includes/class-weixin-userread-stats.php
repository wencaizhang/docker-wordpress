<?php
class WEIXIN_UserReadStats extends WPJAM_Model {
	use WEIXIN_Stats;
	use WEIXIN_Trait;
	
	public static function sync($appid=''){
		$appid	= ($appid)?:static::get_appid();
		$dates	= self::get_dates(3, $appid);
		if(is_wp_error($dates)){
			return $dates;
		}

		$begin_date	= $dates['begin_date'];
		$end_date	= $dates['end_date'];

		$response	= weixin()->get_user_read($begin_date, $end_date);

		return self::save_data($begin_date, $end_date, $appid, $response);
	}
	
	public static $types =array(
		'0'	=>'会话',
		'1'	=>'好友转发',
		'2'	=>'朋友圈',
		'3'	=>'腾讯微博',
		'4'	=>'历史消息页',
		'5'	=>'其它',
		'6'	=>'看一看',
		'7'	=>'搜一搜'
	);

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_userread_stats';
	}

	protected static $handler;
	protected static $appid;

	public static function get_handler(){
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'field_types'		=> ['id'=>'%d','user_source'=>'%d'],
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
				`user_source` int(3) NOT NULL,
				`int_page_read_user` int(10) NOT NULL,
				`int_page_read_count` int(10) NOT NULL,
				`ori_page_read_user` int(10) NOT NULL,
				`ori_page_read_count` int(10) NOT NULL,
				`share_user` int(10) NOT NULL,
				`share_count` int(10) NOT NULL,
				`add_to_fav_user` int(10) NOT NULL,
				`add_to_fav_count` int(10) NOT NULL,
				PRIMARY KEY(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `appid` (`appid`),
				ADD KEY `user_source` (`user_source`),
				ADD KEY `ref_date` (`ref_date`);");

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD UNIQUE( `appid`, `ref_date`, `user_source`);");
		}
	}
}