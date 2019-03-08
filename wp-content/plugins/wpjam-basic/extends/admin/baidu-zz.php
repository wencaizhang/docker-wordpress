<?php
add_filter('wpjam_basic_sub_pages', function($subs){
	$subs['baidu-zz']	=[
		'menu_title'	=>'百度站长',
		'function'		=>'tab',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/baidu-zz.php',
	];

	return $subs;
});

add_action('wpjam_post_page_file', function($post_type){
	include WPJAM_BASIC_PLUGIN_DIR.'extends/admin/posts/baidu-zz-post.php';
});




