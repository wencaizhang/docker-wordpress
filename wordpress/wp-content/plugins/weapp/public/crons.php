<?php
add_action('weapp_scheduled_check', function(){
	$lock	= wp_cache_get('cron_lock', 'weapp');
	if($lock)	return;

	$type	= wp_cache_get('cron_type', 'weapp');
	$type 	= ($type)?:0;
	
	wp_cache_set('cron_lock', 1, 'weapp', 5);

	if($type == 0){
		weapp_send_template_messages();
	}elseif($type == 1){
		WEAPP_UserFormId::list_cache_to_db();
	}elseif($type == 2){
		if(current_time('H') == '4'){
			$delete	= wp_cache_get('delete', 'weapp');

			if($delete === false)	{
				wp_cache_set('delete', 1, 'weapp_crons', HOUR_IN_SECONDS);

				if(is_multisite() || weapp_get_setting('message')){
					include WEAPP_PLUGIN_DIR.'includes/class-weapp-message.php';

					WEAPP_Message::delete_old_messages();		// 删除超过一个月的消息	
				}
				
				WEAPP_UserFormId::delete_expired_form_ids();	// 删除过期的 form_id	
			}
		}
	}

	$type++;
	$type	= ($type > 3 )?0:$type;

	wp_cache_set('cron_type', $type, 'weapp', DAY_IN_SECONDS);

	// wp_cache_delete('cron_lock', 'weapp');

	if(!is_admin()) exit;
});