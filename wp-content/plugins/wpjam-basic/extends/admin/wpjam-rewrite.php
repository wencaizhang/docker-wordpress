<?php
add_filter('wpjam_basic_sub_pages',function($subs){
	$subs['wpjam-rewrite']	= [
		'menu_title'	=> 'Rewrite设置',
		'function'		=> 'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-rewrite.php',
		'option_name'	=>'wpjam-basic'
	];
	return $subs;
});


