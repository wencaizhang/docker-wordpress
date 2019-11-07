<?php

$code	= wpjam_get_parameter('code',	['method'=>'POST',	'required'=>true]);
$scene	= wpjam_get_parameter('scene',	['method'=>'POST',	'required'=>true]);
$appid	= weapp_get_appid();

$weapp_wxacode	= wp_cache_get($scene, 'weapp_scene_wxacode_'.$appid);

if($weapp_wxacode === false){
	wpjam_send_json(['errcode'=>'empty_qrcode', 'errmsg'=>'请首先获取二维码，再来验证！']);
}

$access_token	= $weapp_wxacode['access_token'];
if(empty($access_token)){
	wpjam_send_json(['errcode'=>'empty_access_token', 'errmsg'=>'该二维码还未绑定']);
}

$openid	= WEAPP_User::get_openid_by_access_token($access_token);

if($code == $weapp_wxacode['code']){
	wpjam_send_json([
		'errcode'		=> 0,
		'access_token'	=> $access_token,
		'expired_in'	=> WEAPP_User::get_expired_time_by_access_token($access_token) - time() - 600,
		'user'			=> WEAPP_User::parse_for_json($openid),
	]);
}else{
	wpjam_send_json(['errcode'=>'invalid_code', 'errmsg'=>'非法Code']);
}