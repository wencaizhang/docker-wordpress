<?php
$weapp = weapp();
if(is_wp_error($weapp)){
	wpjam_send_json($weapp);
}

$weapp_setting	= weapp_get_setting();




