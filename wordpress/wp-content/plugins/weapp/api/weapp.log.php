<?php
include(WP_CONTENT_DIR.'/plugins/weapp/weapp.php');
include(WP_CONTENT_DIR.'/plugins/weapp/includes/class-weapp-log.php');

$response		= array('errcode'=>0);

$appid			= weapp_get_appid();
if(empty($appid)){
	wpjam_send_json($response);
}

$vendor			= wpjam_get_parameter('vendor',	array('method' => 'POST'));
$level			= wpjam_get_parameter('level',	array('method' => 'POST', 'default'=>1));
$page			= wpjam_get_parameter('page',	array('method' => 'POST', 'default'=>''));

$access_token	= wpjam_get_parameter('access_token', array('method' => 'GET'));
$openid			= weapp_get_current_openid();
$openid			= (is_wp_error($openid))?'':$openid;

$ua				= wpjam_get_user_agent();
$ip_data	 	= wpjam_parse_ip();

$country		= $ip_data['country'];
$region			= $ip_data['region'];
$city			= $ip_data['city'];

$device			= wpjam_get_parameter('device',		array('method' => 'POST'));
$os				= wpjam_get_parameter('os',			array('method' => 'POST'));
$os_ver			= wpjam_get_parameter('os_ver',		array('method' => 'POST'));
$weixin_ver		= wpjam_get_parameter('weixin_ver',	array('method' => 'POST'));

$time			= time();

WEAPP_LOG::insert(compact('appid', 'vendor', 'level', 'openid', 'ip', 'country', 'region', 'city', 'ua', 'device', 'os', 'os_ver', 'weixin_ver', 'time'));

wpjam_send_json($response);