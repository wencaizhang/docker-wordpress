<?php
register_activation_hook(WEIXIN_GROUP_QRCODE_PLUGIN_FILE, function(){
	flush_rewrite_rules();
});

add_action('wpjam_post_page_file', function($post_type){
	if($post_type == 'qrcode'){
		include WEIXIN_GROUP_QRCODE_PLUGIN_DIR.'admin/qrcode-options.php';
	}
});

add_action('wpjam_post_list_page_file', function($post_type){
	if($post_type == 'qrcode'){
		include WEIXIN_GROUP_QRCODE_PLUGIN_DIR.'admin/qrcode-list.php';
	}
});