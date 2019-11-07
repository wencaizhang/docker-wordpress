<?php 

$form_id	= wpjam_get_parameter('form_id',	['method'=>'POST', 'required'=> true]);

wpjam_send_json(['errcode'=>0, 'form_id'=>$form_id]);