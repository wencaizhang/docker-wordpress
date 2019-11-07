<?php
$openid		= weapp_get_current_openid();
if(is_wp_error($openid)){
	wpjam_send_json($openid);
}

$data	= apply_filters('weapp_user_update_json', [], $openid);

if($data){
	$result	= WEAPP_User::update($openid, $data);
}

wpjam_send_json([
	'errcode'	=> 0,
	'user'		=> WEAPP_User::parse_for_json($openid)
]);