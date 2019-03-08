<?php
/*
Plugin Name: WPJAM BASIC
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: WPJAM 常用的函数和 Hook，屏蔽所有 WordPress 所有不常用的功能。
Version: 3.5
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if (version_compare(PHP_VERSION, '7.2.0') < 0) {
	include plugin_dir_path(__FILE__).'old/wpjam-basic.php';
}else{
	define('WPJAM_BASIC_PLUGIN_URL', plugins_url('', __FILE__));
	define('WPJAM_BASIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('WPJAM_BASIC_PLUGIN_FILE',  __FILE__);

	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-model.php';		// Model 和其操作类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-api.php';			// 通用类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-field.php';		// 字段解析类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-cache.php';		// 缓存类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-thumbnail.php';	// 缩略图类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-post-type.php';	// Post Type 类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-taxonomy.php';		// Taxonomy 类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-notice.php';		// 消息通知类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-module.php';		// 模块化类

	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-core.php';					// 核心底层
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-functions.php';			// 常用函数
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-hooks.php';				// 基本优化
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-route.php';				// Module Action 路由
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-cdn.php';					// CDN
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-posts.php';				// 文章列表
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-shortcode.php';			// Shortcode
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-compat.php';				// 兼容代码 

	if(is_admin()) {
		include WPJAM_BASIC_PLUGIN_DIR.'admin/admin.php';
	}

	wpjam_include_extends();	// 加载扩展

	do_action('wpjam_loaded');
}


