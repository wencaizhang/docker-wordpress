<?php
function wpjam_topic_upgrade($force=false){
	$switched	= wpjam_topic_switch_to_blog();

	$version	= get_option('wpjam_topic_version');

	if($force || $version < 2){
		update_option('wpjam_topic_version', 2);

		global $wpdb;
		
		$table	= $wpdb->posts;

		if(!$wpdb->query("SHOW COLUMNS FROM `{$table}` WHERE field='last_comment_time'")){
			$wpdb->query("ALTER TABLE `{$table}` ADD COLUMN last_comment_time int(10) NULL");
			$wpdb->query("ALTER TABLE `{$table}` ADD COLUMN last_comment_user bigint(20) NULL");
			$wpdb->query("ALTER TABLE `{$table}` ADD KEY `last_comment_time_idx` (`last_comment_time`);");
		}
	}

	if($switched){
		restore_current_blog();
	}
}

wpjam_topic_upgrade();