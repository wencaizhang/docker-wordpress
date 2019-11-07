<?php
$card_id	= isset($_GET['card_id'])?$_GET['card_id']:'';
$outer_id	= isset($_GET['outer_id'])?$_GET['outer_id']:0;
$code		= isset($_GET['code'])?$_GET['code']:'';
$openid		= isset($_GET['openid'])?$_GET['openid']:'';


$card_ext	= weixin_robot_generate_card_ext(compact('card_id','outer_id','code','openid'));

wpjam_send_json($card_ext);