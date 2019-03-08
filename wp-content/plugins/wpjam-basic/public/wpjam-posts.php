<?php
// WP_Query 缓存
function wpjam_query($args=[], $cache_time='600'){
	return WPJAM_Cache::query($args, $cache_time);
}

function wpjam_validate_post($post_id, $post_type='', $action=''){
	return WPJAM_PostType::validate($post_id, $post_type, $action);
}

function wpjam_get_post($post_id, $args=[]){
	return WPJAM_PostType::parse_for_json($post_id, $args);
}

function wpjam_get_posts($post_ids, $args=[]){
	if(class_exists('WPJAM_PostType')){
		return WPJAM_PostType::get_posts($post_ids, $args);
	}else{
		return [];
	}
}

function wpjam_get_post_views($post_id, $type='views'){
	return WPJAM_PostType::get_views($post_id, $type);
}

function wpjam_update_post_views($post_id, $type='views'){
	return WPJAM_PostType::update_views($post_id, $type);
}

function wpjam_get_post_excerpt($post=null, $excerpt_length=240){
	return WPJAM_PostType::get_excerpt($post, $excerpt_length);
}
	
if(!function_exists('get_post_excerpt')){   
	//获取日志摘要
	function get_post_excerpt($post=null, $excerpt_length=240){
		return WPJAM_PostType::get_excerpt($post, $excerpt_length);
	}
}

function wpjam_get_related_posts_query($number=5, $post_type=null){
	return WPJAM_PostType::related_query($number, $post_type);
}

function wpjam_related_posts($args=[]){
	echo wpjam_get_related_posts($args);
}

function wpjam_get_related_posts($args=[]){
	$args	= apply_filters('wpjam_related_posts_args', $args);

	$post_type	= $args['post_type'] ?? null;
	$number		= $args['number'] ?? null;

	$related_query	= WPJAM_PostType::related_query($number, $post_type);
	$related_posts	= WPJAM_PostType::parse_post_list($related_query, $args);

	return $related_posts;
}

function wpjam_get_new_posts($args=[]){
	$wpjam_query	= wpjam_query(array(
		'posts_per_page'=> $args['number'] ?? 5, 
		'post_type'		=> $args['post_type'] ?? 'post', 
		'orderby'		=> $args['orderby'] ?? 'date', 
	));

	return WPJAM_PostType::parse_post_list($wpjam_query, $args);
}

function wpjam_new_posts($args=[]){
	echo wpjam_get_new_posts($args);
}

function wpjam_get_top_viewd_posts($args=[]){
	$date_query	= array();

	if(isset($args['days'])){
		$date_query	= array(array(
			'column'	=> $args['column']??'post_date_gmt',
			'after'		=> $args['days'].' days ago',
		));
	}

	$wpjam_query	= wpjam_query(array(
		'posts_per_page'=> $args['number'] ?? 5, 
		'post_type'		=> $args['post_type']??['post'], 
		'orderby'		=> 'meta_value_num', 
		'meta_key'		=> 'views', 
		'date_query'	=> $date_query 
	));

	return WPJAM_PostType::parse_post_list($wpjam_query, $args);
}

function wpjam_top_viewd_posts($args=[]){
	echo wpjam_get_top_viewd_posts($args);
}

function wpjam_get_post_list($wpjam_query, $args=[]){
	return WPJAM_PostType::parse_post_list($wpjam_query, $args);
}

function wpjam_get_term($term, $taxonomy, $children_terms=[], $max_depth=-1, $depth=0){
	return WPJAM_Taxonomy::get_term($term, $taxonomy, $children_terms, $max_depth, $depth);
}

/**
 * $max_depth = -1 means flatly display every element.
 * $max_depth = 0 means display all levels.
 * $max_depth > 0 specifies the number of display levels.
 *
 */
function wpjam_get_terms($args, $max_depth=-1){
	return WPJAM_Taxonomy::get_terms($args, $max_depth);
}
