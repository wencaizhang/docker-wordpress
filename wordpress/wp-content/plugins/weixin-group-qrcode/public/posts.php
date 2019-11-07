<?php
wpjam_register_post_type('qrcode',[
	'label'			=> '群二维码',
	'menu_position'	=> 6,
	'menu_icon'		=> 'dashicons-format-chat',
	'supports'		=> ['title','excerpt'],
	'has_archive'	=> true,
	'public'		=> true,
	'permastruct'	=> '/qrcode/%post_id%'
]);


add_action('template_include', function($template){
	if(is_singular('qrcode')){
		if(file_exists(STYLESHEETPATH.'/single-qrcode.php')){
			return STYLESHEETPATH.'/single-qrcode.php';
		}else{
			return WEIXIN_GROUP_QRCODE_PLUGIN_DIR.'template/single-qrcode.php';
		}
	}
	return $template;
});