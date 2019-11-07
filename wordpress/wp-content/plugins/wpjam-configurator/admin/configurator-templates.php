<?php
add_filter('wpjam_configurator_templates_list_table', function(){
	return [
		'title'			=> '配置器模板',
		'singular' 		=> 'configurator-template',
		'plural' 		=> 'configurator-templates',
		'primary_column'=> 'title',
		'primary_key'	=> 'id',
		'model'			=> 'WPJAM_ConfiguratorTemplate',
		'ajax'			=> true,
	];
});

class WPJAM_ConfiguratorTemplate extends WPJAM_Model {
	public static function get_keys(){
		return ['post_types','post_options','taxonomies','term_options','settings','apis'];
	}

	public static function insert($data){
		$data['time']		= time();
		$data['blog_id']	= get_current_blog_id();

		foreach (self::get_keys() as $s) {
			$data[$s]	= maybe_serialize(get_option('wpjam_'.$s));
		}

		return parent::insert($data);
	}

	public static function apply($id){
		$data	= self::get($id);

		foreach (self::get_keys() as $s) {
			$data[$s]	= maybe_unserialize($data[$s]);
			if($data[$s]){
				$value	= get_option('wpjam_'.$s) ?: [];
				$value	= array_merge($value, $data[$s]);
				update_option('wpjam_'.$s, $value);
			}
		}
	}

	public static function item_callback($item){
		$item['time']	= get_date_from_gmt(date('Y-m-d H:i:s', $item['time']));

		return $item;
	}

	public static function get_actions(){
		return [
			'add'		=> ['title'=>'上传'],
			'apply'		=> ['title'=>'应用',	'direct'=>true,	'confirm'=>true],
			'delete'	=> ['title'=>'删除',	'direct'=>true,	'confirm'=>true],
		] ;
	}

	public static function get_fields($action_key='', $pt=''){
		$fields	= [
			'title'		=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>true,	'required',	'description'=>'请输入名称，将本站的设置上传到模板库，方便以后使用！'],
			'time'		=> ['title'=>'添加时间',	'type'=>'text',		'show_admin_column'=>'only'],	
			'blog_id'	=> ['title'=>'来源',		'type'=>'view',		'show_admin_column'=>'only'],
		];

		if(!is_multisite()){
			unset($fields['blog_id']);
		}

		return $fields;
	}

	private static 	$handler;

	public static function get_handler(){
		global $wpdb;
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB($wpdb->base_prefix.'configurator_templates', array(
				'primary_key'		=> 'id',
				'field_types'		=> [],
				'searchable_fields'	=> [],
				'filterable_fields'	=> [],
			));
		}
		return self::$handler;
	}

	public static function create_table(){
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if($wpdb->get_var("show tables like '{$wpdb->base_prefix}configurator_templates'") != $wpdb->base_prefix.'configurator_templates') {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}configurator_templates` (
				`id` bigint(20) NOT NULL auto_increment,
				`title` varchar(255) NOT NULL,
				`blog_id` bigint(20) NOT NULL,
				`post_types` text NOT NULL,
				`post_options` text NOT NULL,
				`taxonomies` text NOT NULL,
				`term_options` text NOT NULL,
				`settings` text NOT NULL,
				`mag_apis` text NOT NULL,
				`time` int(10) NOT NULL,
				PRIMARY KEY	(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}

WPJAM_ConfiguratorTemplate::create_table();