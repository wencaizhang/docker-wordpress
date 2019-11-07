<?php
add_action('plugins_loaded', function (){
	if(!wpjam_is_scheduled_event('weapp_scheduled_check')) {
		wp_schedule_event(time(), 'five_minutes', 'weapp_scheduled_check');
	}
});

add_filter('user_has_cap', function($allcaps, $caps, $args, $user){
	if(!empty($allcaps['manage_options'])){	// 管理员
		if(strpos($args[0], 'manage_weapp') !== false){	// 设置管理员有该博客下的所有小程序的权限
			$allcaps[$args[0]]	= 1;
		}
	}

	return $allcaps;
}, 10, 4);


register_activation_hook( WEAPP_PLUGIN_FILE , function(){
	include(WEAPP_PLUGIN_DIR.'includes/class-weapp-qrcode.php');
	include(WEAPP_PLUGIN_DIR.'includes/class-weapp-message.php');
	include(WEAPP_PLUGIN_DIR.'includes/class-weapp-reply-setting.php');
	include(WEAPP_PLUGIN_DIR.'includes/class-weapp-log.php');

	WEAPP_Setting::create_table();
	WEAPP_Qrcode::create_table();
	WEAPP_Message::create_table();
	WEAPP_ReplySetting::create_table();
	WEAPP_UserFormId::create_table();
	WEAPP_MasssendJob::create_table();
	WEAPP_MasssendLog::create_table();
	
	WEAPP_LOG::create_table();
});
