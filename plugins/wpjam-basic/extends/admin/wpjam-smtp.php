<?php
add_filter('wpjam_basic_sub_pages',function($subs){
	$subs['wpjam-smtp']	= [
		'menu_title'	=> '发信设置',
		'function'		=> 'tab',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-smtp.php'
	];
	return $subs;
});


