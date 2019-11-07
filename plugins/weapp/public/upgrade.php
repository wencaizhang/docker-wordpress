<?php
function weapp_upgrade(){
	$appid = weapp_get_appid();

	if(empty($appid)){
		return;
	}

	$weapp_db_version	= weapp_get_setting('db_version') ?: 0;

	if($weapp_db_version === 0){
		$weapp_db_version	= get_option('weapp_db_version') ?: 0;
		if($weapp_db_version){
			delete_option('weapp_db_version');
			weapp_update_setting('db_version', $weapp_db_version);
		}
	}

	if($weapp_db_version >= 3.1){
		return;
	}
		
	weapp_update_setting('db_version', 3);

	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$weapp_setting	= weapp_get_setting($appid);

	if($weapp_db_version < 3){
		delete_option('weapp_'.$appid.'_templates');
		
		$table	= WEAPP_User::get_table();

		if ($wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'user_id'") != 'user_id') {
			$wpdb->query("ALTER TABLE {$table} ADD `user_id` BIGINT(20) NOT NULL");

			if(!$wpdb->query("SHOW INDEX FROM `{$table}` WHERE Key_name='user_idx'")){
				$wpdb->query("ALTER TABLE `{$table}` ADD KEY `user_idx` (`user_id`);");
			}
		}
	}

	if($weapp_db_version < 2){
		if(!empty($weapp_setting['topic']) && !is_plugin_active('weapp-topic/weapp-topic.php')){
			activate_plugins(['weapp-topic/weapp-topic.php']);
		}

		if(!empty($weapp_setting['mag']) && !is_plugin_active('weapp-mag/weapp-mag.php')){
			activate_plugins(['weapp-mag/weapp-mag.php']);
		}
	}	
}

weapp_upgrade();