<?php
add_filter('wpjam_basic_sub_pages',function($subs){
	$subs['wpjam-smtp']	= [
		'menu_title'	=> '发信设置',
		'function'		=> 'tab',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-smtp.php',
		'tabs'			=> [
			'smtp'	=> ['title'=>'发信设置',	'function'=>'option', 'option_name'=>'wpjam-basic'],
			'send'	=> ['title'=>'发送测试',	'function'=>'wpjam_smtp_send_page'],
		]
	];
	return $subs;
});


