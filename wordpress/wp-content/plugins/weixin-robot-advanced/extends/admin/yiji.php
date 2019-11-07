<?php
add_filter('weixin_response_types','wpjam_weixin_yiji_response_types');
function wpjam_weixin_yiji_response_types($response_types){
    $response_types['yiji']                    = '黄道吉日查询';
    return $response_types;
}