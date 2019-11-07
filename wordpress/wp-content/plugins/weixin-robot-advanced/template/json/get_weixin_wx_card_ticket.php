<?php
$response	=  weixin()->get_wx_card_ticket();

$response['expires_in']	= $response['expires_in']-time();

wpjam_send_json($response);