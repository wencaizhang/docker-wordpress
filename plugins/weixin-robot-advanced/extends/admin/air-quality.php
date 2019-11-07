<?php

add_filter('weixin_setting','wpjam_weixin_air_quality_fields',11);
function wpjam_weixin_air_quality_fields($sections){
    
    $air_quality_fields = array(
        'air_quality_app_key'       => array('title'=>'APPKey',     'type'=>'text',  'description'=>'点击<a href="http://pm25.in/api_doc">这里</a>申请空气质量数据接口。申请理由参考：为微信公众账号【XX在线】用户提供XX市空气质量查询服务,感谢贵网站提供数据接口！'),
        'air_quality_default_city'  => array('title'=>'默认城市',    'type'=>'text',  'description'=>'用户发送“空气”时，默认查询该城市空气质量数据。点击<a href="http://pm25.in/">查看</a>支持查询的城市。务必确定该城市已经开通查询')
    );
    $sections['air_quality'] = array('title'=>'空气质量', 'callback'=>'', 'fields'=>$air_quality_fields);
    
    return $sections;
}

add_filter('weixin_response_types','wpjam_weixin_air_quality_response_types');
function wpjam_weixin_air_quality_response_types($response_types){
    $response_types['air_quality']  = '空气质量查询';
    return $response_types;
}