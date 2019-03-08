<?php
include(WPJAM_BASIC_PLUGIN_DIR.'admin/core/core.php');

include(WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/admin-menus.php');	// 后台菜单
include(WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/custom.php');		// 自定义后台
include(WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/stats.php');		// 后台统计基础函数
include(WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/users.php');		// 用户
include(WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/modules.php');		// 模块化后台

include(WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/verify.php');		// 验证
include(WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/topic.php');		// 讨论组

wpjam_include_extends($admin=true);	// 加载扩展，获取扩展的后台设置文件

add_action('plugins_loaded', function(){
	if(!wpjam_is_scheduled_event('wpjam_remove_invalid_crons')) {
		wp_schedule_event(time(), 'daily', 'wpjam_remove_invalid_crons');
	}

	if(!wpjam_is_scheduled_event('wpjam_scheduled_auto_draft_delete')) {
		wp_schedule_event(time(), 'hourly', 'wpjam_scheduled_auto_draft_delete');
	}

	if(!wpjam_is_scheduled_event('wpjam_scheduled_delete')) {
		wp_schedule_event(time(), 'hourly', 'wpjam_scheduled_delete');
	}
});

// 给测试版插件加上测试版标签
add_filter('all_plugins',function ($all_plugins){
	foreach($all_plugins as $plugin_file => $plugin_data){
		if(strpos($plugin_file, 'test') !== false || strpos($plugin_file, 'beta') !== false){
			$all_plugins[$plugin_file]['Name'] = $plugin_data['Name'].'《测试版》';
		}
	}
	return $all_plugins;
});

