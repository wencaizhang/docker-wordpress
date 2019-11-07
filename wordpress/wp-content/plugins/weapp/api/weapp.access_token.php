<?php
if($_SERVER['RAW_HTTP_HOST'] != gethostbyname(gethostname()) ){
	wpjam_send_json(['errcode'=>'invalid', 'errmsg'=>'非法请求']);	// 请使用内网IP访问，HTTP_HOST 可以伪造，存在风险。
}

$appid			= weapp_get_appid();
$weapp			= weapp();

$access_token	= $weapp->get_access_token();

wpjam_send_json([
	'errcode'		=>0,
	'access_token'	=> $access_token
]);


