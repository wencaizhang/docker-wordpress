<?php
$longitude	= wpjam_get_parameter('longitude',	array('required'=>true));
$latitude	= wpjam_get_parameter('latitude',	array('required'=>true));

$baidu_key	= 'EB41da933903c0215494f5b6f6609851';
$baidu_url	= 'http://api.map.baidu.com/geocoder/v2/?coordtype=wgs84ll&output=json&pois=0&location='.$latitude.','.$longitude.'&ak='.$baidu_key;

$response	= wpjam_remote_request($baidu_url);

if(is_wp_error($response)){
	wpjam_send_json($response);
}

// wpjam_send_json($response['result']['addressComponent']);

$city_name	= $response['result']['addressComponent']['city'];