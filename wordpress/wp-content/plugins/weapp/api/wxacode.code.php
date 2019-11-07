<?php
$openid		= weapp_get_current_openid();
if(is_wp_error($openid)){
	wpjam_send_json($openid);
}

$access_token	= wpjam_get_parameter('access_token');

$appid	= weapp_get_appid();
$scene	= wpjam_get_parameter('scene',	['method'=>'POST',	'required'=>true]);

$weapp_wxacode	= wp_cache_get($scene, 'weapp_scene_wxacode_'.$appid);
if($weapp_wxacode === false){
	wpjam_send_json(['errcode'=>'empty_qrcode', 'errmsg'=>'请首先获取二维码，再来验证！']);
}

$weapp_wxacode['access_token']	= $access_token;
wp_cache_set( $scene, $weapp_wxacode, 'weapp_scene_wxacode_'.$appid, 1200);

wpjam_send_json($weapp_wxacode);