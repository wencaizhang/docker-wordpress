<?php
/*
Plugin Name: 微信小程序
Plugin URI: https://blog.wpjam.com/project/weapp/
Description: 微信小程序基础类库和管理
Version: 3.0
Author: Denis
Author URI: https://blog.wpjam.com/
*/
define('WEAPP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WEAPP_PLUGIN_FILE',  __FILE__);
define('WEAPP_MEDIA_DIR', WP_CONTENT_DIR.'/uploads/weapp');
define('WEAPP_MEDIA_URL', WP_CONTENT_URL.'/uploads/weapp');

if(!defined('WPJAM_PLUGIN_WEIXIN_PAY_CERT_DIR')){
	define('WPJAM_PLUGIN_WEIXIN_PAY_CERT_DIR', '/data/www/weixin.pay.cert');
}


function weapp_load(){
	// 基本类库，其他类库要接口自己加载
	include WEAPP_PLUGIN_DIR.'includes/class-weapp.php';
	include WEAPP_PLUGIN_DIR.'includes/class-weapp-setting.php';
	include WEAPP_PLUGIN_DIR.'includes/trait-weapp.php';
	include WEAPP_PLUGIN_DIR.'includes/class-weapp-user.php';
	include WEAPP_PLUGIN_DIR.'includes/class-weapp-user-form-id.php';
	include WEAPP_PLUGIN_DIR.'includes/class-weapp-template.php';
	include WEAPP_PLUGIN_DIR.'includes/class-weapp-masssend.php';

	include WEAPP_PLUGIN_DIR.'public/global-cache-group.php';
	include WEAPP_PLUGIN_DIR.'public/utils.php';
	include WEAPP_PLUGIN_DIR.'public/group-share.php';
	include WEAPP_PLUGIN_DIR.'public/crons.php';
	include WEAPP_PLUGIN_DIR.'public/hooks.php';
	include WEAPP_PLUGIN_DIR.'public/upgrade.php';
	
	if(is_admin()){
		include WEAPP_PLUGIN_DIR.'admin/admin.php';
	}

	do_action('weapp_loaded');
}

if(did_action('wpjam_loaded')){
	weapp_load();
}else{
	add_action('wpjam_loaded', 'weapp_load');
}

