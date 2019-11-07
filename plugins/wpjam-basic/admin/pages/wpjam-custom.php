<?php
add_filter('wpjam_basic_setting', function(){
	$admin_fields = [
		'admin_logo'	=> ['title'=>'后台左上角 Logo',		'type'=>'img',	'item_type'=>'url',	'description'=>'建议大小：20x20。'],
		'admin_head'	=> ['title'=>'后台 Head 代码 ',		'type'=>'textarea',	'class'=>''],
		'admin_footer'	=> ['title'=>'后台 Footer 代码',		'type'=>'textarea',	'class'=>'']
	];

	$custom_fields = [
		'head'			=> ['title'=>'前台 Head 代码',		'type'=>'textarea',	'class'=>''],
		'footer'		=> ['title'=>'前台 Footer 代码',		'type'=>'textarea',	'class'=>''],
	];

	$login_fields = [
		// 'login_logo'			=> ['title'=>'登录界面 Logo',		'type'=>'img',		'description'=>'建议大小：宽度不超过600px，高度不超过160px。'),
		'login_head'	=> ['title'=>'登录界面 Head 代码',	'type'=>'textarea',	'class'=>''],
		'login_footer'	=> ['title'=>'登录界面 Footer 代码',	'type'=>'textarea',	'class'=>''],
		'login_redirect'=> ['title'=>'登录之后跳转的页面',		'type'=>'text'],
	];

	$summary	= '通过该功能可以对网站的前端或者后台的样式进行定制，详情请点击<a href="https://blog.wpjam.com/m/wpjam-basic-custom-setting/"  target="_blank">样式定制</a>介绍。';

	$sections	= [ 
		'wpjam-custom'	=> ['title'=>'前台定制',	'fields'=>$custom_fields,	'summary'=>$summary],
		'admin-custom'	=> ['title'=>'后台定制',	'fields'=>$admin_fields,	'summary'=>$summary],
		'login-custom'	=> ['title'=>'登录界面', 	'fields'=>$login_fields,	'summary'=>$summary]
	];

	return compact('sections');
});