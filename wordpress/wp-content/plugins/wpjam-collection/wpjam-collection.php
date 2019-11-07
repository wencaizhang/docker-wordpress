<?php
/*
Plugin Name: WPJAM 图片集
Plugin URI: https://blog.wpjam.com/project/wpjam-collection/
Description: 1. 给媒体创建个分类「图片集 | collection」2. 图片分类限制为二级 3. 取消图片编辑入口 4. 附件页面直接图片链接。
Version: 2.1
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_COLLECTION_PLUGIN_DIR')){
	define('WPJAM_COLLECTION_PLUGIN_DIR', plugin_dir_path(__FILE__));

	function wpjam_collection_load(){
		include WPJAM_COLLECTION_PLUGIN_DIR.'public/hooks.php';

		if(is_admin()){
			include WPJAM_COLLECTION_PLUGIN_DIR.'admin/admin.php';
		}
	}

	if(did_action('wpjam_loaded')){
		wpjam_collection_load();
	}else{
		add_action('wpjam_loaded', 'wpjam_collection_load');
	}
}


