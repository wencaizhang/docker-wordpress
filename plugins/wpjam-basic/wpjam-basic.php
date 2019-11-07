<?php
/*
Plugin Name: WPJAM BASIC
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: WPJAM 常用的函数和接口，屏蔽所有 WordPress 不常用的功能。
Version: 3.8.5
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if (version_compare(PHP_VERSION, '7.2.0') < 0) {
	include plugin_dir_path(__FILE__).'old/wpjam-basic.php';
}else{
	define('WPJAM_BASIC_PLUGIN_URL', plugins_url('', __FILE__));
	define('WPJAM_BASIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('WPJAM_BASIC_PLUGIN_FILE',  __FILE__);

	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-model.php';	// Model 和其操作类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-util.php';		// 通用工具类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-field.php';	// 字段解析类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-post.php';		// 文章操作类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-term.php';		// 分类操作类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-comment.php';	// 评论操作类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-notice.php';	// 消息通知类

	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-core.php';			// 核心底层
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-functions.php';	// 常用函数
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-hooks.php';		// 基本优化
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-route.php';		// 路由
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-cdn.php';			// CDN
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-posts.php';		// 文章列表
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-shortcodes.php';	// Shortcode
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-upgrade.php';		// 升级
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-extends.php';		// 扩展
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-compat.php';		// 兼容代码 

	if(is_admin()) {
		include WPJAM_BASIC_PLUGIN_DIR.'admin/admin.php';
	}

	do_action('wpjam_loaded');
}


