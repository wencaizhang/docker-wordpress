<?php
/*
Plugin Name: WPJAM 文章专题
Plugin URI: http://blog.wpjam.com/project/wpjam-series/
Description: 创建一个「文章专题」自定义分类，并且在在文章末尾显示专题文章列表。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_SERIES_PLUGIN_DIR')){
	define('WPJAM_SERIES_PLUGIN_DIR',	plugin_dir_path(__FILE__));

	function wpjam_series_load(){

		include WPJAM_SERIES_PLUGIN_DIR . 'public/hooks.php';

		if(is_admin()){
			include WPJAM_SERIES_PLUGIN_DIR . 'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_series_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_series_load');
	}
}
