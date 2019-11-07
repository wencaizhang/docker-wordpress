<?php
global $wpdb;
$response = array();

$weixin_custom_replies 	= WEIXIN_Reply::Query()->where('appid', weixin_get_appid())->get_results('`keyword`, `match`, reply, `status``, `type`');

if($weixin_custom_replies){
	$response['custom_replies'] = $weixin_custom_replies;
}

wpjam_send_json($response);