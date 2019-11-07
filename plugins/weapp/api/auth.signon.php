<?php
// 放弃ing

WPJAM_API::method_allow('POST');

$code			= wpjam_get_parameter('code',			['method'=>'POST',	'required'=>true]);
$iv				= wpjam_get_parameter('iv',				['method'=>'POST',	'required'=>true]);
$encrypted_data	= wpjam_get_parameter('encrypted_data',	['method'=>'POST',	'required'=>true]);

$session_key	= weapp_get_session_key($code);
if(is_wp_error($session_key)){
	wpjam_send_json($session_key);
}

$user_info		= $weapp->decrypt_user($session_key, $iv, $encrypted_data);
if(is_wp_error($user_info)){
	wpjam_send_json($user_info);
}

$user = WEAPP_User::sync_user($user_info);
if(is_wp_error($user)){
	wpjam_send_json($user);
}

weapp_set_current_openid($user['openid']);

$user_json	= array(
	'access_token'	=> WEAPP_User::generate_access_token($user['openid']),
	'expired_in'	=> DAY_IN_SECONDS - 600,
	'user'			=> WEAPP_User::parse_for_json($user),
);

$send = ($send)??1;
if($send){
	$user_json['errcode']	= 0;
	wpjam_send_json($user_json);
}