<?php
include WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/trait.php';

add_filter('wpjam_post_types_list_table', function(){
	return array(
		'title'			=> '文章类型',
		'singular' 		=> 'post-type',
		'plural' 		=> 'post-types',
		'primary_column'=> 'label',
		'primary_key'	=> 'pt',
		'ajax'			=> true,
		'sortable'		=> true,
		'model'			=> 'WPJAM_PostTypeSetting'
	);
});

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function ($){
		$('body').on('change', '#show_in_menu', function(){
			if($(this).is(':checked')){
				$("#tr_menu_set").show();
			}else{
				$("#tr_menu_set").hide();
			}
		});

		$('body').on('list_table_action_success', function(event, response){
			$('#show_in_menu').change();
		});

		$('.wp-list-table tbody').sortable({items: '> tr.source-configurator'});
	});
	</script>
	<?php
});

function wpjam_add_default_post_types($value){
	$value		= $value ?: [];

	if(isset($value['post'])){
		$post_active	= $value['post']['active'] ?? 0;
		unset($value['post']);
	}else{
		$post_active	= 1;
	}

	if(isset($value['page'])){
		$page_active	= $value['page']['active'] ?? 0;
		unset($value['page']);
	}else{
		$page_active	= 1;
	}

	$defaults	= [
		'post'	=> [
			'label'			=> '文章',
			'menu_position'	=> 5,
			'menu_icon'		=> 'dashicons-admin-post',
			'hierarchical'	=> false,
			'supports'		=> ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats'],
			'taxonomies'	=> ['category','post_tag'],
			'pt'			=> 'post',
			'active'		=> $post_active
		],
		'page'	=> [
			'label'			=> '页面',
			'menu_position'	=> 5,
			'menu_icon'		=> 'dashicons-admin-page',
			'hierarchical'	=> true,
			'supports'		=> ['title', 'editor', 'author', 'thumbnail', 'comments'],
			'taxonomies'	=> [],
			'pt'			=> 'page',
			'active'		=> $page_active
		]
	];

	return array_merge($value, $defaults);
}
add_filter('default_option_wpjam_post_types', 'wpjam_add_default_post_types');
add_filter('option_wpjam_post_types', 'wpjam_add_default_post_types');

class WPJAM_PostTypeSetting extends WPJAM_Model{
	use WPJAM_Configurator;

	public static function insert($data){
		$data	= self::prepare($data);
		return parent::insert($data);
	}

	public static function update($pt, $data){
		$data	= self::prepare($data, $pt);

		return parent::update($pt, $data);
	}

	public static function delete($pt){
		flush_rewrite_rules();
		
		return parent::delete($pt);
	}

	public static function get($pt){
		$item	= parent::get($pt);
		$counts	= self::get_counts();

		$item['source']	= 'configurator';
		$item['class']	= 'source-configurator';
		$item['count']	= $counts[$pt] ?? 0;

		return $item;
	}

	public static function prepare($data, $pt=''){
		flush_rewrite_rules();

		$data['public']					= true;
		$data['show_ui']				= true;
		$data['exclude_from_search']	= boolval($data['exclude_from_search']);
		$data['has_archive']			= boolval($data['has_archive']);
		$data['show_in_menu']			= boolval($data['show_in_menu']);
		$data['show_in_nav_menus']		= boolval($data['show_in_menu']);
		$data['rewrite']				= boolval($data['rewrite']);

		if($data['rewrite']){
			$data['permastruct']	= '/'.$pt.'/%post_id%/';
		}else{
			$data['permastruct']	= false;
		}

		return $data;
	}

	public static function activate($pt){
		if(!in_array($pt, ['post', 'page'])){
			return new WP_Error('invalid_post_type', '该文章类型无需启用');
		}

		return self::update($pt, ['active'=>1]);
	}

	public static function deactivate($pt){
		if(!in_array($pt, ['post', 'page'])){
			return new WP_Error('invalid_post_type', '该文章类型无需停用');
		}

		return self::update($pt, ['active'=>0]);
	}

	public static function get_counts(){
		global $wpdb;

		static $counts;

		if(isset($counts)){
			return $counts;
		}

		$counts = (array) $wpdb->get_results("SELECT post_type, COUNT(*) AS count FROM {$wpdb->posts} GROUP BY post_type", ARRAY_A);

		$counts = $counts ? wp_list_pluck($counts, 'count', 'post_type') : [];

		return $counts;
	}

	public static function query_items($limit, $offset){
		$wpjam_post_types	= wpjam_get_option('wpjam_post_types');

		$post_types	= get_post_types([], 'objects');
		$post_types	= wp_list_sort($post_types, ['_builtin'=>'ASC', 'public'=>'DESC', 'show_ui'=>'DESC'],'',true);
		
		// unset($post_types['attachment']);
		// unset($post_types['wp_block']);

		// $_builtin_post_types	= get_post_types(['_builtin'=>true], 'objects');

		$items	= [];

		foreach ($post_types as $pt => $wp_post_type) {
			if(isset($wpjam_post_types[$pt])){
				$items[$pt]	= $wpjam_post_types[$pt];

				$items[$pt]['pt']	= $pt;

				if(in_array($pt, ['post', 'page'])){
					$items[$pt]['source']	= 'system';
				}else{
					$items[$pt]['source']	= 'configurator';
				}
			}else{
				$items[$pt]	= [
					'pt'			=> $pt,
					'label'			=> $wp_post_type->label,
					'hierarchical'	=> $wp_post_type->hierarchical,
					'menu_icon'		=> $wp_post_type->menu_icon,
					'supports'		=> array_keys(get_all_post_type_supports($pt))
				];

				if($wp_post_type->_builtin){
					$items[$pt]['source']	= 'system';
				}else{
					$items[$pt]['source']	= 'code';
				}
			}
		}

		$total	= count($items);

		return compact('items', 'total');
	}

	public static function user_can($item){
		return in_array($item['source'], ['configurator', '配置器生成']);
	}

	public static function item_callback($item){
		$counts	= self::get_counts();

		$item['post_type']		= $item['menu_icon'] ? '<span class="dashicons-before '.$item['menu_icon'].'"></span> ':'';
		$item['post_type']		.= $item['label'].' ('.$item['pt'].')';
		$item['hierarchical']	= $item['hierarchical'] ? '是' : '否';
		$item['count']			= $counts[$item['pt']] ?? 0;

		$item['class']			= 'source-'.$item['source'];

		if($item['source'] == 'system'){
			if(in_array($item['pt'], ['post', 'page'])){
				unset($item['row_actions']['edit']);
				unset($item['row_actions']['delete']);
				unset($item['row_actions']['move']);
				unset($item['row_actions']['up']);
				unset($item['row_actions']['down']);

				if($item['active']){
					unset($item['row_actions']['activate']);
				}else{
					$item['post_type'] .= '（停用）';
					unset($item['row_actions']['deactivate']);
				}
			}else{
				unset($item['row_actions']);

				$item['post_type']	= wpautop($item['post_type']);
			}
		}elseif($item['source'] == 'configurator'){
			unset($item['row_actions']['activate']);
			unset($item['row_actions']['deactivate']);
		}else{
			unset($item['row_actions']);
			$item['post_type']	= wpautop($item['post_type']);
		}

		return $item;
	}

	public static function get_actions(){
		return [
			'add'		=> ['title'=>'新建'],
			'edit'		=> ['title'=>'编辑'],
			'duplicate'	=> ['title'=>'复制'],
			'delete'	=> ['title'=>'删除',	'direct'=>true, 'confirm'=>true,	'bulk'=>true],
			'activate'	=> ['title'=>'启用',	'direct'=>true,	'confirm'=>true],
			'deactivate'=> ['title'=>'停用',	'direct'=>true,	'confirm'=>true],
		];
	}

	public static function get_fields($action_key='', $pt=''){
		$supports	= [
			'title'			=>'标题',
			'editor'		=>'内容',
			'excerpt'		=>'摘要',
			'thumbnail'		=>'缩略图',
			'author'		=>'作者',
			'post-formats'	=>'文章格式',
			'comments'		=>'评论',
			'likes'			=>'点赞',
			'favs'			=>'收藏',
			// 'page-attributes'	=>'页面属性'
		];

		if(!current_theme_supports('post-formats')){
			unset($supports['post-formats']);
		}

		$fields		= [
			'post_type'		=> ['title'=>'文章类型',	'type'=>'view',		'show_admin_column'=>'only'],
			'base_set'		=> ['title'=>'基础设置',	'type'=>'fieldset',	'fields'=>[
				'label'			=> ['title'=>'名称',		'type'=>'text',		'class'=>'',	'required',	'description'=>'显示的名称'],
				'pt'			=> ['title'=>'类型',		'type'=>'text',		'class'=>'',	'required',	'description'=>'必须英文名'],
				'supports'		=> ['title'=>'功能支持',	'type'=>'checkbox',	'show_admin_column'=>true,	'options'=>$supports],
				'hierarchical'	=> ['title'=>'层次结构',	'type'=>'checkbox',	'show_admin_column'=>true,	'description'=>'类似页面，具有层次结构'],
				'rewrite'		=> ['title'=>'固定链接',	'type'=>'radio',	'options'=>[0=>'使用文章别名',1=>'使用文章ID'],	'sep'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'],
				'taxonomies'	=> ['title'=>'默认分类',	'type'=>'checkbox',	'options'=>['category'=>'分类','post_tag'=>'标签']],
			]],
			'visiblity_set'		=> ['title'=>'显示设置',	'type'=>'fieldset',	'fields'=>[
				'exclude_from_search'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>0,	'description'=>'搜索结果不显示该文章类型'],
				'has_archive'			=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,	'description'=>'该文章类型含有归档页面'],
				'show_in_menu'			=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,	'description'=>'显示在后台菜单'],
			]],
			'menu_set'		=> ['title'=>'菜单设置',	'type'=>'fieldset',	'fields'=>[
				'menu_position'			=> ['title'=>'位置',	'type'=>'number',	'class'=>'','value'=>5,],
				'menu_icon'				=> ['title'=>'图标',	'type'=>'text',		'class'=>'all-options',	'value'=>'dashicons-admin-post'],
			]],
			'count'			=> self::get_count_field(),
			'source'		=> self::get_source_field(),
		];

		$fields	= apply_filters('wpjam_post_type_setting_fields', $fields);

		if($action_key =='edit'){
			$fields['base_set']['fields']['pt']['type']	= 'view';
		}

		return $fields;
	}

	protected static $handler;
	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_post_types', 'pt');
		}
		return static::$handler;
	}
}