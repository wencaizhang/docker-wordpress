<?php
add_filter('wpjam_pages', function ($wpjam_pages){
	if(is_multisite()){
		$wpjam_pages['weapp-settings'] = array(
			'menu_title'	=>'微信小程序',	
			'icon'			=> 'dashicons-paperclip',
			'position'		=> '3.0',
			'function'		=> 'list',
			'capability'	=> 'manage_sites',
			'page_file'		=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-settings.php'
		);
	}

	global $weapp_count;

	if(is_multisite()){
		$weapp_settings	= WEAPP_Setting::get_settings(get_current_blog_id());
		$weapp_count	= count($weapp_settings);
	}else{
		$weapp_settings	= [weapp_get_setting()];
		$weapp_count 	= 1;
	}
	
	$weapp_position	= 3.1;
	
	foreach ($weapp_settings as $weapp_setting) {
		$weapp_subs		= [];
		$weapp_appid	= $weapp_setting['appid'] ?? '';

		if(is_multisite()){
			$capability		= 'manage_weapp_'.$weapp_appid;
			$base_menu		= ($weapp_count == 1)?'weapp':'weapp-'.$weapp_appid;

			$weapp_subs[$base_menu]	= [
				'menu_title'	=> '小程序设置',	
				'function'		=> 'option',	
				'capability'	=> $capability,	
				'option_name'	=> 'weapp_'.$weapp_appid,
				'page_file'		=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-setting.php'
			];
		}else{
			$capability		= 'manage_weapp';
			$base_menu		= 'weapp';

			$weapp_subs[$base_menu]	= [
				'menu_title'	=> '小程序设置',	
				'function'		=> 'option',	
				'capability'	=> $capability,	
				'option_name'	=> 'wpjam_weapp',
				'page_file'		=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-setting.php'
			];
		}

		if($weapp_setting && $weapp_appid){
			$weapp_setting['position']	= $weapp_position;

			$weapp_subs[$base_menu.'-users']	= [
				'menu_title'		=> '用户管理',	
				'function'			=> 'list',	
				'list_table_name'	=> 'weapp_users',
				'capability'		=> $capability,
				'page_file'			=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-users.php'
			];

			if(!empty($weapp_setting['qrcode'])){
				$weapp_subs[$base_menu.'-qrcodes']	= [
					'menu_title'		=> '二维码管理',
					'function'			=> 'list',
					'list_table_name'	=> 'weapp_qrcodes',
					'capability'		=> $capability,
					'page_file'			=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-qrcodes.php'
				];
			}

			if(!empty($weapp_setting['template'])){
				$weapp_subs[$base_menu.'-templates']	= [
					'menu_title'	=> '模板消息配置',
					'function'		=> 'tab',
					'page_file'		=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-templates.php',
					'capability'	=> $capability,
					'tabs'			=> [
						'mine'		=> ['title'=>'我的模板',	'function'=>'list',	'list_table_name'=>'weapp_templates'],
						'library'	=> ['title'=>'模板库',	'function'=>'list',	'list_table_name'=>'weapp_templates']
					]
				];
			}

			if(!empty($weapp_setting['message'])){
				$weapp_subs[$base_menu.'-replies']	= array(
					'menu_title'		=> '自定义回复',
					'function'			=> 'list',
					'list_table_name'	=> 'weapp_replies',
					'page_file'			=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-replies.php',
					'capability'		=> $capability
				);

				$weapp_subs[$base_menu.'-messages']	= array(
					'menu_title'		=> '消息管理',
					'function'			=> 'list',
					'list_table_name'	=> 'weapp_messages',
					'page_file'			=> WEAPP_PLUGIN_DIR.'admin/pages/weapp-messages.php',
					'capability'		=> $capability
				);
			}
		}

		if(is_multisite()){
			$wpjam_pages[$base_menu] = array(
				'menu_title'	=> $weapp_setting['name'] ?: '未命名小程序',	
				'icon'			=> 'dashicons-paperclip',
				'position'		=> (string)$weapp_position,
				'function'		=> 'list',
				'subs'			=> $weapp_subs,
				'capability'	=> $capability
			);
		}else{
			$wpjam_pages[$base_menu] = array(
				'menu_title'	=> '微信小程序',	
				'icon'			=> 'dashicons-paperclip',
				'position'		=> (string)$weapp_position,
				'function'		=> 'list',
				'subs'			=> $weapp_subs,
				'capability'	=> $capability
			);
		}

		if($weapp_setting){
			$weapp_position	+= 0.01;
			$weapp_setting['position']	= $weapp_position;
			$wpjam_pages = apply_filters('wpjam_weapp_pages', $wpjam_pages, $weapp_setting, $weapp_count);
		}
	}
	
	return $wpjam_pages;
});
