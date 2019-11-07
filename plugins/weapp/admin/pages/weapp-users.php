<?php
include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-user.php');

add_filter('wpjam_weapp_users_list_table', function(){
	return [
		'title'		 	=> '微信用户',
		'singular'		=> 'weapp-user',
		'plural'		=> 'weapp-users',
		'primary_column'=> 'nickname',
		'primary_key'	=> 'openid',
		'model'			=> 'WEAPP_AdminUser',
		'capability'	=> 'manage_weapp_'.weapp_get_appid(),
		'ajax'			=> true,
		'style'			=> '
			th.column-gender{width:28px;}
			th.column-username{width:200px;}
			th.column-time, th.column-modified{width:84px;}
		',
	];
});
