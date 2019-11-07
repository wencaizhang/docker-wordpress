<?php

$openid		= weapp_get_current_openid();

if(!is_wp_error($openid) && $openid){
	if($form_ids	= wpjam_get_parameter('form_ids',['method'=>'POST'])){
		foreach ($form_ids as $form_id) {
			weapp_add_form_id($openid, $form_id);
		}
	}
}