<?php
include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-setting.php');

add_filter('wpjam_weapp_settings_list_table', function(){
	return array(
		'title'			=> '小程序',
		'singular'		=> 'weapp-setting',
		'plural'		=> 'weapp-settings',
		'primary_column'=> 'name',
		'primary_key'	=> 'appid',
		'model'		 	=> 'WEAPP_AdminSetting',
		'capability'	=> is_multisite()?'manage_sites':'manage_options',
		'ajax'			=> true,
	);
});