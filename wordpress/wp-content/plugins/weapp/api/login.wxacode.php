<?php

$key	= wpjam_get_parameter('key',	['method'=>'GET',	'required'=>true]);
$appid	= weapp_get_appid();

$weapp_wxacode	= wp_cache_get($key, 'weapp_bind_wxacode_'.$appid);

if($weapp_wxacode === false){

	$weapp_page		= apply_filter('weapp_login_wxacode_page');

	$scene			= wp_generate_password(10,false,false).microtime(true)*10000;
	$wxacode_url	= weapp_create_qrcode(['page' => $weapp_page, 'scene' => $scene, 'type' => 'unlimit', 'width' => $width]);

	if(is_wp_error($qrcode_url)){
		wpjam_send_json($qrcode_url);
	}

	$weapp_wxacode	= [
		'code'			=> rand(1000,9999),
		'key'			=> $key,
		'scene'			=> $scene,
		'wxacode_url'	=> $wxacode_url
	];

	wp_cache_set( $key, $weapp_wxacode, 'weapp_bind_wxacode_'.$appid, 1200 );
	wp_cache_set( $scene, $weapp_wxacode, 'weapp_scene_wxacode_'.$appid, 1200 );
}

unset($weapp_wxacode['code']);

$weapp_wxacode['errcode']	= 0;

wpjam_send_json($weapp_wxacode);
