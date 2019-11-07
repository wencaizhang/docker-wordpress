<?php
register_activation_hook(WEIXIN_ROBOT_PLUGIN_FILE, 'weixin_activation');
function weixin_activation() {
	flush_rewrite_rules();
	
	$administrator = get_role('administrator');
	$administrator->add_cap('view_weixin');
	$administrator->add_cap('edit_weixin');
	$administrator->add_cap('delete_weixin');
	$administrator->add_cap('masssend_weixin');

	include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-message.php');
	include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-reply-setting.php');	
	include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-menu.php');
	include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-qrcode.php');

	WEIXIN_Message::create_table();
	WEIXIN_Menu::create_table();
	WEIXIN_ReplySetting::create_table();
	WEIXIN_Qrcode::create_table();

	if(weixin_get_appid()){
		WEIXIN_User::create_table();
		if(weixin_get_type() == 4){
			WEIXIN_UserSubscribe::create_table();
		}
		
		$table	= WEIXIN_Message::get_table();
		weixin_table_add_appid($table);

		$table	= WEIXIN_Menu::get_table();
		weixin_table_add_appid($table);

		$table	= WEIXIN_Qrcode::get_table();
		weixin_table_add_appid($table);

		global $wpdb;

		$table	= $wpdb->prefix.'weixin_custom_replies'; 

		if($wpdb->get_var("show tables like '".$table."'") == $table){
			$replies	= $wpdb->get_results("SELECT `keyword`,`match`,`reply`,`status`,`type` FROM {$table}", ARRAY_A);

			WEIXIN_ReplySetting::insert_multi($replies);

			$wpdb->query("DROP TABLE {$table}");
		}

		$table	= WEIXIN_ReplySetting::get_table();
		weixin_table_add_appid($table);

		do_action('weixin_activation', weixin_get_appid());
	}

	
};

function weixin_table_add_appid($table){
	global $wpdb;

	if ($wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'appid'") != 'appid') {	
		$wpdb->query("ALTER TABLE $table ADD COLUMN appid varchar(32) NOT NULL");			// 添加 appid 字段
		
		$wpdb->query("UPDATE $table set appid = '".weixin_get_appid()."' WHERE appid = ''");	// 设置为当前公众号的 appid
	}
}


// add_action('delete_blog', function(){
// 	if($appid	= weixin_get_appid()){
// 		WEIXIN_Reply::Query()->delete(array('appid', $appid));
// 		WEIXIN_Menu::Query()->delete(array('appid', $appid));
// 		WEIXIN_Qrcode::Query()->delete(array('appid', $appid));

// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/trait-weixin-stats.php');
// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/class-weixin-user-stats.php');
// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/class-weixin-article-stats.php');
// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/class-weixin-userread-stats.php');
// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/class-weixin-usershare-stats.php');
// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/class-weixin-message-stats.php');
// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/class-weixin-messagedist-stats.php');
// 		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/mp-stats/class-weixin-interface-stats.php');

// 		WEIXIN_UserStats::Query()->delete(array('appid', $appid));
// 		WEIXIN_ArticleStats::Query()->delete(array('appid', $appid));
// 		WEIXIN_UserReadStats::Query()->delete(array('appid', $appid));
// 		WEIXIN_MessageStats::Query()->delete(array('appid', $appid));
// 		WEIXIN_UserShareStats::Query()->delete(array('appid', $appid));
// 		WEIXIN_MessageDistStats::Query()->delete(array('appid', $appid));
// 		WEIXIN_InterfaceStats::Query()->delete(array('appid', $appid));
// 	}
// });


// add_filter('wpmu_drop_tables', function($tables, $blog_id){
// 	global $wpdb;
// 	$blog_prefix 	= $wpdb->get_blog_prefix( $blog_id );
// 	foreach (weixin_robot_get_tables() as $function => $tables) {
// 		foreach ($tables as $table_name => $table_title) {
// 			$tables[] = $blog_prefix.$table_name;	
// 		}
// 	}
// 	return $tables;
// }, 10, 2);