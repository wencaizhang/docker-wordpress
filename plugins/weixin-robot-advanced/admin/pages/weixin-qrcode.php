<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-reply-setting.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-message.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-qrcode.php');

add_filter('wpjam_weixin_qrcode_list_table', function (){
	return [
		'title'				=> '带参数二维码',
		'singular'			=> 'weixin-qrcode',
		'plural'			=> 'weixin-qrcodes',
		'primary_column'	=> 'name',
		'primary_key'		=> 'id',
		'model'				=> 'WEIXIN_Qrcode',
		'style'				=> 'th.column-name{width:30%;}',
		'ajax'				=> true
	];
});

add_filter('wpjam_weixin_qrcode_stats_list_table', function (){
	return [
		'title'				=> '渠道统计分析',
		'singular'			=> 'weixin-qrcode',
		'plural'			=> 'weixin-qrcodes',
		'primary_column'	=> 'name',
		'primary_key'		=> 'id',
		'model'				=> 'WEIXIN_Qrcode',
		'actions'			=> [],
		'style'				=> 'th.column-name{width:30%;}'
	];
});