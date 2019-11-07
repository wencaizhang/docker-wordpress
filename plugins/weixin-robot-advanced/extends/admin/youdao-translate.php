<?php
add_filter('weixin_response_types', function ($response_types){
	$response_types['translate'] = '有道翻译';
	return $response_types;
});


add_filter('weixin_setting',function ($sections){
	$youdao_translate_fields = [
		'youdao_translate_api_key'	=> ['title'=>'有道翻译API Key',	'type'=>'text',	 'description'=>'点击<a href="http://fanyi.youdao.com/openapi?path=data-mode">这里</a>申请有道翻译API！'],
		'youdao_translate_key_from'	=> ['title'=>'有道翻译KEY FROM',	'type'=>'text',	 'description'=>'申请有道翻译API的时候同时填写并获得KEY FROM']
	];
	$sections['youdao_translate'] = array('title'=>'有道翻译', 'callback'=>'', 'fields'=>$youdao_translate_fields);
	return $sections;
},11);
