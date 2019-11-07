<?php
class WEAPP_Qrcode extends WPJAM_Model{
	use WEAPP_Trait;

	public static function insert($data){
		$data['time']	= time();
		$data['color']	= (empty($data['color']) || ($data['color'] == '#000000'))?'':$data['color'];
		return parent::insert($data);
	}

	public static function delete($id){
		$data = self::get($id);

		if($data){
			$media_id	= weapp()->create_qrcode(self::parse_args($data), $data['type']);
			$media_file	= weapp()->get_media_file($media_id, $data['type']);

			if(file_exists($media_file)){
				unlink($media_file);
			}
		}

		return parent::delete($id);
	}

	public static function parse_args($data){
		$args	= array();

		$args['path']	= $data['path'];
		$args['time']	= $data['time'];
		$args['width']	= (int)$data['width'];

		if($data['type'] == 'wxacode'){
			$data['color']	= (empty($data['color']) || ($data['color'] == '#000000'))?false:$data['color'];

			if($data['color']){
				$args['auto_color']	= false;
				list($r, $g, $b)	= sscanf($data['color'], "#%02x%02x%02x");
				$args['line_color']	= compact('r', 'g', 'b');
			}else{
				$args['auto_color']	= true;
			}
		}

		return $args;
	}

	protected static $appid;
	protected static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			global $wpdb;
			static::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'field_types'		=> array('id'=>'%d'),
				'searchable_fields'	=> ['name'],
				'filterable_fields'	=> [],
			));
		}
		return static::$handler;
	}

	public static function get_table(){
		global $wpdb;

		return $wpdb->base_prefix . 'weapp_qrcodes';
	}

	public static function create_table(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$table	= self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` varchar(32) NOT NULL,
				`name` varchar(255)	NOT NULL,
				`path` varchar(255)	NOT NULL,
				`width` int(5) NOT NULL,
				`color` varchar(7) NOT NULL,
				`time` int(10) NOT NULL,
				PRIMARY KEY	(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}
