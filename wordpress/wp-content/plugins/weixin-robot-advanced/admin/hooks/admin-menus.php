<?php
// 后台菜单
add_filter('wpjam_pages', function ($wpjam_pages){
	$base		= 'weixin';
	$menu_icon	= weixin_get_icon();

	if(!WPJAM_Verify::verify()){
		$wpjam_pages[$base] = [
			'menu_title'	=> '微信公众号',
			'icon'			=> $menu_icon,
			'capability'	=> 'view_weixin',
			'position'		=> '3.91',	
			'function'		=> 'wpjam_verify_page',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR .'admin/pages/wpjam-verify.php',	
		];

		return $wpjam_pages;
	}

	if(!weixin_get_appid()){
		$wpjam_pages[$base] = [
			'menu_title'	=> '微信公众号',
			'icon'			=> $menu_icon,
			'capability'	=> 'view_weixin',
			'position'		=> '3.9.1',
			'function'		=> 'option',
			'option_name'	=> 'weixin-robot',
			'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-setting.php'
		];

		return $wpjam_pages;
	}

	// 微信管理菜单
	$subs	= [];

	$subs[$base]	= [
		'menu_title'	=> '数据预览',
		'function'		=>'dashboard',
		'capability'	=> 'view_weixin',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-stats.php',
	];

	if(weixin_get_type() >= 2) {
		$subs['weixin-menu']	= [
			'menu_title'	=> '自定义菜单',	
			'function'		=> 'tab',
			'capability'	=> 'view_weixin',
			'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-menu.php',
		];
	}

	if(weixin_has_feature('weixin_reply')){
		$subs['weixin-replies']	= [
			'menu_title'	=> '自定义回复',	
			'function'		=> 'tab',
			'capability'	=> 'view_weixin',
			'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-replies.php',
		];

		$subs['weixin-messages']	= [
			'menu_title'	=> (weixin_get_type() >= 3) ? '消息管理' : '消息统计',	
			'function'		=> 'tab',
			'capability'	=> 'view_weixin',
			'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-messages.php',
		];
		
	}

	if(weixin_get_type() >= 3) {
		$subs['weixin-material']	= [
			'menu_title'	=> '素材管理',
			'function'		=> 'tab',
			'capability'	=> 'view_weixin',
			'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-material.php'
		];
		
		if(weixin_get_type() == 4){
			$subs['weixin-qrcode']	= [
				'menu_title'	=> '渠道管理',
				'function'		=> 'list',
				'capability'	=> 'view_weixin',
				'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-qrcode.php'
			];
		}

		$subs['weixin-users']		= [
			'menu_title' 	=> '用户管理',	
			'function'		=> 'tab',
			'capability'	=> 'view_weixin',
			'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-users.php',
		];

		// $subs[$base.'-masssend']	= [
		// 	'menu_title'	=> '群发消息',	
		// 	'function'		=> 'tab',
		// 	'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-masssend.php',
		// ];

		// $weixin_setting	= weixin_get_setting();

		// if(!empty($weixin_setting['weixin_dkf'])){
		// 	$subs[$base.'-customservice']	=	[
		// 		'menu_title'	=> '客服管理',
		// 		'function'		=> 'list',
		// 		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-customservice.php',
		// 	];
		// }

		
	}else{
		$subs['weixin-users']		= [
			'menu_title' 	=> '用户统计',	
			'function'		=> 'tab',
			'capability'	=> 'view_weixin',
			'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-users.php',
		];
	}

	$subs = apply_filters('weixin_sub_pages', $subs);

	$subs['weixin-extend']	= [
		'menu_title'	=> '扩展管理',	
		'function'		=> 'tab',
		'capability'	=> 'manage_options',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-extend.php',
	];

	$subs['weixin-setting']	= [
		'menu_title'	=> '设置',	
		'function'		=> 'option',
		'option_name'	=> 'weixin-robot',
		'capability'	=> 'manage_options',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-setting.php'
	];

	$wpjam_pages[$base] = [
		'menu_title'	=> '微信公众号',
		'function'		=> 'option',
		'icon'			=> $menu_icon,
		'capability'	=> 'view_weixin',
		'position'		=> '3.91',
		'subs'			=> $subs
	];

	return $wpjam_pages;
});

add_filter('wpjam_network_pages', function ($wpjam_pages){
	$wpjam_pages['weixin-extend'] = [
		'menu_title'	=> '微信公众号',
		'icon'			=> weixin_get_icon(),
		'capability'	=> 'view_weixin',
		'position'		=> '3.91',
		'function'		=> 'option',
		'option_name'	=> 'weixin_extends',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-extend.php',
	];

	return $wpjam_pages;
});

function weixin_get_icon(){
	return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCwgMCwgNDAwLCA0MDAiPgogIDxnIGlkPSJMYXllciAxIj4KICAgIDxwYXRoIGQ9Ik0xMjAuNjAxLDQ2LjU3NiBDOS4yNDEsNjYuNDY2IC0yNy44NzksMTkyLjI4MSA2MC43LDI0OS44NjkgQzY1LjU1NywyNTIuOTkxIDY1LjU1NywyNTIuNjQ1IDU4LjI3MSwyNzQuMzg1IEw1Mi4wMjcsMjkzLjAwMiBMNzQuNDYxLDI4MC45NzYgTDk2Ljg5NSwyNjguOTUgTDEwOC44MDYsMjcxLjg0MSBDMTIxLjI5NCwyNzQuOTYzIDEzNy4yNTMsMjc3LjE2IDE0Ny44OTEsMjc3LjE2IEwxNTQuMjUyLDI3Ny4xNiBMMTUyLjA1NCwyNjguNzE4IEMxMzQuNTkzLDIwNC40MjMgMTk0Ljk1NiwxNDAuNzA2IDI3My40NzUsMTQwLjcwNiBMMjg0LjExNCwxNDAuNzA2IEwyODEuOTE3LDEzMy4wNzQgQzI2NC42ODYsNzIuODI2IDE5MS45NSwzMy44NTYgMTIwLjYwMSw0Ni41NzYgeiBNMTEwLjg4NywxMDIuODkyIEMxMjIuNjgyLDExMC44NzIgMTIzLjM3NiwxMjguMTAyIDExMi4wNDMsMTM1LjUwMyBDOTMuNjU3LDE0Ny41MjkgNzIuMTQ4LDEyNi4zNjcgODQuNTIxLDEwOC4zMjcgQzg5Ljk1NiwxMDAuMjMzIDEwMy4wMjQsOTcuNTczIDExMC44ODcsMTAyLjg5MiB6IE0yMDUuNzExLDEwMi44OTIgQzIyNS4xMzgsMTE1Ljk2IDIxMC41NjgsMTQ2LjE0MSAxODguODI3LDEzNy44MTUgQzE3My4xMDEsMTMxLjgwMiAxNzEuMjUsMTEwLjE3OCAxODUuOTM2LDEwMi40MyBDMTkxLjcxOCw5OS4zMDggMjAwLjczOCw5OS41MzkgMjA1LjcxMSwxMDIuODkyIHogTTI0OC42MTMsMTUwLjUzNiBDMTkzLjQ1MywxNjAuNTk2IDE1NS4xNzcsMjAyLjQ1NyAxNTcuMzc0LDI1MC41NjMgQzE2MC4yNjUsMzE0Ljk3NCAyMzUuNzc3LDM1OS4zNzkgMzA4LjI4MiwzMzkuNDg5IEwzMTYuODM5LDMzNy4xNzYgTDMzNC44NzksMzQ2Ljg5IEMzNDQuODI0LDM1Mi4zMjUgMzUzLjE1LDM1Ni4yNTcgMzUzLjM4MSwzNTUuNzk0IEMzNTMuNjEzLDM1NS4yMTYgMzUxLjY0NywzNDguMjc4IDM0OS4xMDMsMzQwLjI5OSBDMzQzLjMyMSwzMjIuNDkgMzQzLjIwNSwzMjMuNzYyIDM1MC45NTMsMzE4LjIxMiBDNDM4LjE0NCwyNTUuNjUxIDM2MS41OTIsMTMwLjA2OCAyNDguNjEzLDE1MC41MzYgeiBNMjQ2LjQxNiwyMDIuNDU3IEMyNTEuMjcyLDIwNS42OTUgMjUzLjgxNiwyMTMuNzkgMjUxLjczNSwyMTkuNjg4IEMyNDcuMzQxLDIzMi4yOTIgMjI4LjQ5MiwyMzMuMjE3IDIyMy40MDMsMjIxLjA3NSBDMjE3LjYyMSwyMDcuMDgzIDIzMy41OCwxOTQuMTMxIDI0Ni40MTYsMjAyLjQ1NyB6IE0zMjMuNjYyLDIwMy44NDUgQzMzMS4yOTQsMjExLjEzIDMzMC4wMjIsMjIzLjUwNCAzMjEuMTE4LDIyOC4xMjkgQzMwNy40NzMsMjM1LjA2NyAyOTMuMTM0LDIyMS4xOTEgMzAwLjE4OCwyMDcuODkyIEMzMDQuODEzLDE5OS4zMzUgMzE2LjcyNCwxOTcuMjU0IDMyMy42NjIsMjAzLjg0NSB6IE0yMjAuNDMsMzI4Ljc3MiIgZmlsbD0iI2ZmZiIvPgogIDwvZz4KICA8ZGVmcy8+Cjwvc3ZnPg==';
}