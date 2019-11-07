<?php

function weapp_create_run_model($appid){
	return new class($appid) extends WEAPP_run{
		protected static $appid;
		protected static $handler;

		public function __construct($appid){
			self::$appid = $appid;
		}
	};
}

class WEAPP_Run extends WPJAM_Model {
	protected static $handler;
	
	public static function get_appid(){
		if($appid = weapp_get_appid()){
			return $appid;
		}else{
			return static::$appid;
		}
	}

	public static function get_handler(){
		global $wpdb;
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_DB($wpdb->base_prefix . 'weapp_' . static::get_appid() . '_runs', array(
				'primary_key'		=> 'id',
				'field_types'		=> array('id'=>'%d'),
				'searchable_fields'	=> ['name'],
				'filterable_fields'	=> [],
			));
		}
		return static::$handler;
	}

	public static function sync($data){
		if(!empty($data['openid']) && !empty($data['timestamp']) && !empty($data['step'])){
			if(self::Query()->where('openid', $data['openid'])->where('timestamp',$data['timestamp'])->find() == false){
				return parent::insert($data);
			}
		}
	}

	public static function get_step($openid, $timestamp){
		return self::Query()->where('openid', $openid)->where('timestamp', $timestamp)->get_var('step');
	}

	public static function item_callback($item){
		return $item;
	}

	public static function create_table(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$wpdb->weapp_runs	= $wpdb->base_prefix . 'weapp_' . self::get_appid() . '_runs';

		if($wpdb->get_var("show tables like '{$wpdb->weapp_runs}'") != $wpdb->weapp_runs) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->weapp_runs}` (
				`id` bigint(20) NOT NULL auto_increment,
				`openid` varchar(30)	NOT NULL,
				`timestamp` int(10)	NOT NULL,
				`step` int(6)	NOT NULL,
				PRIMARY KEY	(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}
