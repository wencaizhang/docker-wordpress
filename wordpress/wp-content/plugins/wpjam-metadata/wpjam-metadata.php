<?php
/*
Plugin Name: WPJAM MetaData
Plugin URI: https://blog.wpjam.com/project/wpjam-metadata/
Description: 可视化管理 WordPress Meta 数据，支持所有内置的 Meta 数据：Post Meta，Term Meta，User Meta 和 Comment Meta。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_METADATA_PLUGIN_DIR')){
	define('WPJAM_METADATA_PLUGIN_DIR', plugin_dir_path(__FILE__));
	
	if(is_admin()){
		include WPJAM_METADATA_PLUGIN_DIR.'admin/admin.php';
	}
}