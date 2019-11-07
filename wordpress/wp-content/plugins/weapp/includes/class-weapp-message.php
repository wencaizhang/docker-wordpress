<?php
class WEAPP_Message extends WPJAM_Model{
	use WEAPP_Trait;
	public static function insert($data){
		$msg_type	= $data['MsgType'];

		if($msg_type == 'miniprogrampage'){
			$data['Content']	= $data['Title'];
			$data['AppId2']		= $data['AppId'];
			$data['PicUrl']		= $data['ThumbUrl'];
			$data['MediaId']	= $data['ThumbMediaId'];

			unset($data['Title']);
			unset($data['AppId']);
			unset($data['ThumbUrl']);
			unset($data['ThumbMediaId']);
		}

		unset($data['ToUserName']);

		$data['appid']	= self::get_appid();
		return parent::insert($data);
	}

	public static function delete_old_messages(){
		return self::Query()->where_lt('CreateTime', time()-MONTH_IN_SECONDS)->delete();
	}

	protected static $handler;
	protected static $appid;	

	public static function get_handler(){
		if(is_null(static::$handler)){
			global $wpdb;
			static::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'cache_group'		=> 'weapp_messages',
				'cache'				=> false,
				'field_types'		=> ['id'=>'%d'],
				'searchable_fields'	=> ['Content'],
				'filterable_fields'	=> ['MsgType'],
			));
		}
		return static::$handler;
	}

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix . 'weapp_messages';
	}

	public static function create_table(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$table = self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` varchar(32) NOT NULL,
				`FromUserName` varchar(30)	NOT NULL,
				`CreateTime` int(10) NOT NULL,
				`MsgType` varchar(7) NOT NULL,
				`Content` text NOT NULL,
				`MsgId` bigint(20) NOT NULL,
				`PicUrl` varchar(500) NOT NULL,
				`MediaId` varchar(500) NOT NULL,
				`AppId2` varchar(32) NOT NULL,
				`PagePath` varchar(255) NOT NULL,
				`Event` varchar(50) NOT NULL,
				`SessionFrom` varchar(50) NOT NULL,
				`KfAccount` varchar(50) NOT NULL,
				`CloseType` varchar(15) NOT NULL,
				PRIMARY KEY	(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}
