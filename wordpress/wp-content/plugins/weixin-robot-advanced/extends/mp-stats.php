<?php
/*
Plugin Name: 公众号数据
Plugin URI: http://blog.wpjam.com/project/weixin-robot-advanced/
Description: 公众号数据
Version: 1.0
Author URI: http://blog.wpjam.com/
*/

if(weixin_get_type() >= 3){
	add_action('weixin_get_mp_stats', function($type='all'){
		if($type == 'all'){
			wp_schedule_single_event(time()+5, 'weixin_get_mp_stats',array('user'));
			wp_schedule_single_event(time()+10,'weixin_get_mp_stats',array('article'));
			wp_schedule_single_event(time()+15,'weixin_get_mp_stats',array('userread'));
			wp_schedule_single_event(time()+20,'weixin_get_mp_stats',array('usershare'));
			wp_schedule_single_event(time()+25,'weixin_get_mp_stats',array('message'));
			wp_schedule_single_event(time()+30,'weixin_get_mp_stats',array('messagedist'));
			wp_schedule_single_event(time()+35,'weixin_get_mp_stats',array('interface'));
		}else{
			include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/trait-weixin-stats.php');

			if($type == 'user'){
				include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-user-stats.php');
				$result	= WEIXIN_UserStats::sync();
			}elseif($type == 'article'){
				include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-article-stats.php');
				$result	= WEIXIN_ArticleStats::sync();
			}elseif($type == 'userread'){
				include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-userread-stats.php');
				$result	= WEIXIN_UserReadStats::sync();
			}elseif($type == 'usershare'){
				include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-usershare-stats.php');
				$result	= WEIXIN_UserShareStats::sync();
			}elseif($type == 'message'){
				include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-message-stats.php');
				$result	= WEIXIN_MessageStats::sync();
			}elseif($type == 'messagedist'){
				include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-messagedist-stats.php');
				$result	= WEIXIN_MessageDistStats::sync();
			}elseif($type == 'interface'){
				include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-interface-stats.php');
				$result	= WEIXIN_InterfaceStats::sync();
			}

			if(is_wp_error($result)){
				if($result->get_error_code() != 'no_more_history_stats_data'){
					trigger_error($type . ' ' . var_export($result, true));	
				}

				if($result->get_error_code() != 'http_request_failed'){
					exit;
				}
			}elseif(empty($result)){
				trigger_error($type . ' result empty');
			}

			wp_schedule_single_event(time()+10+rand(1,10),'weixin_get_mp_stats',array($type));
			
			if(!is_admin()){
				exit;
			}
		}

	},10,1);
}