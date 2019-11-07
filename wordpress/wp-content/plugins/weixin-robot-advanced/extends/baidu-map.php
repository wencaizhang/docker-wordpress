<?php
/*
Plugin Name: 百度地图
Plugin URI: http://wpjam.net/item/wpjam-weixin-baidu-map/
Description: 发送你当前地理位置给公众号账号，即可查询附近信息。
Version: 4.0
Author URI: http://blog.wpjam.com/
*/

add_filter('weixin_default_option','weixin_robot_baidu_map_default_option');
function weixin_robot_baidu_map_default_option($defaults_option){
	$baidu_map_default_options = array(
		'baidu_map_app_key'			=> '',
		'baidu_map_default_keyword'	=> '',
		'baidu_map_default_reply'	=> "请回复附近XX来查询附近的商家\n1、查询附近的饭店，则发送【附近饭店】\n2、查询附近的某家店名，如711，发送【附近711】\n3、查询当地的天气，发送【天气】\n4、查询某地天气，发送天气xx，比如【天气广州】",
		'baidu_map_no_location'		=> '还未获取你的地理位置或者地理位置过期，请发过来吧。请点击“+号键”，选择“位置图标”，发送你的地理位置过来！',
	);
	return array_merge($defaults_option, $baidu_map_default_options);
}

add_filter('weixin_builtin_reply', 'weixin_robot_baidu_map_builtin_reply');
function weixin_robot_baidu_map_builtin_reply($weixin_builtin_replies){
	$weixin_builtin_replies['[location]']	= array('type'=>'full',		'reply'=>'获取地理位置',	'function'=>'weixin_robot_baidu_map_location_reply');
	$weixin_builtin_replies['附近']			= array('type'=>'prefix',	'reply'=>'附近信息搜索',	'function'=>'weixin_robot_baidu_map_nearby_reply');
	$weixin_builtin_replies['天气']			= array('type'=>'prefix',	'reply'=>'获取天气数据',	'function'=>'weixin_robot_baidu_map_weather_reply');
	
	return $weixin_builtin_replies;
}

function weixin_robot_baidu_map_location_reply(){
	global $weixin_reply;

	$weixin_setting	= weixin_get_setting();

	if($weixin_reply->get_context_reply() == 'weixin_robot_baidu_map_weather_reply'){
		$message	= $weixin_reply->get_message();

		$keyword = $message['Location_Y'].','.$message['Location_X'];

		$results = weixin_robot_get_baidu_map_weather($keyword);
		if($results){
			$weixin_reply->textReply($results);
			
		}else{
			$weixin_reply->textReply('暂无该地区的天气数据');   
		}

		$weixin_reply->set_response('location-weather'); 
		return true;
	}else{
		if($baidu_map_default_keyword = $weixin_setting['baidu_map_default_keyword']){
			weixin_robot_baidu_map_nearby_reply($baidu_map_default_keyword);
		}else{
			$weixin_reply->textReply($weixin_setting['baidu_map_default_reply']);
			$weixin_reply->set_response('location');
		}
	}
}

function weixin_robot_baidu_map_nearby_reply($keyword){
	global $weixin_reply;

	$weixin_setting	= weixin_get_setting();

	$keyword = trim(str_replace('附近', '', $keyword));
	$keyword = $keyword? $keyword : $weixin_setting['baidu_map_default_keyword'];

	if($keyword){
		$openid		= $weixin_reply->get_openid();
		$location	= WEIXIN_Message::get_user_location($openid);

		if($location){
			$location	= $location['Location_X'].','.$location['Location_Y'];
			$reply		= weixin_robot_get_baidu_map_places($keyword,$location);
			$reply		= ($reply)?$reply: '附近没有【'.$keyword.'】';
		}else{
			$reply		= $weixin_setting['baidu_map_no_location'];
		}
	}else{
		$reply = '附近后面要加上搜索的关键词，比如【附近饭店】';
	}

	$weixin_reply->textReply($reply);
	$weixin_reply->set_response('location-query');
}

function weixin_robot_baidu_map_weather_reply($keyword){
	global $weixin_reply;

	if($keyword == '天气'){
		$weixin_reply->set_context_reply('weixin_robot_baidu_map_weather_reply');
		$weixin_reply->textReply("请输入城市名称获取城市天气预报\n\n或者点击“+号键”，选择“位置图标”，发送地理位置来获取天气预报\n\n退出请输入： Q");
		$weixin_reply->set_response('location-weather'); 
		return true;
	}

	if($keyword == 'q'){
		$weixin_reply->delete_context_reply();
		$weixin_reply->textReply("你已经退出了天气查询，下次要进行查询，请再次输入：天气或者点击菜单");  
		$weixin_reply->set_response('location-weather'); 
		return true;
	}

	$keyword = trim(str_replace('天气', '', $keyword));

	if($keyword){
		$results = weixin_robot_get_baidu_map_weather($keyword);
		if($results){
			$weixin_reply->textReply($results);
			
		}else{
			$weixin_reply->textReply('暂无该地区的天气数据');   
		}
	}

	$weixin_reply->set_response('location-weather'); 
}

function weixin_robot_baidu_map_remote_request($url, $method='get', $body=''){

	if($method == 'get'){
		$args = array('headers' => array('Accept-Encoding'=>''), 'sslverify'=>false);
		$response = wp_remote_get($url, $args);
	}elseif($method == 'post'){
		$args = array('headers' => array('Accept-Encoding'=>''), 'sslverify'=>false, 'body'=>$body);
		$response = wp_remote_post($url, $args);
	}

	if(is_wp_error($response)){
		return false;
	}

	$response = json_decode($response['body']);

	if(!empty($response->error)){
		return false;
	}

	return $response;

}

function weixin_robot_get_baidu_map_places($keyword, $location, $type = 'location'){
	$weixin_setting	= weixin_get_setting();

	if($type == 'location'){
		$url = "http://api.map.baidu.com/place/v2/search?&page_size=6&query=".urlencode($keyword)."&location=".$location."&radius=3000&output=json&scope=2&ak=".$weixin_setting['baidu_map_app_key'];
	}elseif($type == 'region'){
		$url = "http://api.map.baidu.com/place/v2/search?&page_size=6&query=".urlencode($keyword)."&region=".urlencode($location)."&output=json&scope=2&ak=".$weixin_setting['baidu_map_app_key'];
	}

	$response = weixin_robot_baidu_map_remote_request($url);

	if(!$response){
		return false;
	}

	if(count($response->results) <1){
		return false;
	}

	$data = "";
	foreach ($response->results as $result) {
		//$data .= "店名：<a href='".$result['detail_info']['detail_url']."' >".$result['name']."</a>\r\n地址：".$result['address']."\r\n电话：".$result['telephone']."\r\n距离：".$result['detail_info']['distance']."米\r\n\r\n";
		$data	.= "店名：".$result->name."\n";
		$data	.= "地址：".$result->address."\n";
		if(isset($result->telephone)){
			$data	.= "电话：".$result->telephone."\n";
		}
		$data	.= "距离：".$result->detail_info->distance."米\n\n";
	}

	return $data;  
}

function weixin_robot_get_baidu_map_weather($location){
	$weixin_setting	= weixin_get_setting(); 
	$url = "http://api.map.baidu.com/telematics/v3/weather?location=".urlencode($location)."&output=json&scope=2&ak=".$weixin_setting['baidu_map_app_key'];

	$response = weixin_robot_baidu_map_remote_request($url);

	if(!$response){
		return false;
	}

	if(count($response->results) <1){
		return false;
	}

	$data = '';

	$result			= $response->results[0];
	$data 			.= $result->currentCity.'，';
	$data 			.= 'PM25：'.$result->pm25."\n\n";

	$indexs			= $result->index;
	$weather_datas	= $result->weather_data;

	$i = 0;
	foreach ($weather_datas as $weather_data) {
		$data .= $weather_data->date."\n";
		$data .= $weather_data->weather." ";
		$data .= $weather_data->wind." ";
		$data .= $weather_data->temperature."\n\n";
		$i++;

		if($i>1){
			break;
		}
	}

	foreach ($indexs as $index) {
		$data .= $index->title.'：';
		$data .= $index->zs."\n";
		$data .= $index->des."\n\n";
	}

	return $data;
}


function weixin_robot_get_baidu_map_city($location){
	$weixin_setting	= weixin_get_setting();
	$url = "http://api.map.baidu.com/geocoder?location=".$location."&output=json&key=".$weixin_setting['baidu_map_app_key'];
	$response = weixin_robot_baidu_map_remote_request($url);

	if(!$response){
		return false;
	}
	
	return $response->result->addressComponent->city;
}



