<?php
add_action('plugins_loaded', function(){
	wpjam_register_taxonomy('collection', [
		'object_type'			=> ['attachment'],
		'label'					=> apply_filters('wpjam_collection_label', '图片集'),	
		'public'				=> false,
		'query_var'				=> false,
		'rewrite'				=> false,
		'show_ui'				=> true,
		'filterable'			=> true,
		'levels'				=> 2,
		'supports'				=> ['name','parent','slug'],
		'update_count_callback'	=>'wpjam_update_collection_count_now'
	]);
});

add_filter('attachment_link', function($link, $post_id){
	return wp_get_attachment_url($post_id);
}, 10, 2);

function wpjam_update_collection_count_now( $terms, $taxonomy ) {
	$terms		= array_map('intval', $terms);
	$taxonomy	= get_taxonomy('collection');
	
	_update_generic_term_count($terms, $taxonomy);
	
	clean_term_cache($terms, '', false);

	return true;
}