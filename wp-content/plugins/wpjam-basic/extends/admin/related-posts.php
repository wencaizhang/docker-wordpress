<?php
add_filter('wpjam_basic_sub_pages', function($subs){
	$subs['related-posts']	=[
		'menu_title'	=>'相关文章',
		'function'		=>'option', 
		'option_name'	=>'wpjam-basic', 
		'page_file'		=>WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/related-posts.php'
	];

	return $subs;
}, 1);