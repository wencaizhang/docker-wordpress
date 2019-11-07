<?php
class WEIXIN_InterfaceStats extends WPJAM_Model {
	use WEIXIN_Stats;
	use WEIXIN_Trait;
	
	public static function sync($appid=''){
		$appid	= ($appid)?:static::get_appid();
		$dates	= self::get_dates(30, $appid);
		if(is_wp_error($dates)){
			return $dates;
		}

		$begin_date	= $dates['begin_date'];
		$end_date	= $dates['end_date'];

		$response	= weixin()->get_interface_summary($begin_date, $end_date);

		return self::save_data($begin_date, $end_date, $appid, $response);
	}

	public static $types = array(
		'callback_count'	=>'调用次数', 
		'fail_count'		=>'失败次数', 
		'fail_percent'		=>'失败率', 
		// 'total_time_cost'	=>'总共耗时（毫秒）#', 
		'avg_time_cost'		=>'平均耗时（毫秒）',
		'max_time_cost'		=>'最大耗时（毫秒）',
	);

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_interface_stats';
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
				`callback_count` int(10) NOT NULL,
				`fail_count` int(10) NOT NULL,
				`total_time_cost` int(10) NOT NULL,
				`max_time_cost` int(10) NOT NULL,
				PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `appid` (`appid`),
				ADD KEY `ref_date` (`ref_date`);");

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD UNIQUE( `appid`, `ref_date`);");
		}
	}
}