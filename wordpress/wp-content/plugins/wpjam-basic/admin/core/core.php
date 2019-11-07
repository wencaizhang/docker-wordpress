<?php
function wpjam_admin_init(){
	global $pagenow, $plugin_page, $current_tab, $plugin_page_setting, $current_admin_url;

	if(wp_doing_ajax()){
		$plugin_page	= $_POST['plugin_page'] ?? '';
		$current_tab	= $_POST['current_tab'] ?? '';

		if(empty($plugin_page) || !in_array($_POST['action'], ['wpjam-page-action', 'wpjam-option-action', 'wpjam-list-table-action'])){
			return;
		}

		$add_menus	= false;
	}elseif($pagenow == 'options.php'){
		$add_menus	= false;
	}else{
		$add_menus	= true;
	}

	// 获取后台菜单
	if(is_multisite() && is_network_admin()){
		$wpjam_pages	=  apply_filters('wpjam_network_pages', []);

		if(!$wpjam_pages) {
			return;
		}

		$builtin_parent_pages	= [
			'settings'	=> 'settings.php',
			'theme'		=> 'themes.php',
			'themes'	=> 'themes.php',
			'plugins'	=> 'plugins.php',
			'users'		=> 'users.php',
			'sites'		=> 'sites.php',
		];
	}else{
		global $wpjam_pages, $wpjam_option_settings;
		$wpjam_pages	= $wpjam_pages ?? [];
		
		if(!empty($wpjam_option_settings)){
			foreach ($wpjam_option_settings as $option_name => $args){
				if(!empty($args['post_type'])){
					$wpjam_pages[$args['post_type'].'s']['subs'][$option_name] = ['menu_title' => $args['title'],	'function'=>'option'];
				}
			}
		}

		$wpjam_pages	= apply_filters('wpjam_pages', $wpjam_pages);

		if(!$wpjam_pages) {
			return;
		}

		$builtin_parent_pages	= [
			'management'=> 'tools.php',
			'options'	=> 'options-general.php',
			'theme'		=> 'themes.php',
			'themes'	=> 'themes.php',
			'plugins'	=> 'plugins.php',
			'posts'		=> 'edit.php',
			'media'		=> 'upload.php',
			'links'		=> 'link-manager.php',
			'pages'		=> 'edit.php?post_type=page',
			'comments'	=> 'edit-comments.php',
			'users'		=> current_user_can('edit_users')?'users.php':'profile.php',
		];
		
		if($custom_post_types = get_post_types(['_builtin' => false, 'show_ui' => true])){
			foreach ($custom_post_types as $custom_post_type) {
				$builtin_parent_pages[$custom_post_type.'s'] = 'edit.php?post_type='.$custom_post_type;
			}
		}
	}

	$plugin_page_setting	= null;
	$current_page_hook		= null;

	foreach ($wpjam_pages as $menu_slug=>$wpjam_page) {
		if(isset($builtin_parent_pages[$menu_slug])){
			$parent_slug = $builtin_parent_pages[$menu_slug];
		}else{
			if(empty($wpjam_page['menu_title'])){
				continue;
			}
			
			$menu_title	= $wpjam_page['menu_title'];
			$page_title	= $wpjam_page['page_title'] = $wpjam_page['page_title']?? $menu_title;

			if($plugin_page == $menu_slug){
				$plugin_page_setting	= $wpjam_page;

				$current_admin_url	= 'admin.php?page='.$plugin_page;
				$current_admin_url	= is_network_admin() ? network_admin_url($current_admin_url) : admin_url($current_admin_url);
			}

			if($add_menus){
				$capability	= $wpjam_page['capability'] ?? 'manage_options';
				$icon		= $wpjam_page['icon'] ?? '';
				$position	= $wpjam_page['position'] ?? '';

				$page_hook	= add_menu_page($page_title, $menu_title, $capability, $menu_slug, 'wpjam_admin_page', $icon, $position);

				if($plugin_page == $menu_slug){
					$current_page_hook	= $page_hook;
				}
			}

			$parent_slug	= $menu_slug;
		}

		if(!empty($wpjam_page['subs'])){
			foreach ($wpjam_page['subs'] as $menu_slug => $wpjam_page) {
				$menu_title	= $wpjam_page['menu_title'] ?? '';
				$page_title	= $wpjam_page['page_title'] = $wpjam_page['page_title'] ?? $menu_title;

				if($plugin_page == $menu_slug){
					$plugin_page_setting	= $wpjam_page;

					if(in_array($parent_slug, $builtin_parent_pages)){
						$current_admin_url	= $parent_slug;
						$current_admin_url 	.= strpos($current_admin_url, '?') ? '&page='.$plugin_page : '?page='.$plugin_page;
					}else{
						$current_admin_url	= 'admin.php?page='.$plugin_page;
					}

					$current_admin_url	= is_network_admin() ? network_admin_url($current_admin_url) : admin_url($current_admin_url);

					if(!$add_menus){
						break;
					}
				}

				if($add_menus){
					$capability	= $wpjam_page['capability'] ?? 'manage_options';
					$page_hook	= add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, 'wpjam_admin_page');
					
					if($plugin_page == $menu_slug){
						$current_page_hook	= $page_hook;
					}
				}
			}	
		}

		if(!$add_menus && $plugin_page_setting){
			break;
		}
	}

	if($plugin_page_setting){
		if($current_page_hook){
			$plugin_page_setting['page_hook']	= $current_page_hook;
		}

		if(!empty($plugin_page_setting['page_file'])){
			include $plugin_page_setting['page_file'];
		}

		global $current_option, $current_list_table, $current_dashboard, $current_query_data;

		$current_query_data	= [];

		$query_args	= $plugin_page_setting['query_args'] ?? [];
		if($query_args){
			foreach($query_args as $query_arg) {
				$current_query_data[$query_arg]	= wpjam_get_data_parameter($query_arg);
			}

			$current_admin_url	= add_query_arg($current_query_data, $current_admin_url);
		}

		$function	= $plugin_page_setting['function'] ?? null;

		if($function == 'tab'){
			$current_tab	= $current_tab ?? ($_GET['tab'] ?? '');

			$tabs	= $plugin_page_setting['tabs'] ?? [];
			$tabs	= apply_filters(wpjam_get_filter_name($plugin_page, 'tabs'), $tabs);

			if(wp_doing_ajax()){
				if(!$tabs) {
					wpjam_send_json([
						'errcode'	=> 'empty_tabs',
						'errmsg'	=> 'Tabs 未设置',
					]);
				}

				if(empty($current_tab) || empty($tabs[$current_tab])){
					wpjam_send_json([
						'errcode'	=> 'invalid_tab',
						'errmsg'	=> '非法Tab',
					]);
				}
			}else{
				if(!$tabs) {
					wp_die('Tabs 未设置');
				}

				$tab_keys		= array_keys($tabs);
				$current_tab	= $current_tab ?: $tab_keys[0];	
				
				if(empty($tabs[$current_tab])){
					wp_die('非法Tab');
				}
			}

			$plugin_page_setting['tabs']	= $tabs;
			$plugin_page_setting['tab_url']	= $current_admin_url;

			$current_tab_setting	= $tabs[$current_tab];

			if(!empty($current_tab_setting['tab_file'])){
				include $current_tab_setting['tab_file'];
			}

			$current_admin_url		= $current_admin_url.'&tab='.$current_tab;
			
			$query_args	= $current_tab_setting['query_args'] ?? [];
			if($query_args){
				foreach($query_args as $query_arg) {
					$current_query_data[$query_arg]	= wpjam_get_data_parameter($query_arg);
				}

				$current_admin_url	= add_query_arg($current_query_data, $current_admin_url);
			}

			$function	= $current_tab_setting['function'] ?? null;

			if($function == 'option'){
				$current_option	= $current_tab_setting['option_name'] ?? $plugin_page;
			}elseif($function == 'list' || $function == 'list_table'){
				$current_list_table	= $current_tab_setting['list_table_name'] ?? $plugin_page;
			}elseif($function == 'dashboard'){
				$current_dashboard	= $current_tab_setting['dashboard_name'] ?? $plugin_page;
			}
		}elseif($function == 'option'){
			$current_option	= $plugin_page_setting['option_name'] ?? $plugin_page;
		}elseif($function == 'list' || $function == 'list_table'){
			$current_list_table	= $plugin_page_setting['list_table_name'] ?? $plugin_page;
		}elseif($function == 'dashboard'){
			$current_dashboard	= $plugin_page_setting['dashboard_name'] ?? $plugin_page;
		}

		if($current_option){
			include WPJAM_BASIC_PLUGIN_DIR.'admin/core/options.php';
		}elseif($current_list_table){
			include WPJAM_BASIC_PLUGIN_DIR.'admin/core/list-table.php';
		}elseif($current_dashboard){
			include WPJAM_BASIC_PLUGIN_DIR.'admin/core/dashboard.php';
		}else{
			add_action('load-'.$current_page_hook, function(){
				$action	= $_GET['action'] ?? '';
				if($action && in_array($action, ['add','edit','set','bulk-edit'])) {
					return;
				}

				global $plugin_page;

				do_action($plugin_page.'_page_load');	// 等着旧的 list table 升级慢慢取消
				// do_action_deprecated($plugin_page.'_page_load', [], 'WPJAM Basic 3.4');	// 等着旧的 list table 升级慢慢取消
			});	
		}
	}

	return $current_page_hook;
}

function wpjam_admin_load(){
	if(wp_doing_ajax()){
		$ajax_action	= $_POST['action'] ?? '';

		if(in_array($ajax_action, ['wpjam-list-table-action', 'wpjam-page-action']) && !empty($_POST['screen_id'])){
			$current_screen = WP_Screen::get($_POST['screen_id']);

			if($current_screen->base == 'edit'){
				$pagenow	= 'edit.php';
				$post_type	= $current_screen->post_type;
			}elseif($current_screen->base == 'upload'){
				$pagenow	= 'upload.php';
				$post_type	= 'attachment';
			}elseif($current_screen->base == 'post'){
				$pagenow	= 'post.php';
				$post_type	= $current_screen->post_type;
			}elseif($current_screen->base == 'edit-tags'){
				$pagenow	= 'edit-tags.php';
				$taxonomy	= $current_screen->taxonomy;
			}else{
				wpjam_print_r($current_screen);
			}	
		}elseif($ajax_action == 'inline-save'){
			$pagenow	= 'edit.php';
			$post_type	= $_POST['post_type'] ?? '';
		}elseif(in_array($ajax_action, ['add-tag', 'inline-save-tax'])){
			$pagenow	= 'edit-tags.php';
			$taxonomy 	= $_POST['taxonomy'] ?? '';
		}elseif($ajax_action == 'get-comments'){
			$pagenow	= 'edit-comments.php';
		}else{
			return;
		}
	}else{
		global $pagenow, $plugin_page, $current_screen;

		if($plugin_page){
			return;
		}

		$post_type	= $current_screen->post_type ?? '';
		$taxonomy	= $current_screen->taxonomy ?? '';
	}

	if($pagenow == 'index.php'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/core/dashboard.php';	
	}elseif($pagenow == 'post.php' || $pagenow == 'post-new.php'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/core/post.php';
	}elseif($pagenow == 'edit.php' || $pagenow == 'upload.php'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/core/post-list.php';
	}elseif($pagenow == 'term.php' || $pagenow == 'edit-tags.php') {
		include WPJAM_BASIC_PLUGIN_DIR.'admin/core/term-list.php';
	}elseif($pagenow == 'edit-comments.php'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/core/comment-list.php';
	}elseif($pagenow == 'users.php'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/core/user-list.php';
	}elseif($pagenow == 'user-edit.php' || $pagenow == 'profile.php'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/core/user.php';
	}
}

// 内部的 hook 使用 优先级 9
// 因为内嵌的 hook 优先级要低
add_action('wp_loaded', function(){	
	if(wp_doing_ajax()){
		add_action('admin_init', function(){
			if(!empty($_POST['plugin_page'])){
				wpjam_admin_init();
			}else{
				wpjam_admin_load();
			}
		}, 9);
	}else{
		global $pagenow;

		if($pagenow == 'options.php'){
			// 为了实现多个页面使用通过 option 存储。
			// 注册设置选项，选用的是：'admin_action_' . $_REQUEST['action'] 的filter，
			// 因为在这之前的 admin_init 检测 $plugin_page 的合法性
			add_action('admin_action_update', function(){
				global $plugin_page, $current_tab;

				$referer_origin	= parse_url(wpjam_get_referer());

				if(!empty($referer_origin['query']))	{
					$referer_args	= wp_parse_args($referer_origin['query']);

					$plugin_page	= $referer_args['page'] ?? '';	// 为了实现多个页面使用通过 option 存储。
					$current_tab	= $_POST['current_tab'] ?? '';

					wpjam_admin_init();
				}
			}, 9);
		}else{
			add_action('network_admin_menu',	'wpjam_admin_init',	9);
			add_action('admin_menu', 			'wpjam_admin_init',	9);
			add_action('current_screen',		'wpjam_admin_load',	9);
		}
	}
});