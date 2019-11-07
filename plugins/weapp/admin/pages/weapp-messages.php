<?php
include(WEAPP_PLUGIN_DIR.'includes/class-weapp-message.php');
include(WEAPP_PLUGIN_DIR.'includes/class-weapp-reply-setting.php');

include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-message.php');
include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-reply-setting.php');

add_filter('wpjam_weapp_messages_list_table', function(){
	return array(
		'title'				=> '消息',
		'singular'			=> 'weapp-message',
		'plural'			=> 'weapp-messages',
		'primary_column'	=> 'FromUserName',
		'primary_key'		=> 'id',
		'model'				=> 'WEAPP_AdminMessage',
		'capability'		=> 'manage_weapp_'.weapp_get_appid(),
		'style'				=> '
		th.column-MsgType{width:28px;}
		th.column-username{width:260px;}
		th.column-CreateTime{width:84px;}
		',
	);
});