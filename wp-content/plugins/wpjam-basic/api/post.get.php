<?php
global $wp, $wp_query;

$output		= $args['output']??'';
$post_type	= $args['post_type']??($_GET['post_type']??'any');
$_blog_id	= $args['blog_id']??($_GET['blog_id']??0);

if(is_multisite() && $_blog_id){
	switch_to_blog($_blog_id);
}

if(!empty($args['id'])){
	$post_id	= $args['id'];
}else{
	$post_id	= wpjam_get_parameter('id');
}

$post_id	= trim($post_id);

if($post_type != 'any'){
	$post_type_object	= get_post_type_object($post_type);
	if(!$post_type_object){
		wpjam_send_json(array(
			'errcode'	=> 'post_type_not_defined',
			'errmsg'	=> 'post_type 未定义'
		));
	}
}

if(empty($post_id)){
	if($post_type == 'any'){
		wpjam_send_json(array(
			'errcode'	=> 'empty_id',
			'errmsg'	=> '文章ID不能为空'
		));
	}

	if($post_type_object->hierarchical){
		$wp->set_query_var('pagename', wpjam_get_parameter('pagename', array('required'=>true)));
	}else{
		$wp->set_query_var('name', wpjam_get_parameter('name', array('required'=>true)));
	}
}else{
	if(!is_numeric($post_id)){
		if($post_id == 'rand'){
			$orderby	= 'rand';
		}else{
			$orderby	= 'date';
		}

		$wp->set_query_var('orderby', $orderby);
	}else{
		$wp->set_query_var('p', $post_id);
	}
}

$wp->set_query_var('post_type', $post_type);
$wp->set_query_var('posts_per_page', 1);

$wp->set_query_var('cache_results', false);
$wp->set_query_var('update_post_meta_cache', false);
$wp->set_query_var('update_post_term_cache', false);
$wp->set_query_var('lazy_load_term_meta', false);

$wp->query_posts();

if($wp_query->have_posts()){
	$post_id = $wp_query->post->ID;
}else{
	if(!empty($wp_query->query_vars['name'])){
		global $wpdb;
		$post_id	= (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_old_slug' AND meta_value = %s", $wp_query->query_vars['name']));

		if(empty($post_id)){

			$post_types	= get_post_types(['public' => true]);
			unset($post_types['attachment']);

			$post_types	= "'" . implode("','", $post_types) . "'";

			$where		= $wpdb->prepare("post_name LIKE %s", $wpdb->esc_like($wp_query->query_vars['name']) . '%');
			$post_id	= $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE $where AND post_type in ($post_types) AND post_status = 'publish'");
		}

		$post_type	= 'any';

		if($post_id){
			$wp->set_query_var('post_type', $post_type);
			$wp->set_query_var('posts_per_page', 1);
			$wp->set_query_var('p', $post_id);
			$wp->set_query_var('name', '');
			$wp->set_query_var('pagename', '');

			$wp->query_posts();
		}else{
			wpjam_send_json(array(
				'errcode'	=> 'empty_query',
				'errmsg'	=> 'WP_Query 查询结果为空'
			));
		}

	}else{
		wpjam_send_json(array(
			'errcode'	=> 'empty_query',
			'errmsg'	=> 'WP_Query 查询结果为空'
		));
	}
}

$the_post	= wpjam_validate_post($post_id, $post_type);

if(is_wp_error($the_post)){
	wpjam_send_json($the_post);
}

$output				= ($output)?:$the_post->post_type;
$response['output']	= $output;

$response[$output]	= wpjam_get_post($post_id, $args);

if(is_multisite() && $_blog_id){
	restore_current_blog();
}

$response = apply_filters('wpjam_post_get_json', $response, $post_type, $post_id, $args);