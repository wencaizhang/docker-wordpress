<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-menu.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-messages.php');

add_action('wpjam_weixin_menu_tabs', function($tabs){
	$tabs =  [
		'default'		=> ['title'=>'自定义菜单',		'function'=>'list',	'list_table_name'=>'weixin-menu-button'],
		// 'conditional'	=> ['title'=>'个性化菜单',		'function'=>'list',	'list_table_name'=>'weixin-menu'],
		'menu'			=> ['title'=>'菜单点击统计',		'function'=>'weixin_message_stats_page'],
		'tree'			=> ['title'=>'默认菜单汇总统计',	'function'=>'list',	'list_table_name'=>'weixin-menu-button']
	];

	if(weixin_get_type() == 2){
		// unset($tabs['conditional']);
	}else{
		global $current_tab;
		
		if($current_tab == 'buttons' || (!empty($_GET['tab']) && $_GET['tab'] == 'buttons')){
			if($menu_id	= wpjam_get_data_parameter('menu_id')){
				if($menu = WEIXIN_Menu::get($menu_id)){
					$menu_name	= $menu['name'] ?: $menu['menuid'];
					
					$tabs['buttons']	= ['title'=>$menu_name,	'function'=>'list',	'list_table_name'=>'weixin-menu-button', 'query_args'=>['menu_id']];
				}else{
					wp_die('该菜单不存在');
				}
			}
		}
	}

	return $tabs;
});

add_filter('wpjam_weixin_menu_button_list_table', function(){

	return [
		'title'			=> (weixin_get_type() >= 3)?'默认菜单':'自定义菜单',
		'singular'		=> 'weixin-button',
		'plural'		=> 'weixin-buttons',
		'primary_key'	=> 'pos',
		'model'			=> 'WEIXIN_MenuButton',
		'ajax'			=> true,
	];
});

add_filter('wpjam_weixin_menu_list_table', function(){
	return [
		'title'				=> '个性化菜单',
		'singular'			=> 'weixin-menu',
		'plural'			=> 'weixin-menus',
		'primary_column'	=> 'name',
		'model'				=> 'WEIXIN_Menu',
		'ajax'				=> true,
	];
});
