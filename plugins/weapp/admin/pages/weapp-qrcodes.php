<?php
include(WEAPP_PLUGIN_DIR.'includes/class-weapp-qrcode.php');
include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-qrcode.php');

add_filter('wpjam_weapp_qrcodes_list_table', function(){
	return array(
		'title'				=> '二维码',
		'singular'			=> 'weapp-qrcode',
		'plural'			=> 'weapp-qrcodes',
		'primary_column'	=> 'nickname',
		'primary_key'		=> 'id',
		'model'				=> 'WEAPP_AdminQrcode',
		'ajax'				=> true,
		'search'			=> true,
		'capability'		=> 'manage_weapp_'.weapp_get_appid(),
	);
});
