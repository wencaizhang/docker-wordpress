<?php
if(wpjam_is_topic_blog()){
	add_action('init', function(){
		wpjam_register_post_type('topic',	[
			'label'					=> '贴子',
			'public'				=> true,
			'exclude_from_search'	=> true,
			'show_ui'				=> false,
			'show_in_nav_menus'		=> false,
			'rewrite'				=> true,
			'query_var'				=> false,
			'has_archive'			=> true,
			'supports'				=> ['title','editor','author','comments'],
			'permastruct'			=> 'topic/%post_id%/'
		]);	

		wpjam_register_taxonomy('group',	[
			'label'				=> '分组',
			'public'			=> true,
			'hierarchical'		=> true,
			'show_ui'			=> false,
			'show_in_nav_menus'	=> true,
			'rewrite'			=> true,
			'query_var'			=> true,
			'object_type'		=> ['topic'],
			'capabilities'		=> [
				'manage_terms'	=> 'manage_categories',
				'edit_terms'	=> 'edit_categories',
				'delete_terms'	=> 'delete_categories',
				'assign_terms'	=> 'read',
			],
			'supports'		=> ['name', 'slug', 'order'],
			'levels'		=> 1
		]);
	});
}else{
	register_post_type('topic',	[
		'label'					=> '贴子',
		'public'				=> false,
		'exclude_from_search'	=> true,
		'show_ui'				=> false,
		'show_in_nav_menus'		=> false,
		'rewrite'				=> false,
		'query_var'				=> false,
		'has_archive'			=> false,
		'supports'				=> ['title','editor','author','comments']
	]);	

	register_taxonomy('group',	'topic',[
		'label'				=> '分组',
		'public'			=> false,
		'show_ui'			=> false,
		'show_in_nav_menus'	=> false,
		'rewrite'			=> false,
		'query_var'			=> true,
		'capabilities'		=> [
			'manage_terms'	=> 'manage_categories',
			'edit_terms'	=> 'edit_categories',
			'delete_terms'	=> 'delete_categories',
			'assign_terms'	=> 'read',
		],
		'supports'		=> ['name', 'slug', 'order'],
		'levels'		=> 1
	]);
}

	
add_filter('user_has_cap', function($allcaps){
	$allcaps['read']	= 1;
	return $allcaps;
});


add_filter('posts_orderby', function($orderby, $wp_query){
	if(isset($wp_query->query['orderby']) && $wp_query->query['orderby'] == 'last_comment_time'){
		$order	= $wp_query->query['order'] ?? 'DESC';
		return 'last_comment_time '.$order;
	}else{
		return $orderby;
	}
}, 10, 2);

add_filter('wp_insert_post_data', function($data, $postarr){
	$post_type	= $postarr['post_type'] ?? '';

	if($post_type == 'topic'){
		if(isset($postarr['last_comment_time'])){
			$data['last_comment_time']	= $postarr['last_comment_time'];
		}

		if(isset($postarr['last_comment_user'])){
			$data['last_comment_user']	= $postarr['last_comment_user'];
		}
	}

	return $data;
}, 10, 2);