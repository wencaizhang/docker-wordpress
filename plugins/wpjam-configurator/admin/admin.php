<?php
add_filter('wpjam_pages', function ($wpjam_pages){
	$wpjam_pages['wpjam-post-types'] = array(
		'menu_title'	=> '配置器',	
		'icon'			=> 'dashicons-wordpress',
		'position'		=> '58.88.1',
		'function'		=> 'list',
		'subs'			=> array(
			'wpjam-post-types'		=> [
				'menu_title'	=> '文章类型',		
				'function'		=> 'list',	
				'page_file'		=> WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/post-types.php'
			],
			'wpjam-post-options'	=> [
				'menu_title'	=> '文章选项',	
				'function'		=> 'list',
				'page_file'		=> WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/post-options.php'
			],
			'wpjam-taxonomies'		=> [
				'menu_title'	=> '自定义分类',
				'function'		=> 'list',
				'page_file'		=> WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/taxonomies.php'
			],
			'wpjam-term-options'	=> [
				'menu_title'	=> '分类选项',	
				'function'		=> 'list',
				'page_file'		=> WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/term-options.php'
			],
			'wpjam-settings'		=> [
				'menu_title'	=> '全局选项',
				'function'		=> 'list',
				'page_file'		=> WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/settings.php'
			],
			// 'wpjam-apis'			=> [
			// 	'menu_title'	=> '接口生成器',
			// 	'function'		=> 'list',
			// 	'page_file'		=> WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/apis.php'
			// ],
			'wpjam-configurator-templates'	=> [
				'menu_title'	=> '配置器模板',
				'function'		=> 'list',
				'page_file'		=> WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/configurator-templates.php'
			],
		)
	);

	if($settings = get_option('wpjam_settings')){
		$setting_subs	= [];
		foreach ($settings as $option_name => $args) {
			if(!empty($args['pt'])){
				$wpjam_pages[$args['pt'].'s']['subs'][$args['option_name']] = ['menu_title' => $args['title'],	'function'=>'option'];
			}else{
				$setting_subs[$args['option_name']] = ['menu_title' => $args['title'],	'function'=>'option'];
				$menu_title	= $menu_title ?? $args['title'];
				$menu_slug	= $menu_slug ?? $args['option_name'];
			}
		}

		if($setting_subs){
			$wpjam_pages[$menu_slug] = [
				'menu_title'	=> $menu_title,	
				'icon'			=> 'dashicons-admin-generic',
				'position'		=> '57.1111',
				'function'		=> 'option',
				'subs'			=> $setting_subs
			];
		}
	}	

	return $wpjam_pages;
});


add_action('admin_menu', function(){
	$post_types_settings	= get_option('wpjam_post_types');

	if(isset($post_types_settings['post']) && empty($post_types_settings['post']['active'])){
		remove_menu_page('edit.php');
	}

	if(isset($post_types_settings['page']) && empty($post_types_settings['page']['active'])){
		remove_menu_page('edit.php?post_type=page');
	}

	// $capabiltity = is_multisite()?'manage_sites':'manage_options';

	// if(!current_user_can($capabiltity)){
	// 	remove_menu_page('index.php');
	// 	remove_menu_page('tools.php');
	// 	remove_menu_page('options-general.php');
	// 	remove_menu_page('themes.php');
		
	// 	//remove_menu_page('upload.php');
	// 	// remove_menu_page('wpjam-basic');
	// 	// remove_menu_page('wpjam-devices');
	// }
	
	// if(!current_user_can('manage_options')){
	// 	remove_menu_page('users.php');
	// 	remove_menu_page('profile.php');
	// }
});