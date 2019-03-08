<?php
add_filter('wpjam_basic_sub_pages',function($subs){
	$subs['wpjam-stats']	= [
		'menu_title'	=> '统计代码',
		'option_name'	=> 'wpjam-basic',
		'function'		=> 'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-stats.php'
	];

	return $subs;
});