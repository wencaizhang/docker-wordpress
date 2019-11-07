<?php
/*
Plugin Name: WPJAM #Hashtag# 
Plugin URI: http://blog.wpjam.com/project/wpjam-hashtag/
Description: 文章中插入 #话题标签#，如果是标签或者分类，则自动转换成标签或分类链接，否则跳转到搜索链接。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_HASHTAG_PLUGIN_DIR')){
	define('WPJAM_HASHTAG_PLUGIN_DIR',	plugin_dir_path(__FILE__));
	
	function wpjam_hashtag_load(){	
		include WPJAM_HASHTAG_PLUGIN_DIR . 'public/hooks.php';

		if(is_admin()){
			include WPJAM_HASHTAG_PLUGIN_DIR . 'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_hashtag_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_hashtag_load');
	}
}