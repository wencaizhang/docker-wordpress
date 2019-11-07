<?php

add_filter('weixin_response_types','wpjam_weixin_renpin_response_types');
function wpjam_weixin_renpin_response_types($response_types){
    $response_types['renpin']       	= '人品查询';
    return $response_types;
}