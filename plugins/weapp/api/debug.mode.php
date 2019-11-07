<?php
$appid		= weapp_get_appid();
$setting	= weapp_get_setting($appid);

wpjam_send_json(array(
	'errcode'	=> 0,
	'appid'		=> $appid,
	'debug'		=> (int)$setting['debug'],
	'version'	=> $weapp->get_version()
)); 