<?php
class WEIXIN_UserSubscribe extends WPJAM_Model {
	private static 	$handler;

	public static function get_appid(){
		return weixin_get_appid();
	}

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_'.weixin_get_appid().'_subscribes';
	}

	public static function get_handler(){
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'openid',
				'field_types'		=> array('time'=>'%d','id'=>'%d','unsubscribe_time'=>'%d'),
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
				`openid` varchar(30) NOT NULL,
				`scene` varchar(64) NOT NULL,
				`type` varchar(16) NOT NULL,
 				`time` int(10) NOT NULL,
				PRIMARY KEY	(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `openid` (`openid`);");
		}
	}
}