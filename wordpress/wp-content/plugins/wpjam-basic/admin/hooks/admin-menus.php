<?php
// 设置菜单
function wpjam_basic_admin_pages($wpjam_pages){
	$wpjam_pages['users']['subs']['wpjam-messages'] 		= [
		'menu_title'	=> '站内消息',
		'capability'	=> 'read',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-messages.php'
	];
		
	$capability	= (is_multisite())?'manage_sites':'manage_options';
	$subs		= [];

	$subs['wpjam-basic']	= [
		'menu_title'	=> '优化设置',	
		'function'		=> 'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-basic.php'
	];

	$subs['wpjam-custom']	= [
		'menu_title'	=> '样式定制', 
		'function'		=> 'option',
		'option_name'	=> 'wpjam-basic',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-custom.php'
	];
	
	$verified	= WPJAM_Verify::verify();

	if(!$verified){
		$subs['wpjam-verify']	= [
			'menu_title'	=> '扩展管理',
			'function'		=> 'wpjam_verify_page',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-verify.php'
		];

		$subs['wpjam-about']	= [
			'menu_title'	=> '关于WPJAM',	
			'function'		=> 'wpjam_basic_about_page',	
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-about.php'
		];

		$wpjam_pages['wpjam-basic']	= [
			'menu_title'	=> 'WPJAM',	
			'icon'			=> 'dashicons-performance',
			'position'		=> '58.99',
			'subs'			=> $subs
		];

		return $wpjam_pages;
	}

	$subs['wpjam-cdn']	= [
		'menu_title'	=> 'CDN加速', 
		'function'		=> 'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-cdn.php'
	];

	$subs['wpjam-thumbnail']	= [
		'menu_title'	=> '缩略图设置', 
		'function'		=> 'option',
		'option_name'	=> 'wpjam-cdn',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-thumbnail.php'
	];

	$subs = apply_filters('wpjam_basic_sub_pages', $subs);

	$subs['server-status']	= [
		'menu_title'	=> '系统信息',		
		'function'		=> 'tab',	
		'capability'	=> $capability,	
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR .'admin/pages/server-status.php'
	];

	if(!is_multisite() || !is_network_admin()){
		$subs['wpjam-crons']		= [
			'menu_title'	=> '定时作业',		
			'function'		=> 'list',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR .'admin/pages/wpjam-crons.php'
		];
		
		$subs['dashicons']		= [
			'menu_title'	=> 'Dashicons',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR .'admin/pages/dashicons.php',	
		];
	}

	$subs['wpjam-extends']	= [
		'menu_title'	=> '扩展管理',
		'function'		=> 'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-extends.php'
	];

	if($verified !== 'verified'){
		$subs['wpjam-basic-topics']	= [
			'menu_title'	=> '讨论组',
			'function'		=> 'tab',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-topics.php'
		];
		
		$subs['wpjam-about']	= [
			'menu_title'	=> '关于WPJAM',
			'function'		=> 'wpjam_basic_about_page',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-about.php'
		];
	}

	$wpjam_pages['wpjam-basic']	= [
		'menu_title'	=> 'WPJAM',	
		'icon'			=> 'dashicons-performance',
		'position'		=> '58.99',	
		'function'		=> 'option',	
		'subs'			=> $subs
	];

	return $wpjam_pages;
}
add_filter('wpjam_pages', 'wpjam_basic_admin_pages');
add_filter('wpjam_network_pages', 'wpjam_basic_admin_pages');
  
add_action('admin_menu', function () {
	global $menu, $submenu;
	$menu['58.88']	= ['',	'read',	'separator'.'58.88', '', 'wp-menu-separator'];
}); 


