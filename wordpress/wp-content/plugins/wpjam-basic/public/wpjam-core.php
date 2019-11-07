<?php
// 添加后台菜单
function wpjam_add_menu_page($menu_slug, $args=[]){
	global $wpjam_pages;
	$wpjam_pages	= $wpjam_pages ?? [];
	
	$wpjam_pages[$menu_slug]	= $args;
}

function wpjam_register_post_type($post_type, $args=[]){
	global $wpjam_post_types;

	$wpjam_post_types	= $wpjam_post_types ?? [];

	$wpjam_post_types[$post_type]	= $args;
}

// 注册自定义日志类型
add_action('init', function (){
	global $wpjam_post_types, $wp_post_types;

	$wpjam_post_types	= $wpjam_post_types ?? [];
	$wpjam_post_types	= apply_filters('wpjam_post_types', $wpjam_post_types);

	if(!$wpjam_post_types) {
		return;
	}

	foreach ($wpjam_post_types as $post_type => $post_type_args) {
		$post_type_args	= wp_parse_args($post_type_args, [
			'public'			=> true,
			'show_ui'			=> true,
			'hierarchical'		=> false,
			'rewrite'			=> true,
			'permastruct'		=> false,
			'thumbnail_size'	=> '',
			// 'capability_type'	=> $post_type,
			// 'map_meta_cap'		=> true,
			'supports'			=> ['title'],
			'taxonomies'		=> [],
		]);

		if(empty($post_type_args['taxonomies'])){
			unset($post_type_args['taxonomies']);
		}

		if($post_type_args['hierarchical']){
			$post_type_args['supports'][]	= 'page-attributes';
		}

		$permastruct = $post_type_args['permastruct'];

		if(empty($post_type_args['rewrite'])){
			$post_type_args['rewrite']	= $permastruct ? true : false;
		}

		if($post_type_args['rewrite']){
			if(is_array($post_type_args['rewrite'])){
				$post_type_args['rewrite']	= wp_parse_args($post_type_args['rewrite'], ['with_front'=>false, 'feeds'=>false]);
			}else{
				$post_type_args['rewrite']	= ['with_front'=>false, 'feeds'=>false];
			}
		}

		register_post_type($post_type, $post_type_args);

		if($permastruct){
			if(strpos($permastruct, "%post_id%") || strpos($permastruct, "%{$post_type}_id%")){
				$wp_post_type	= $wp_post_types[$post_type];

				$permastruct_args			= $wp_post_type->rewrite;
				$permastruct_args['feed']	= $permastruct_args['feeds'];

				$permastruct	= str_replace('%post_id%', '%'.$post_type.'_id%', $permastruct); 

				add_rewrite_tag('%'.$post_type.'_id%', '([0-9]+)', "post_type=$post_type&p=" );

				add_permastruct($wp_post_type->name, $permastruct, $permastruct_args);
			}
		}
	}
}, 11);

// 设置自定义日志的链接
add_filter('post_type_link', function($post_link, $post){
	$post_type	= $post->post_type;
	$post_id	= $post->ID;

	global $wp_post_types;

	if(empty($wp_post_types[$post_type]->permastruct)){
		return $post_link;
	}

	$post_link	= str_replace( '%'.$post_type.'_id%', $post_id, $post_link );

	if(strpos($post_link, '%') === false){
		return $post_link;
	}

	$taxonomies = get_taxonomies(['object_type'=>[$post_type]], 'objects');

	if(!$taxonomies){
		return $post_link;
	}

	foreach ($taxonomies as $taxonomy=>$taxonomy_object) {
		if($taxonomy_rewrite = $taxonomy_object->rewrite){
			$terms = get_the_terms($post_id, $taxonomy);
			if($terms){
				$term = current($terms);
				$post_link	= str_replace( '%'.$taxonomy_rewrite['slug'].'%', $term->slug, $post_link );
			}else{
				$post_link	= str_replace( '%'.$taxonomy_rewrite['slug'].'%', $taxonomy, $post_link );
			}
		}
	}

	return $post_link;
}, 1, 2);

function wpjam_register_taxonomy($taxonomy, $args=[]){
	global $wpjam_taxonomies;

	$wpjam_taxonomies	= $wpjam_taxonomies ?? [];

	$wpjam_taxonomies[$taxonomy]	= ['object_type'=>$args['object_type'], 'args'=>$args];
}

// 注册自定义分类
add_action('init', function (){
	global $wpjam_taxonomies;

	$wpjam_taxonomies	= $wpjam_taxonomies ?? [];

	$wpjam_taxonomies	= apply_filters('wpjam_taxonomies', $wpjam_taxonomies);

	if(!$wpjam_taxonomies) {
		return;
	}

	foreach ($wpjam_taxonomies as $taxonomy=>$taxonomy_args) {
		$object_type	= $taxonomy_args['object_type'];
		$taxonomy_args	= wp_parse_args($taxonomy_args['args'], [
			'show_ui'			=> true,
			'show_in_nav_menus'	=> false,
			'show_admin_column'	=> true,
			'hierarchical'		=> true,
		]);

		register_taxonomy($taxonomy, $object_type, $taxonomy_args);
	}
}, 11);

function wpjam_register_post_option($meta_box, $args=[]){
	global $wpjam_post_options;

	$wpjam_post_options	= $wpjam_post_options ?? [];

	$wpjam_post_options[$meta_box]	= $args;
}

// 获取自定义字段设置
function wpjam_get_post_options($post_type){
	$post_type_options	= [];

	global $wpjam_post_options;
	$wpjam_post_options	= $wpjam_post_options ?? [];

	$wpjam_post_options	= apply_filters('wpjam_post_options', $wpjam_post_options, $post_type);
	
	if($wpjam_post_options){
		foreach($wpjam_post_options as $meta_key => $post_option){
			$post_option = wp_parse_args($post_option, [
				'post_types'	=> 'all',
				'post_type'		=> ''
			]);

			if($post_option['post_type'] && $post_option['post_types'] == 'all'){
				$post_option['post_types'] = [$post_option['post_type']];
			}

			if($post_option['post_types'] == 'all' || in_array($post_type, $post_option['post_types'])){
				$post_type_options[$meta_key] = $post_option;
			}
		}
	}

	return apply_filters('wpjam_'.$post_type.'_post_options', $post_type_options);
}

function wpjam_get_post_fields($post_type){
	if($post_options = wpjam_get_post_options($post_type)) {
		return call_user_func_array('array_merge', array_column(array_values($post_options), 'fields'));
	}else{
		return [];
	}
}

function wpjam_register_term_option($key, $args=[]){
	global $wpjam_term_options;

	$wpjam_term_options	= $wpjam_term_options ?? [];

	$wpjam_term_options[$key]	= $args;
}

// 获取 Term Meta Options 
function wpjam_get_term_options($taxonomy){
	global $wpjam_term_options;

	$wpjam_term_options	= $wpjam_term_options ?? [];
		
	$wpjam_term_options	= apply_filters('wpjam_term_options', $wpjam_term_options, $taxonomy);

	$taxonomy_options	= [];

	if($wpjam_term_options){
		foreach ($wpjam_term_options as $key => $term_option) {
			$term_option	= wp_parse_args( $term_option, [
				'taxonomies'	=> 'all',
				'taxonomy'		=> ''
			]);

			if($term_option['taxonomy'] && $term_option['taxonomies'] == 'all'){
				$term_option['taxonomies'] = [$term_option['taxonomy']];
			}

			if($term_option['taxonomies'] == 'all' || in_array($taxonomy, $term_option['taxonomies'])){
				$taxonomy_options[$key]	= $term_option;
			}
		}
	}

	return apply_filters('wpjam_'.$taxonomy.'_term_options', $taxonomy_options);
}

// 注册选项
function wpjam_register_option($option_name, $args=[]){
	global $wpjam_option_settings;

	$wpjam_option_settings	= $wpjam_option_settings ?? [];

	$wpjam_option_settings[$option_name]	= $args;
}

// 获取某个选项的所有设置
function wpjam_get_option_setting($option_name){
	$option_setting	= apply_filters(wpjam_get_filter_name($option_name,'setting'), []);
	if(!$option_setting){
		global $wpjam_option_settings;

		$wpjam_option_settings	= $wpjam_option_settings ?? [];

		if($wpjam_option_settings && !empty($wpjam_option_settings[$option_name])){
			$option_setting	= $wpjam_option_settings[$option_name];
		}else{
			$option_settings	= apply_filters('wpjam_settings', [], $option_name);

			if(!$option_settings || empty($option_settings[$option_name])) {
				return false;
			}

			$option_setting	= $option_settings[$option_name];
		}	
	}

	if(empty($option_setting['sections'])){	// 支持简写
		if(isset($option_setting['fields'])){
			$fields		= $option_setting['fields'];
			$summary	= $option_setting['summary']??'';
			unset($option_setting['fields']);
			$option_setting['sections']	= [$option_name => compact('fields','summary')];
		}else{
			$option_setting['sections']	= $option_setting;
		}
	}

	return wp_parse_args($option_setting, [
		'option_group'	=> $option_name, 
		'option_page'	=> $option_name, 
		'ajax'			=> true,
		'option_type'	=> 'array', 	// array：设置页面所有的选项作为一个数组存到 options 表， single：每个选项单独存到 options 表。
		'capability'	=> 'manage_options',
		'sections'		=> []
	]);
}

function wpjam_register_api($json, $args=[]){
	global $wpjam_apis;

	$wpjam_apis	= $wpjam_apis ?? [];

	$wpjam_apis[$json]	= $args;
}

function wpjam_get_api_setting($json){
	global $wpjam_apis;

	$wpjam_apis	= $wpjam_apis ?? [];

	if($wpjam_apis && !empty($wpjam_apis[$json])){
		return $wpjam_apis[$json];
	}

	$api_settings	= apply_filters('wpjam_apis', [], $json);

	return $api_settings[$json] ?? [];
}

function wpjam_get_filter_name($name='', $type=''){
	global $plugin_page;

	$filter	= str_replace('-', '_', $name);
	$filter	= str_replace('wpjam_', '', $filter);

	return 'wpjam_'.$filter.'_'.$type;
}