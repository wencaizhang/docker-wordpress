<?php
class WEAPP_LOG extends WPJAM_Model {
	private static 	$handler;

	public static function get_table(){
		global $wpdb;

		return $wpdb->base_prefix.'weapp_logs';
	}

	public static function get_handler(){
		global $wpdb;


		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'field_types'		=> array('id'=>'%d','time'=>'%d'),
				'searchable_fields'	=> [],
				'filterable_fields'	=> ['short', 'v'],
			));
		}
		return self::$handler;
	}

	public static function create_table($appid=''){
		global $wpdb;

		$table	= self::get_table($appid);

		if(empty($table)){
			return;
		}

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				  `id` bigint(20) NOT NULL auto_increment,
				  `appid` varchar(32) NOT NULL,
				  `page` varchar(63) NOT NULL,
				  `openid` varchar(30) NOT NULL,
				  `vendor` varchar(255) NOT NULL,
				  `level` int(3) NOT NULL,
				  `ip` varchar(23) NOT NULL,
				  `country` varchar(255) NOT NULL,
				  `region` varchar(255) NOT NULL,
				  `city` varchar(255) NOT NULL,
				  `ua` varchar(255) NOT NULL,
				  `device` varchar(31) NOT NULL,
				  `os` varchar(31) NOT NULL,
				  `os_ver` varchar(31) NOT NULL,
				  `weixin_ver` varchar(31) NOT NULL,
				  `time` int(10) NOT NULL,
				PRIMARY KEY	(`id`),
				KEY `appid` (`appid`),
				KEY `page` (`page`),
				KEY `level` (`level`),
				KEY `vendor` (`vendor`),
				KEY `timt` (`time`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}