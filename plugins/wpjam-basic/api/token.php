<?php
wpjam_api_validate_quota('token', 1000);

$appid	= wpjam_get_parameter('appid', ['required' => true]);
$secret	= wpjam_get_parameter('secret', ['required' => true]);

$token	= WPJAM_Grant::generate_access_token($appid, $secret);

if(is_wp_error($token)){
	wpjam_send_json($token);
}

wpjam_send_json([
	'errcode'		=> 0,
	'access_token'	=> $token,
	'expires_in'	=> 7200
]);

