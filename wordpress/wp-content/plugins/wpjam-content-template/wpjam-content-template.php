<?php
/*
Plugin Name: WPJAM 内容模板
Plugin URI: http://blog.wpjam.com/project/wpjam-content-template/
Description: WordPress 内容模板，通过 shortcode 在内容中插入一段共用的内容模板，支持内容和表格模板。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR')){
	define('WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR',	plugin_dir_path(__FILE__));
	
	function wpjam_content_template_load(){	
		include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR . 'public/utils.php';
		include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR . 'public/hooks.php';
		include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR . 'public/post-password.php';
		include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR . 'public/upgrade.php';

		if(is_admin()){
			include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR . 'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_content_template_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_content_template_load');
	}
}
