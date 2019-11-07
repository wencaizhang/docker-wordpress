<?php
if(empty($args['option_name'])){
	wpjam_send_json(['errcode'=>'empty_option_name', 'errmsg'=>'option_name 不能为空']);
}

$option_name	= $args['option_name'] ?? '';
$setting_name	= $args['setting_name'] ?? ($args['setting'] ?? '');
$output			= $args['output'] ?? '';

if($setting_name){
	$output	= $output ?: $setting_name; 
	$value	= apply_filters('wpjam_setting_value', wpjam_get_setting($option_name, $setting_name), $setting_name, $option_name);
}else{
	$output	= $output ?: $option_name;
	$value	= apply_filters('wpjam_option_value', wpjam_get_option($option_name), $option_name);
}

if(is_wp_error($value)){
	wpjam_send_json($value);
}

$response[$output]	= $value;