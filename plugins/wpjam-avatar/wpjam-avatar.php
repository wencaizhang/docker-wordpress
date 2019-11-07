<?php
/*
Plugin Name: WPJAM 用户头像
Plugin URI: http://blog.wpjam.com/project/wpjam-avatar/
Description: 让用户在 WordPress 后台自定义头像和设置默认头像。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_AVATAR_PLUGIN_DIR')){
	define('WPJAM_AVATAR_PLUGIN_DIR',	plugin_dir_path(__FILE__));

	function wpjam_avatar_load(){
		include WPJAM_AVATAR_PLUGIN_DIR.'public/hooks.php';

		if(is_admin()){
			include WPJAM_AVATAR_PLUGIN_DIR.'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_avatar_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_avatar_load');
	}
}
