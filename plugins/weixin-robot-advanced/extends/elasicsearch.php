<?php
/*
Plugin Name: Elasicsearch
Plugin URI: http://blog.wpjam.com/
Description: 使用Elasicsearch处理和统计微信的消息
Version: 5.0
Author URI: http://blog.wpjam.com/
*/

add_action('weixin_message', function($message, $response){
	$blog_id	= get_current_blog_id();
	$url 		= 'http://172.16.3.120:8810/wxmsg/import';
	$url 		= add_query_arg(compact('blog_id'),$url);
	
	wpjam_remote_request($url, array(
		'method'			=> 'POST',
		'timeout'			=> 1,
		'body'				=> array('message'=>json_encode($message),'response'=>$response),
		'blocking'			=> false,
		'need_json_decode'	=> false,
	));
}, 9, 2);