<?php
include WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/trait.php';

add_filter('wpjam_settings_list_table', function(){
	return [
		'title'			=> '全局选项',
		'singular' 		=> 'setting',
		'plural' 		=> 'settings',
		'primary_column'=> 'title',
		'primary_key'	=> 'option_name',
		'model'			=> 'WPJAM_SettingsSetting',
		'ajax'			=> true,
		'sortable'		=> true
		// 'summary'		=> '该列表仅显示配置器生成的全局选项，通过代码生成的全局选项无法显示。'
	];
});

add_action('admin_head', function(){
	?>
	<style type="text/css">
	td.column-fields table td{
		padding:0 10px 4px 0;
	}
	</style>
	<script type="text/javascript">
	jQuery(function($){
		$('.wp-list-table tbody').sortable({items: '> tr.source-configurator'});
	});
	</script>
	<?php
});

class WPJAM_SettingsSetting extends WPJAM_Model {
	use WPJAM_Configurator;

	public static function add_field($option_name, $item){
		$settings	= self::get($option_name);
		if(empty($settings)){
			return new WP_Error('invalid_option_name', '非法选项');
		}

		$item_handler	= new WPJAM_SettingsField($option_name);

		return $item_handler->insert($item);
	}

	public static function edit_field($option_name, $item){
		$settings	= self::get($option_name);
		if(empty($settings)){
			return new WP_Error('invalid_option_name', '非法选项');
		}

		$item_handler	= new WPJAM_SettingsField($option_name);

		return $item_handler->update($item['i'], $item);
	}

	public static function delete_field($option_name){
		$settings	= self::get($option_name);
		if(empty($settings)){
			return new WP_Error('invalid_option_name', '非法选项');
		}

		$i		= wpjam_get_data_parameter('i') ?: 0;

		$item_handler	= new WPJAM_SettingsField($option_name);

		return $item_handler->delete($i);
	}

	public static function get($option_name){
		$item	= parent::get($option_name);
		$item['source']	= 'configurator';
		$item['class']	= 'source-configurator';

		return $item;
	}

	public static function query_items($limit, $offset){
		$items	= [];

		$wpjam_settings	= wpjam_get_option('wpjam_settings');

		foreach ($wpjam_settings as $option_name => $setting) {
			$items[$option_name]	= $wpjam_settings[$option_name];
			$items[$option_name]['source']	= 'configurator';
		}

		global $wpjam_option_settings;

		if($wpjam_option_settings){
			foreach ($wpjam_option_settings as $option_name => $setting) {
				$items[$option_name]	= [
					'source'		=> 'code',
					'option_name'	=> $option_name,
					'fields'		=> current($setting['sections'])['fields'],
					'title'			=> current($setting['sections'])['title']
				];
			}
		}

		$total	= count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		$item['fields']		= self::get_items_table($item['fields'], $item['option_name']);
		$item['settings']	= $item['title'].' ('.$item['option_name'].')';
		$item['class']		= 'source-'.$item['source'];

		unset($item['row_actions']['edit_field']);
		unset($item['row_actions']['delete_field']);

		if($item['source'] != 'configurator'){
			$item['settings']	= wpautop($item['settings']);
			unset($item['row_actions']);
		}

		return $item;
	}

	public static function user_can($item){
		return in_array($item['source'], ['configurator', '配置器生成']);
	}

	public static function get_actions(){
		return [
			'add'			=> ['title'=>'新建'],
			'edit'			=> ['title'=>'编辑'],
			'duplicate'		=> ['title'=>'复制'],
			'add_field'		=> ['title'=>'添加字段',	'page_title'=>'添加字段'],
			'edit_field'	=> ['title'=>'编辑字段',	'page_title'=>'编辑字段'],
			'delete_field'	=> ['title'=>'删除字段',	'page_title'=>'编辑字段',		'direct'=>true, 'confirm'=>true],
			'delete'		=> ['title'=>'删除',		'bulk'=>true,	'direct'=>true, 'confirm'=>true]
		];
	}

	public static function get_fields($action_key='', $option_name=''){
		if($action_key == 'add_field'){
			return [
				'key'		=> ['title'=>'名称',	'type'=>'text',	'class'=>'',	'description'=>'必须英文名'],
				'detail'	=> ['title'=>'详情',	'type'=>'textarea',	'description'=>self::get_field_usage_description()],
			];
		}elseif($action_key == 'edit_field'){
			$i			= wpjam_get_data_parameter('i') ?: 0;

			$settings	= self::get($option_name);

			if(empty($settings)){
				return new WP_Error('invalid_option_name', '非法选项');
			}

			if(isset($settings['fields'])){
				$field	= $settings['fields'][$i] ?? [];
			}else{
				$field	= [];
			}

			if(empty($field)){
				return new WP_Error('invalid_field_index', '非法字段');
			}

			$key	= $field['key'] ?? '';
			$detail	= $field['detail'] ?? '';

			return [
				'i'			=> ['title'=>'',	'type'=>'hidden',	'value'=>$i],
				'key'		=> ['title'=>'名称',	'type'=>'text',		'value'=>$key,		'class'=>'',	'description'=>'必须英文名'],
				'detail'	=> ['title'=>'详情',	'type'=>'textarea',	'value'=>$detail,	'description'=>self::get_field_usage_description()],
			];
		}else{
			$post_type_options	= self::get_post_type_options();

			return [
				'title'			=> ['title'=>'名称',	'type'=>'text',		'show_admin_column'=>false,	'class'=>'',	'required'],
				'option_name'	=> ['title'=>'选项',	'type'=>'text',		'show_admin_column'=>false,	'class'=>'',	'required',	'description'=>'必须英文名'],
				'settings'		=> ['title'=>'选项',	'type'=>'view',		'show_admin_column'=>'only'],
				'fields'		=> ['title'=>'字段',	'type'=>'view',		'show_admin_column'=>'only'],
				'pt'			=> ['title'=>'位置',	'type'=>'radio',	'show_admin_column'=>false,	'options'=>[''=>'设置']+$post_type_options,	'description'=>'设置在那个菜单下面'],
				'source'		=> self::get_source_field()
			];
		}	
	}

	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_settings', 'option_name');
		}
		return static::$handler;
	}
}

class WPJAM_SettingsField extends WPJAM_Item{
	private $option_name;
	public function __construct($option_name){
		$this->option_name	= $option_name;
		parent::__construct([
			'primary_key'	=> 'id',
			'primary_title'	=> 'ID',
			'unique_key'	=> 'key',
			'unique_title'	=> '名字',
		]);
	}

	public function get_items(){
		$settings	= wpjam_get_setting('wpjam_settings', $this->option_name);

		if(isset($settings)){
			$items	= $settings['fields'] ?? [];

			return $this->parse_items($items);
		}
	}

	public function update_items($items){
		$settings	= wpjam_get_setting('wpjam_settings', $this->option_name);

		if(isset($settings)){
			$settings['fields']	= $items;
			return wpjam_update_setting('wpjam_settings', $this->option_name, $settings);
		}
	}
}