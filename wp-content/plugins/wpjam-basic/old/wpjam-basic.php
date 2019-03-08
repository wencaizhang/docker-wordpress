<?php
/*
Plugin Name: WPJAM BASIC
Plugin URI: http://wpjam.net/item/wpjam-basic/
Description: WPJAM 常用的函数和 Hook，屏蔽所有 WordPress 所有不常用的功能。「最新版本已包含<a href="https://wordpress.org/plugins/wpjam-qiniu/">七牛插件</a>，并兼容1.4.5及以上版本七牛插件。如果启用该版本插件，请先停用七牛1.4.5版本以下插件。」
Version: 3.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

define('WPJAM_BASIC_PLUGIN_URL', plugins_url('', __FILE__));
define('WPJAM_BASIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPJAM_BASIC_PLUGIN_FILE', __FILE__);

if (!function_exists('wpjam_option_page')) {
    include(WPJAM_BASIC_PLUGIN_DIR . 'include/wpjam-api.php');    // 加载 WPJAM 基础类库
}

// if(!function_exists('get_term_meta')){
// 	include(WPJAM_BASIC_PLUGIN_DIR.'include/simple-term-meta.php');
// }

include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-setting.php');    // 默认选项
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-functions.php');    // 常用函数
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-route.php');        // Module Action 路由
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-shortcode.php');    // Shortcode
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-cache.php');        // 缓存
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-cdn.php');        // CDN
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-thumbnail.php');    // 缩略图
include(WPJAM_BASIC_PLUGIN_DIR . 'term-thumbnail.php');    // term 缩略图
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-posts.php');        // 日志列表
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-stats.php');        // 统计
include(WPJAM_BASIC_PLUGIN_DIR . 'wpjam-mcrypt.php');        // 加密解密 class

if (is_admin()) {
    include(WPJAM_BASIC_PLUGIN_DIR . 'admin/admin.php');
}

wpjam_include_extends();    // 加载扩展