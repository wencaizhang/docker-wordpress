<?php
include WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/trait.php';

add_filter('wpjam_post_options_list_table', function(){
	return [
		'title'			=> '文章选项',
		'singular' 		=> 'option',
		'plural' 		=> 'options',
		'primary_column'=> 'title',
		'primary_key'	=> 'meta_box',
		'ajax'			=> true,
		'sortable'		=> true,
		'model'			=> 'WPJAM_PostOptionsSetting',
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

class WPJAM_PostOptionsSetting extends WPJAM_Model {
	use WPJAM_Configurator;

	public static function add_field($meta_box, $item){
		$meta	= self::get($meta_box);
		if(empty($meta)){
			return new WP_Error('invalid_meta_box', '非法 Meta Box');
		}

		$item_handler	= new WPJAM_PostOptionsField($meta_box);

		return $item_handler->insert($item);
	}

	public static function edit_field($meta_box, $item){
		$meta	= self::get($meta_box);
		if(empty($meta)){
			return new WP_Error('invalid_meta_box', '非法 Meta Box');
		}

		$item_handler	= new WPJAM_PostOptionsField($meta_box);

		return $item_handler->update($item['i'], $item);
	}

	public static function delete_field($meta_box){
		$meta	= self::get($meta_box);
		if(empty($meta)){
			return new WP_Error('invalid_meta_box', '非法 Meta Box');
		}

		$i		= wpjam_get_data_parameter('i') ?: 0;

		$item_handler	= new WPJAM_PostOptionsField($meta_box);

		return $item_handler->delete($i);
	}

	public static function get($meta_box){
		$item	= parent::get($meta_box);
		$item['source']	= 'configurator';
		$item['class']	= 'source-configurator';

		return $item;
	}

	public static function query_items($limit, $offset){
		$post_options		= [];
		$post_type_options	= self::get_post_type_options();
		foreach ($post_type_options as $pt => $dummy) {
			$post_options	= $post_options + wpjam_get_post_options($pt);	// 防止数字键名，使用加号
		}

		$wpjam_post_options	= wpjam_get_option('wpjam_post_options');
		$post_options		= $post_options	+ $wpjam_post_options;

		$items	= [];

		foreach ($post_options as $meta_box => $post_option) {
			if(isset($wpjam_post_options[$meta_box])){
				$items[$meta_box]	= $wpjam_post_options[$meta_box];
				$items[$meta_box]['source']	= 'configurator';
			}else{
				if(empty($post_option['post_types'])){
					$post_option['post_types']	= [$post_option['post_type']];
				}elseif($post_option['post_types'] == 'all'){
					$post_option['post_types']	= array_keys($post_type_options);
				}

				$items[$meta_box]	= [
					'source'		=> 'code',
					'meta_box'		=> $meta_box,
					'title'			=> $post_option['title'],
					'post_types'	=> $post_option['post_types'],
					'fields'		=> $post_option['fields'],
				];
			}
		}

		$total	= count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		$item['fields']	= self::get_items_table($item['fields'], $item['meta_box']);
		$item['meta']	= $item['title'].' ('.$item['meta_box'].')';
		$item['class']	= 'source-'.$item['source'];

		if($item['source'] == 'configurator'){
			unset($item['row_actions']['edit_field']);
			unset($item['row_actions']['delete_field']);
		}else{
			$item['meta']	= wpautop($item['meta']);
			unset($item['row_actions']);
		}

		return $item;
	}

	public static function user_can($item){
		return in_array($item['source'], ['configurator', '配置器生成']);
	}

	public static function get_actions(){
		return [
			'add'			=> ['title'=>'新建',		'page_title'=>'新建 Meta Box'],
			'edit'			=> ['title'=>'编辑',		'page_title'=>'编辑 Meta Box'],
			'duplicate'		=> ['title'=>'复制',		'page_title'=>'复制 Meta Box'],
			'add_field'		=> ['title'=>'添加字段',	'page_title'=>'添加字段'],
			'edit_field'	=> ['title'=>'编辑字段',	'page_title'=>'编辑字段'],
			'delete_field'	=> ['title'=>'删除字段',	'page_title'=>'编辑字段',			'direct'=>true, 'confirm'=>true],
			'delete'		=> ['title'=>'删除',		'page_title'=>'删除 Meta Box',	'direct'=>true, 'confirm'=>true,	'bulk'=>true]
		];
	}

	public static function get_fields($action_key='', $meta_box=''){
		if($action_key == 'add_field'){
			return [
				'key'		=> ['title'=>'名称',	'type'=>'text',	'class'=>'',	'description'=>'必须英文名'],
				'detail'	=> ['title'=>'详情',	'type'=>'textarea',	'description'=>self::get_field_usage_description()],
			];
		}elseif($action_key == 'edit_field'){
			$i		= wpjam_get_data_parameter('i') ?: 0;

			$meta	= self::get($meta_box);

			if(empty($meta)){
				return new WP_Error('invalid_meta_box', '非法 Meta Box');
			}

			if(isset($meta['fields'])){
				$field	= $meta['fields'][$i] ?? [];
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
				'title'			=> ['title'=>'Meta 标题',	'type'=>'text', 	'show_admin_column'=>false,	'class'=>'',	'required'],
				'meta_box'		=> ['title'=>'Meta ID',		'type'=>'text',		'show_admin_column'=>false,	'class'=>'',	'required',	'description'=>'必须英文名'],
				'meta'			=> ['title'=>'Meta Box',	'type'=>'view',		'show_admin_column'=>'only'],
				'fields'		=> ['title'=>'字段',			'type'=>'view',		'show_admin_column'=>'only'],
				'post_types'	=> ['title'=>'文章类型',		'type'=>'checkbox',	'show_admin_column'=>true,	'options'=>$post_type_options],
				'source'		=> self::get_source_field(),
			];
		}	
	}

	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_post_options', 'meta_box');
		}
		return static::$handler;
	}
}

class WPJAM_PostOptionsField extends WPJAM_Item{
	private $meta_box;

	public function __construct($meta_box){
		$this->meta_box	= $meta_box;
		parent::__construct([
			'primary_key'	=> 'id',
			'primary_title'	=> 'ID',
			'unique_key'	=> 'key',
			'unique_title'	=> '名字',
		]);
	}

	public function get_items(){
		$meta	= wpjam_get_setting('wpjam_post_options', $this->meta_box);

		if(isset($meta)){
			$items	= $meta['fields'] ?? [];

			return $this->parse_items($items);
		}
	}

	public function update_items($items){
		$meta	= wpjam_get_setting('wpjam_post_options', $this->meta_box);

		if(isset($meta)){
			$meta['fields']	= $items;
			return wpjam_update_setting('wpjam_post_options', $this->meta_box, $meta);
		}
	}
};
