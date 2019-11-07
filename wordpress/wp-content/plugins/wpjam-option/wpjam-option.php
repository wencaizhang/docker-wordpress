<?php
/*
Plugin Name: WPJAM 站点选项
Plugin URI: http://blog.wpjam.com/project/wpjam-option/
Description: 查看和管理 WordPress 设置。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_OPTION_PLUGIN_DIR')){
	define('WPJAM_OPTION_PLUGIN_DIR', plugin_dir_path(__FILE__));
	
	function wpjam_option_load(){
		if(is_admin()){
			include WPJAM_OPTION_PLUGIN_DIR.'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_option_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_option_load');
	}
}