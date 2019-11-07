<?php
include WPJAM_AVATAR_PLUGIN_DIR.'admin/hooks.php';

add_action('wpjam_user_page_file', function(){
	include WPJAM_AVATAR_PLUGIN_DIR.'admin/avatar.php';
});

add_filter('wpjam_pages', function ($wpjam_pages){
	$wpjam_pages['users']['subs']['wpjam-avatar']	= [
		'menu_title'	=> '默认头像',
		'function'		=> 'option', 
		'option_name'	=> 'wpjam-avatar', 
		'page_file'		=> WPJAM_AVATAR_PLUGIN_DIR.'admin/setting.php'
	];

	return $wpjam_pages;
});

