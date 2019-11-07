<?php
/*
Plugin Name: WPJAM 配置器
Plugin URI: http://blog.wpjam.com/
Description: 全自动 WordPress 配置器，支持自定义文章类型，自定义字段，自定义分类，分类选项，全局选项和接口生成器。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_CONFIGURATOR_PLUGIN_DIR')){
	define('WPJAM_CONFIGURATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));

	function wpjam_configurator_load(){
		include WPJAM_CONFIGURATOR_PLUGIN_DIR.'public/utils.php';
		include WPJAM_CONFIGURATOR_PLUGIN_DIR.'public/hooks.php';

		if(is_admin()){
			include WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_configurator_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_configurator_load');
	}
}