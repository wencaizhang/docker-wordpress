<?php

add_filter('weixin_response_types','wpjam_weixin_horoscope_response_types');
function wpjam_weixin_horoscope_response_types($response_types){
    $response_types['horoscope'] = '星座运势查询';
    return $response_types;
}