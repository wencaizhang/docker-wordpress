<?php
add_filter('wpjam_basic_sub_pages', function($subs){
	$subs['301-redirects']	= array('menu_title'=>'301跳转', 	'function'=>'option');
	return $subs;
});

add_filter('wpjam_301_redirects_setting', function(){
	return array(
		'option_type'	=> 'single',
		'summary'		=> '该功能只能跳转 404 页面到正常页面，可以正常访问页面无法设置 301 跳转。',
		'fields'		=> array(
			'301-redirects'	=> array('title'=>'', 'type'=>'mu-fields', 'fields'=>array(
				'request'		=>	array('title'=>'原地址',		'type'=>'url'),
				'destination'	=>	array('title'=>'目标地址',	'type'=>'url'),
			))
		)
	);
});