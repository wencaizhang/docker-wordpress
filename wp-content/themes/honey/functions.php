<?php

if(!defined('WPJAM_BASIC_PLUGIN_FILE')){
	if(!is_admin()){
		wp_die('该主题基于 WPJAM Basic 插件开发，请先<a href="https://wordpress.org/plugins/wpjam-basic/">下载</a>并<a href="'.admin_url('plugins.php').'">激活</a> WPJAM Basic 插件。');
		exit;
	}
}else{
	
	include_once TEMPLATEPATH.'/public/theme_functions.php';

	if(is_admin()){
		include(TEMPLATEPATH.'/admin/admin.php');
	}
}

function wpjam_theme_get_setting($setting_name){
    return wpjam_get_setting('wpjam_theme', $setting_name);
}
