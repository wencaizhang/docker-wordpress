<?php
/*
Plugin Name: 微信机器人高级版
Plugin URI: http://blog.wpjam.com/
Description: 微信机器人的主要功能就是能够将你的公众账号和你的 WordPress 博客联系起来，搜索和用户发送信息匹配的日志，并自动回复用户，让你使用微信进行营销事半功倍。
Version: 5.2.6
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if (version_compare(PHP_VERSION, '7.2.0') < 0) {
	include(plugin_dir_path(__FILE__).'php5/weixin-robot-advanced.php');
}else{
	define('WEIXIN_ROBOT_PLUGIN_URL', plugins_url('', __FILE__));
	define('WEIXIN_ROBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('WEIXIN_ROBOT_PLUGIN_FILE',  __FILE__);
	define('WEIXIN_ROBOT_PLUGIN_TEMP_URL', WP_CONTENT_URL.'/uploads/weixin/');
	define('WEIXIN_ROBOT_PLUGIN_TEMP_DIR', WP_CONTENT_DIR.'/uploads/weixin/');
	define('WEIXIN_CUSTOM_SEND_LIMIT', time()-2*DAY_IN_SECONDS);

	function weixin_robot_loaded(){
		include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/trait-weixin.php');
		include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin.php');					// 微信基本类	
		include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-user.php');				// 微信用户类
		include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-user-subscribe.php');	// 微信用户渠道类
		
		include(WEIXIN_ROBOT_PLUGIN_DIR.'public/utils.php');	// 基本函数
		include(WEIXIN_ROBOT_PLUGIN_DIR.'public/crons.php');	// 基本函数

		weixin_include_extends();	 // 扩展

		if(is_admin()){
			include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/admin.php');		// 插件后台
		}

		do_action('weixin_loaded');
	}

	if(did_action('wpjam_loaded')){
		weixin_robot_loaded();
	}else{
		add_action('wpjam_loaded', 'weixin_robot_loaded');
	}
}


