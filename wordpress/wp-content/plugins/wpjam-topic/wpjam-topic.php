<?php
/*
Plugin Name: WPJAM 讨论组
Plugin URI: http://blog.wpjam.com/project/wpjam-basic/
Description: WPJAM 讨论组
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_TOPIC_PLUGIN_DIR')){
	define('WPJAM_TOPIC_PLUGIN_DIR',	plugin_dir_path(__FILE__));
	define('WPJAM_TOPIC_PLUGIN_FILE', 	__FILE__);
	
	function wpjam_topic_load(){
		include WPJAM_TOPIC_PLUGIN_DIR . 'public/utils.php';

		if(wpjam_is_topic_blog()){
			include WPJAM_TOPIC_PLUGIN_DIR . 'public/hooks.php';
		}

		if(is_admin()){
			include WPJAM_TOPIC_PLUGIN_DIR . 'admin/admin.php';
		}
	}

	if(did_action('plugins_loaded')){
		wpjam_topic_load();
	}else{
		add_action('plugins_loaded', 'wpjam_topic_load');
	}
}