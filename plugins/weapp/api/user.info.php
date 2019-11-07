<?php

WPJAM_API::method_allow('POST');

$iv				= wpjam_get_parameter('iv',				['method'=>'POST',	'required'=>true]);
$encrypted_data	= wpjam_get_parameter('encrypted_data',	['method'=>'POST',	'required'=>true]);

$session_key	= weapp_get_session_key();

if(is_wp_error($session_key)){
	wpjam_send_json($session_key);
}

$user_info		= $weapp->decrypt_user($session_key, $iv, $encrypted_data);
if(is_wp_error($user_info)){
	// wpjam_send_json($user_info);
	wpjam_send_json([
		'errcode'	=> 'bad_authentication',
		'errmsg'	=> '无权限'
	]);
}

$user = WEAPP_User::sync_user($user_info);
if(is_wp_error($user)){
	wpjam_send_json($user);
}

weapp_set_current_openid($user['openid']);

wpjam_send_json([
	'errcode'	=> 0,
	'user'		=> WEAPP_User::parse_for_json($user)
]);