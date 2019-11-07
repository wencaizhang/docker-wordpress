<?php
WPJAM_API::method_allow('POST');

$iv				= wpjam_get_parameter('iv',				['method'=>'POST',	'required'=>true]);
$encrypted_data	= wpjam_get_parameter('encrypted_data',	['method'=>'POST',	'required'=>true]);

$session_key	= weapp_get_session_key();

if(is_wp_error($session_key)){
	wpjam_send_json($session_key);
}

$phone_info  = weapp()->decrypt_phone($session_key, $iv, $encrypted_data);

if (is_wp_error($phone_info)) {
	wpjam_send_json($phone_info);
}

$phone = $phone_info['phoneNumber'];

wpjam_send_json([
	'errcode'	=> 0,
	'phone'		=> $phone
]);