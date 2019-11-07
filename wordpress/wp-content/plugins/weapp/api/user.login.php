<?php

WPJAM_API::method_allow('POST');

$code			= wpjam_get_parameter('code',	['method'=>'POST',	'required'=>true]);

$weapp			= weapp();
$session_data	= $weapp->jscode2session($code);

if(is_null($session_data)){
	wpjam_send_json([
		'errcode'	=> 'invalid_session_data',
		'errmsg'	=> '非法 Session Data'
	]);
}

if(is_wp_error($session_data)){
	wpjam_send_json($session_data);
}

$openid			= $session_data['openid'];


$iv				= wpjam_get_parameter('iv',				['method'=>'POST']);
$encrypted_data	= wpjam_get_parameter('encrypted_data',	['method'=>'POST']);

if($iv && $encrypted_data){
	$session_key	= $session_data['session_key'];

	$user_info		= $weapp->decrypt_user($session_key, $iv, $encrypted_data);
	if(is_wp_error($user_info)){
		wpjam_send_json($user_info);
	}

	$user = WEAPP_User::sync_user($user_info);
	if(is_wp_error($user)){
		wpjam_send_json($user);
	}
}

$unionid	= $session_data['unionid']??($session_data['unionId'] ?? '');

$user	= WEAPP_User::get($openid);
if(!$user){
	$result		= WEAPP_User::insert([
		'openid'	=> $openid,
		'unionid'	=> $unionid,
		'time'		=> time(),
		'modified'	=> time()
	]);

	if(is_wp_error($result)){
		wpjam_send_json($result);
	}
}elseif($unionid && $unionid != $user['unionid']){
	WEAPP_User::update($openid, [
		'unionid'	=> $unionid,
		'modified'	=> time()
	]);
}

$user	= WEAPP_User::parse_for_json($openid);

$weapp_setting	= weapp_get_setting();

// if(!empty($weapp_setting['authorization']) && $user['expired']){
// 	wpjam_send_json([
// 		'errcode'	=> 'user_info_expired',
// 		'errmsg'	=> '用户信息已过期'
// 	]);
// }

weapp_set_current_openid($openid);

wpjam_send_json([
	'errcode'		=> 0,
	'access_token'	=> WEAPP_User::generate_access_token($openid),
	'expired_in'	=> DAY_IN_SECONDS - 600,
	'user'			=> $user,
]);