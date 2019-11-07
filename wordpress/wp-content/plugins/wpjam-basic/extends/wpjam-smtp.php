<?php
/*
Plugin Name: SMTP 发信
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 简单配置就能让 WordPress 使用 SMTP 发送邮件。
Version: 1.0
*/

add_action('phpmailer_init',function ($phpmailer) {
	$phpmailer->isSMTP(); 

	// $phpmailer->SMTPDebug	= 1;

	$phpmailer->SMTPAuth	= true;
	$phpmailer->SMTPSecure	= wpjam_basic_get_setting('smtp_ssl');
	$phpmailer->Host		= wpjam_basic_get_setting('smtp_host'); 
	$phpmailer->Port		= wpjam_basic_get_setting('smtp_port');
	$phpmailer->Username	= wpjam_basic_get_setting('smtp_user');
	$phpmailer->Password	= wpjam_basic_get_setting('smtp_pass');

	if($smtp_reply_to_mail	= wpjam_basic_get_setting('smtp_reply_to_mail')){
		$name	= wpjam_basic_get_setting('smtp_mail_from_name') ?: '';
		$phpmailer->AddReplyTo($smtp_reply_to_mail, $name);
	}
});

add_filter('wp_mail_from', function(){
	return wpjam_basic_get_setting('smtp_user');
});

add_filter('wp_mail_from_name', function($name){
	return wpjam_basic_get_setting('smtp_mail_from_name') ?: $name;
});