<?php
add_filter('wpjam_basic_sub_pages', function($subs){
	$subs['mobile-theme']	=[
		'menu_title'	=>'移动主题',
		'function'		=>'option',
		'option_name'	=>'wpjam-basic',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/mobile-theme.php',
	];

	return $subs;
});