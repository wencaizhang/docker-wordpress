<?php

add_filter('weixin_response_types','wpjam_weixin_stock_response_types');
function wpjam_weixin_stock_response_types($response_types){
    $response_types['stock']            = '股票行情查询';
    return $response_types;
}

add_filter('weixin_setting','wpjam_weixin_stock_fields',11);
function wpjam_weixin_stock_fields($sections){
    $stock_fields = array(
        'stock_default_code'    => array('title'=>'默认股票代码', 'type'=>'text', 'description'=>'用户发送“股票”时默认查询的股票代码。上证指数请填写999999；企业如上市，可填写本公司股票代码'),
    );
    $sections['stock'] = array('title'=>'股票行情', 'callback'=>'', 'fields'=>$stock_fields);

    return $sections;
}