<?php
function is_login() {
    return in_array($pagenow, ['wp-login.php', 'wp-register.php']);
}

if(PHP_VERSION < 7.2){
	if(!is_admin()&&!is_login()){
		wp_die('Sweet 主题需要PHP 7.2，你的服务器 PHP 版本为：'.PHP_VERSION.'，请升级到 PHP 7.2。');
		exit;
	}
}elseif(!defined('WPJAM_BASIC_PLUGIN_FILE')){
	if(!is_admin()){
		wp_die('Sweet 主题基于 WPJAM Basic 插件开发，请先<a href="https://wordpress.org/plugins/wpjam-basic/">下载</a>并<a href="'.admin_url('plugins.php').'">激活</a> WPJAM Basic 插件。');
		exit;
	}
}else{

	function wpjam_theme_get_setting($setting_name){
		return wpjam_get_setting('wpjam_theme', $setting_name);
	}
	
	include_once TEMPLATEPATH.'/public/sweet-functions.php';
	include_once TEMPLATEPATH.'/public/xintheme-block.php';
	include_once TEMPLATEPATH.'/public/comment.php';

	if(is_admin()){
		include(TEMPLATEPATH.'/admin/admin.php');
	}
}

add_theme_support('title-tag');
add_theme_support('post-thumbnails');

register_nav_menus(['main'	=> '主菜单']);
