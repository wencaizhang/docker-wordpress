<?php
include WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/trait.php';

add_filter('wpjam_term_options_list_table', function(){
	return [
		'title'			=> '分类选项',
		'singular' 		=> 'term-option',
		'plural' 		=> 'term-options',
		'primary_column'=> 'field_key',
		'primary_key'	=> 'field_key',
		'model'			=> 'WPJAM_TermOptionsSetting',
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

class WPJAM_TermOptionsSetting extends WPJAM_Model {
	use WPJAM_Configurator;

	public static function get($pt){
		$item	= parent::get($pt);
		$item['source']	= 'configurator';
		$item['class']	= 'source-configurator';

		return $item;
	}

	public static function query_items($limit, $offset){
		$term_options		= [];
		$taxonomy_options	= self::get_taxonomy_options();
		foreach ($taxonomy_options as $tax => $dummy) {
			$term_options	= $term_options + wpjam_get_term_options($tax);
		}

		$wpjam_term_options	= wpjam_get_option('wpjam_term_options') ?: [];
		$term_options		= $term_options + $wpjam_term_options;

		$items	= [];

		foreach ($term_options as $field_key => $term_option) {
			if(isset($wpjam_term_options[$field_key])){
				$items[$field_key]	= $wpjam_term_options[$field_key];
				$items[$field_key]['source']	= 'configurator';
			}else{
				if(empty($term_option['taxonomies'])){
					$term_option['taxonomies']	= [$term_option['taxonomy']];
				}elseif($term_option['taxonomies'] == 'all'){
					$term_option['taxonomies']	= array_keys($taxonomy_options);
				}

				$items[$field_key]	= [
					'source'		=> 'code',
					'field_key'		=> $field_key,
					'taxonomies'	=> $term_option['taxonomies']
				];
			}
		}

		$total	= count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		if($item['source'] != 'configurator'){
			unset($item['row_actions']);
			$item['field_key']	= wpautop($item['field_key']);
		}

		$item['class']	= 'source-'.$item['source'];

		return $item;
	}

	public static function user_can($item){
		return in_array($item['source'], ['configurator', '配置器生成']);
	}

	public static function get_fields($action_key='', $id=0){
		$taxonomy_options	= self::get_taxonomy_options();

		return [
			'field_key'		=> ['title'=>'选项字段',		'type'=>'text', 	'show_admin_column'=>true,	'required',	'description'=>'必须英文名'],
			'taxonomies'	=> ['title'=>'自定义分类',	'type'=>'checkbox',	'show_admin_column'=>true,	'options'=> $taxonomy_options],
			'field'			=> ['title'=>'字段详情',		'type'=>'textarea',	'style'=>'width: 25em;',	'description'=>self::get_field_usage_description()],
			'source'		=> self::get_source_field(),
		];
	}

	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_term_options', 'field_key');
		}
		return static::$handler;
	}
}