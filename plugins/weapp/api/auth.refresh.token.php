<?php
$refresh_token	= wpjam_get_parameter('refresh_token', array('required'=> true));
$openid 		= WEAPP_User::get_openid_by_refresh_token($refresh_token);
if(is_wp_error($openid)){
	wpjam_send_json($openid);
}

weapp_set_current_openid($openid);

wpjam_send_json(array(
	'errcode'		=> 0,
	'access_token'	=> WEAPP_User::generate_access_token($openid),
	'refresh_token'	=> WEAPP_User::generate_refresh_token($openid),
	'expired_in'	=> DAY_IN_SECONDS*7 - 600,
	'user'			=> wpjam_get_user($openid),
));
