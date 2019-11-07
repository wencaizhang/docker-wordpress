<?php
include WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/trait.php';

add_filter('wpjam_taxonomies_list_table', function(){
	return [
		'title'			=> '自定义分类',
		'singular' 		=> 'taxonomy',
		'plural' 		=> 'taxonomies',
		'primary_column'=> 'label',
		'primary_key'	=> 'tax',
		'model'			=> 'WPJAM_TaxonomySetting',
		'ajax'			=> true,
		'sortable'		=> true,
	];
});

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('.wp-list-table tbody').sortable({items: '> tr.source-configurator'});
	});
	</script>
	<?php
});

class WPJAM_TaxonomySetting extends WPJAM_Model {
	use WPJAM_Configurator;

	public static function insert($data){
		$data	= self::prepare($data);
		return parent::insert($data);
	}

	public static function update($tax, $data){
		$data	= self::prepare($data, $tax);

		return parent::update($tax, $data);
	}

	public static function delete($tax){
		flush_rewrite_rules();

		return parent::delete($tax);
	}

	public static function prepare($data, $tax=''){
		flush_rewrite_rules();
		$data['public']				= true;
		$data['show_ui']			= true;
		$data['show_in_nav_menus']	= boolval($data['show_in_nav_menus']);
		$data['show_admin_column']	= boolval($data['show_admin_column']);
		$data['rewrite']			= boolval($data['rewrite']);

		return $data;
	}

	public static function get($tax){
		$item	= parent::get($tax);
		$count	= wp_count_terms($tax);

		$item['count']	= is_wp_error($count) ? 0 : $count;
		$item['source']	= 'configurator';
		$item['class']	= 'source-configurator';

		return $item;
	}

	public static function query_items($limit, $offset){
		$wpjam_taxonomies	= wpjam_get_option('wpjam_taxonomies');

		$taxonomies	= get_taxonomies([], 'objects');
		unset($taxonomies['link_category']);

		$taxonomies	= wp_list_sort($taxonomies, ['_builtin'=>'ASC', 'public'=>'DESC', 'show_ui'=>'DESC'],'',true);

		$items	= [];

		foreach ($taxonomies as $tax => $taxonomy) {
			if(isset($wpjam_taxonomies[$tax])){
				$items[$tax]	= $wpjam_taxonomies[$tax];
				$items[$tax]['source']	= 'configurator';
			}else{
				$items[$tax]	= [
					'tax'			=> $tax,
					'label'			=> $taxonomy->label,
					'hierarchical'	=> $taxonomy->hierarchical,
					'object_type'	=> $taxonomy->object_type
				];

				if($taxonomy->_builtin){
					$items[$tax]['source']	= 'system';
				}else{
					$items[$tax]['source']	= 'code';
				}
			}
		}

		$total	= count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		$count	= wp_count_terms($item['tax']);
		$item['count']			= is_wp_error($count) ? 0 : $count;

		$item['taxonomy']		= $item['label'].' ('.$item['tax'].')';
		$item['hierarchical']	= $item['hierarchical'] ? '是' : '否';

		$item['class']			= 'source-'.$item['source'];

		if($item['source'] != 'configurator'){
			$item['taxonomy']	= wpautop($item['taxonomy']);
			unset($item['row_actions']);
		}

		return $item;
	}

	public static function user_can($item){
		return in_array($item['source'], ['configurator', '配置器生成']);
	}

	public static function get_fields($action_key='', $tax=0){
		if($action_key){
			$post_type_options	= self::get_post_type_options();
		}else{
			$post_type_options	= wp_list_pluck(get_post_types([], 'objects'), 'label', 'name');
		}

		$post_type_options['attachment']	= '媒体';

		$fields	= [
			'taxonomy'		=> ['title'=>'自定义分类',	'type'=>'view',		'show_admin_column'=>'only'],
			'base_set'		=> ['title'=>'基础设置',		'type'=>'fieldset',	'fields'=>[
				'label'			=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>false,	'class'=>'',	'required'],
				'tax'			=> ['title'=>'分类',		'type'=>'text',		'show_admin_column'=>false,	'class'=>'',	'required',	'description'=>'必须英文名'],
				'hierarchical'	=> ['title'=>'层次结构',	'type'=>'checkbox',	'show_admin_column'=>true,	'description'=>'类似分类，而非标签模式',	'value'=>1],
				'object_type'	=> ['title'=>'文章类型',	'type'=>'checkbox',	'show_admin_column'=>true,	'options'=>$post_type_options],
				'rewrite'		=> ['title'=>'固定链接',	'type'=>'checkbox',	'description'=>'支持固定链接'],
			]],
			'visiblity_set'		=> ['title'=>'显示设置',	'type'=>'fieldset',	'fields'=>[
				'show_in_nav_menus'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,	'description'=>'显示在后台导航菜单设置中'],
				'show_admin_column'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,	'description'=>'显示在关联文章类型的后台列表页'],
			]],
			'count'			=> self::get_count_field(),
			'source'		=> self::get_source_field(),
		];

		if($action_key =='edit'){
			$fields['base_set']['fields']['tax']['type']	= 'view';
		}

		return $fields;
	}

	private static $handler;

	public static function get_handler(){
		global $wpdb;
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_taxonomies', 'tax');
		}
		return static::$handler;
	}
}