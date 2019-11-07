<?php
$response	=  weixin()->get_access_token();
$response['expires_in']	= $response['expires_in'] - time();

wpjam_send_json($response);