<?php
class WEIXIN_UserShareStats extends WPJAM_Model {
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

		$response	= weixin()->get_user_share($begin_date, $end_date);

		return self::save_data($begin_date, $end_date, $appid, $response);
	}

	public static $types = array(
		'1'		=>'好友转发',
		'2'		=>'朋友圈',
		'5'		=>'腾讯微博',
		'255'	=>'其它',
	);
	
	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_usershare_stats';
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
				`share_scene` int(3) NOT NULL,
				`share_count` int(10) NOT NULL,
				`share_user` int(10) NOT NULL,
				PRIMARY KEY(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `appid` (`appid`),
				ADD KEY `share_scene` (`share_scene`),
				ADD KEY `ref_date` (`ref_date`);");
			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD UNIQUE( `appid`, `ref_date`, `share_scene`);");
		}
	}
}