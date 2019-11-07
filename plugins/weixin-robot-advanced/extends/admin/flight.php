<?php
add_filter('weixin_response_types','wpjam_weixin_flight_response_types');
function wpjam_weixin_flight_response_types($response_types){
    $response_types['flight-not-entity']         = '航班名称为空';
    $response_types['flight-fail']               = '航班查询出错';
    $response_types['flight']                    = '航班查询回复';
    return $response_types;
}