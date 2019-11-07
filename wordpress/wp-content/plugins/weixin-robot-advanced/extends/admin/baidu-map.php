<?php
add_filter('weixin_response_types','weixin_robot_add_baidu_map_response_types');
function weixin_robot_add_baidu_map_response_types($response_types){
	$response_types['location']						= '获取地理位置';
	$response_types['location-query']				= '附近查询';
	$response_types['location-weather']				= '天气查询';
	
	return $response_types;
}

add_filter('weixin_setting','weixin_robot_add_baidu_map_fields',15);
function weixin_robot_add_baidu_map_fields($sections){

	$sections['baidu_map'] = array(
		'title'		=> '百度地图', 
		'callback'	=> '', 
		'fields'	=>	array(
			'baidu_map_app_key'			=> array('title'=>'百度地图 APP Key',		'type'=>'text',		'description'=>'点击<a href="http://lbsyun.baidu.com/apiconsole/key?application=key">这里</a>申请百度地图 APP KEY！'),
			'baidu_map_default_keyword'	=> array('title'=>'默认搜索关键字',		'type'=>'text',		'description'=>'设置用户发送地理位置之后直接到百度地图搜索的关键字，该选项设置后下面默认回复的选项将失效。'),
			'baidu_map_default_reply'	=> array('title'=>'获取位置信息后回复',		'type'=>'textarea',	'description'=>'获取用户发送位置信息之后，提示用户如何进行搜索的回复！'),
			'baidu_map_no_location'		=> array('title'=>'未获取位置信息时回复',	'type'=>'textarea',	'description'=>'还未获取用户位置信息，但是用户已经发送【附近xxx】时的回复！'),
		)
	);

	return $sections;
}

add_filter('weixin_reply_setting','weixin_robot_baidu_map_remove_location_field');
function weixin_robot_baidu_map_remove_location_field($sections){
	if(isset($_GET['tab']) && $_GET['tab'] == 'default'){
		unset($sections['default_reply']['fields']['weixin_default_location']);
	}
	return $sections;
}

