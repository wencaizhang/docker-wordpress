<?php
add_filter('wpjam_pages', function($wpjam_pages){
	$wpjam_pages['themes']['subs']['mobile-theme']	=[
		'menu_title'	=>'移动主题',
		'function'		=>'option',
		'option_name'	=>'wpjam-basic',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/mobile-theme.php',
	];

	return $wpjam_pages;
});