<?php
// global $wpdb;
// $wpdb->weixin_messages		= WEIXIN_Message::get_table();
// $wpdb->weixin_subscribes	= WEIXIN_UserSubscribe::get_table();;
// $wpdb->weixin_users			= WEIXIN_User::get_table();

// 在插件页面添加快速设置链接
add_filter('plugin_action_links_' . plugin_basename(WEIXIN_ROBOT_PLUGIN_FILE), function( $links, $file ) {
	$links['setting']	= '<a href="'.admin_url('admin.php?page=weixin').'">设置</a>';
	return array_reverse($links);
}, 10, 2 );


add_filter('user_has_cap', function($allcaps, $caps, $args, $user){
	if(!empty($allcaps['manage_options'])){	// 管理员
		if($args[0] == 'view_weixin'){		// 设置管理员有 view_weixin 权限
			$allcaps[$args[0]]	= 1;
		}
	}

	return $allcaps;
}, 10, 4);

$today	= date('Y-m-d', current_time('timestamp'));

// 只保留三个月的消息
if(!wpjam_is_scheduled_event('weixin_delete_messages')) {
	$time	= strtotime(get_gmt_from_date($today.' 02:00:00')) + rand(0,7200);
	wp_schedule_event( $time, 'twicedaily', 'weixin_delete_messages' );
}


// if(!wpjam_is_scheduled_event('weixin_get_users')) {	
// 	$today	= date('Y-m-d', current_time('timestamp'));
// 	$time	= strtotime(get_gmt_from_date($today.' 03:00:00')) + rand(0,7200);

// 	wp_schedule_event( $time, 'daily', 'weixin_get_users' );
// }