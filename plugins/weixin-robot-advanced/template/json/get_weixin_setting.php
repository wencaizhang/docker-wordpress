<?php

global $wpdb;
$response = array();

// if(defined('WEIXIN_EXPORT') && WEIXIN_EXPORT){
	$response['setting']	= get_option('weixin-robot');
// }

wpjam_send_json($response);