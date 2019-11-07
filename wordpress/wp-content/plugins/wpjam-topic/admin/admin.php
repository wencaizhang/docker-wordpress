<?php
include_once WPJAM_TOPIC_PLUGIN_DIR . 'public/upgrade.php';

add_filter('wpjam_pages', function ($wpjam_pages){
	if(!wpjam_get_topic_blog_id()){
		return $wpjam_pages;
	}
	
	$subs	= [];

	$subs['wpjam-topics']	= [
		'menu_title'	=> '讨论',
		'function'		=> 'list', 
		'capability'	=> 'read',
		'page_file'		=> WPJAM_TOPIC_PLUGIN_DIR.'admin/topics.php'
	];

	$subs['wpjam-groups']	= [
		'menu_title'	=> '分组',
		'function'		=> 'list', 
		'capability'	=> is_multisite() ? 'manage_sites' : 'manage_options',
		'page_file'		=> WPJAM_TOPIC_PLUGIN_DIR.'admin/groups.php'
	];

	$subs['wpjam-topic-messages']	= [
		'menu_title'	=> '消息',
		'capability'	=> 'read',
		'function'		=> 'wpjam_messages_page',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-messages.php'
	];

	$wpjam_pages['wpjam-topics']	= [
		'menu_title'	=> '讨论组',
		'function'		=> 'list', 
		'icon'			=> 'dashicons-format-chat',
		'position'		=> '59.9999',
		'capability'	=> 'read',
		'subs'			=> $subs
	];

	return $wpjam_pages;
});

add_action('admin_init', function(){
	if(!wpjam_is_topic_blog()){
		global $plugin_page;

		if(in_array($plugin_page, ['wpjam-topics', 'wpjam-groups'])){
			include WPJAM_TOPIC_PLUGIN_DIR . 'public/hooks.php';
		}
	}
},11);
