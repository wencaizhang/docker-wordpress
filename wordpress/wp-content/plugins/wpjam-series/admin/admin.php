<?php
add_filter('wpjam_basic_sub_pages', function($subs){
	$subs['wpjam-series']	=[
		'menu_title'	=>'文章专题',
		'function'		=>'option',
		'option_name'	=>'wpjam-basic',
		'page_file'		=> WPJAM_SERIES_PLUGIN_DIR.'admin/settings.php'
	];

	return $subs;
});


add_filter('wpjam_term_options', function($term_options){
	$term_options['guide']	= [
		'title'			=> '引导语',
		'type'			=> 'checkbox',
		'description'	=> '将简介放到文章开头作为引导语',	
		'taxonomy'		=> 'series'
	];

	return $term_options;
});