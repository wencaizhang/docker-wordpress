<?php
/*
Plugin Name: WPJAM 分类管理
Plugin URI: https://blog.wpjam.com/project/wpjam-taxonomy-levels/
Description: 层式管理分类和分类拖动排序，支持设置分类的层级，并且在 WordPress 后台分类管理界面可以按层级显示和拖动排序。
Version: 3.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_TAXONOMY_PLUGIN_DIR')){
	define('WPJAM_TAXONOMY_PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('WPJAM_TAXONOMY_LEVELS_PLUGIN_DIR', WPJAM_TAXONOMY_PLUGIN_DIR);

	function wpjam_taxonomy_load(){
		include WPJAM_TAXONOMY_PLUGIN_DIR.'public/hooks.php';
		
		if(is_admin()){
			include WPJAM_TAXONOMY_PLUGIN_DIR.'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_taxonomy_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_taxonomy_load');
	}
}