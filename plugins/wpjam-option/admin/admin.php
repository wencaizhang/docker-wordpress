<?php
$sub_pages_filter	= defined('WPJAM_DEBUG_PLUGIN_DIR') ? 'wpjam_debug_sub_pages' : 'wpjam_basic_sub_pages';

add_filter($sub_pages_filter, function($subs){
	if(!is_multisite() || !is_network_admin()){

		$capability	= is_multisite() ? 'manage_sites' : 'manage_options';

		$subs['wpjam-options']	=[
			'menu_title'	=> '站点选项', 
			'page_file'		=> WPJAM_OPTION_PLUGIN_DIR .'admin/options.php',		
			'capability'	=> $capability,
			'function'		=> 'list'
		];
	}

	return $subs;
});