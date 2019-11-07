<?php
/*
Plugin Name: 微信群二维码
Plugin URI: https://blog.wpjam.com/project/weixin-group-qrcode/
Description: 微信群二维码轮询显示工具，每个群加够大概100人之后，换下一个群二维码。
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/
define('WEIXIN_GROUP_QRCODE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WEIXIN_GROUP_QRCODE_PLUGIN_FILE',  __FILE__);

function weixin_group_qrcode_load(){
	include WEIXIN_GROUP_QRCODE_PLUGIN_DIR.'public/posts.php';
	
	if(is_admin()){
		include WEIXIN_GROUP_QRCODE_PLUGIN_DIR.'admin/admin.php';
	}
}

if(did_action('wpjam_loaded')){
	weixin_group_qrcode_load();
}else{
	add_action('wpjam_loaded', 'weixin_group_qrcode_load');
}

