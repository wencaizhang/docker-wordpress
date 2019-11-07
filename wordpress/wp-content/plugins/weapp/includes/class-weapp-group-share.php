<?php
class WEAPP_GroupShare extends WPJAM_Model {
	use WEAPP_Trait;
	public static function get_shares($gid){
		return self::get_by('gid', $gid);
	}

	public static function insert($data){
		if(!empty($data['gid'])){
			parent::insert($data);
		}
	}

	public static function update($id, $data){
		if(!empty($data['gid'])){
			parent::update($id, $data);
		}
	}

	protected static $handlers;
	protected static $appid;

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weapp_'.static::get_appid().'_group_shares';
	}

	public static function get_cache_group(){
		return 'weapp_group_shares_'.static::get_appid().'';
	}

	public static function get_handler(){
		static::$handlers	= (static::$handlers)??array();
		$appid				= static::get_appid();

		if(empty(static::$handlers[$appid])){
			static::$handlers[$appid] = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'cache_key'			=> 'gid',
				'order_by'			=> 'time',
				'field_types'		=> array('time'=>'%d',),
				'searchable_fields'	=> ['text'],
				'filterable_fields'	=> ['gid','openid','type'],
			));
		}

		return static::$handlers[$appid];
	}

	public static function create_table(){
		global $wpdb;

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');

		$table = self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` 		bigint(20) NOT NULL auto_increment,
				`openid`	varchar(30) NOT NULL,
				`gid` 		varchar(30) NOT NULL,
				`type`		varchar(15)	NOT NULL,
				`time`		int(10) NOT NULL,
				PRIMARY KEY	(`id`),
				KEY `openid` (`openid`),
				KEY `gid` (`gid`),
				KEY `type` (`type`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}