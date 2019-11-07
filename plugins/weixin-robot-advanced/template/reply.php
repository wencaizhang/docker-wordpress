<?php

// trigger_error('12312');
/*下面代码优化微信自定义回复的效率*/

// remove_action('plugins_loaded', 'wp_maybe_load_widgets', 0);
// remove_action('plugins_loaded', 'wp_maybe_load_embeds',  0);
// remove_action('plugins_loaded', '_wp_customize_include' );

// remove_action('sanitize_comment_cookies',   'sanitize_comment_cookies');

// remove_action('init', 'smilies_init', 5);	
// remove_action('init', 'wp_widgets_init', 1);
// remove_action('init', 'kses_init');
// remove_action('init', 'ms_subdomain_constants');
// remove_action('init', 'check_theme_switched',99);
// remove_action('init', 'maybe_add_existing_user_to_blog');
// remove_action('init', 'wpjam_feed_post_views_init', 4);

// remove_action('set_current_user', 'kses_init');

// remove_action('wp_loaded', '_custom_header_background_just_in_time' );
// remove_filter('wpjam_html_replace', 'wpjam_qiniu_html_replace');

if(!defined('ABSPATH')){
	include('../../../../wp-load.php');
}

if(!trait_exists('WEIXIN_Trait')){
	trigger_error('WEIXIN_Trait');
	echo ' ';
	exit;
}

include WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-reply.php';		// 微信被动回复类库
include WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-message.php';	// 微信消息处理类库

global $wechatObj, $weixin_reply;

define('DOING_WEIXIN_REPLY', true);

if(!defined('WEIXIN_SEARCH')) {
	$weixin_setting	= weixin_get_setting();
	define('WEIXIN_SEARCH', $weixin_setting['weixin_search'] ?? false);
}

// 如果是在被动响应微信消息，和微信用户界面中，设置 is_home 为 false，
add_action('parse_query',function($query){	
	$query->is_home 	= false;
	$query->is_search 	= false;
	$query->is_weixin 	= true;
});

$weixin_appid	= weixin_get_appid();

$weixin_seting	= weixin_get_setting($weixin_appid);
$weixin_token	= $weixin_seting['weixin_token'];
$encodingAESKey	= $weixin_seting['weixin_encodingAESKey'];

if (strlen($encodingAESKey) != 43) {
	echo ' ';
	trigger_error('encodingAesKey 非法');
	exit;
}

$weixin_reply	= new WEIXIN_Reply($weixin_appid, $weixin_token, $encodingAESKey);
$wechatObj		= $weixin_reply; // 兼容

if(!isset($_GET['debug'])){
	$timestamp	= WPJAM_API::get_parameter('timestamp');
	$nonce		= WPJAM_API::get_parameter('nonce');
	$signature	= WPJAM_API::get_parameter('signature');

	if($weixin_reply->verify_msg($timestamp, $nonce, $signature)){
		if(isset($_GET["echostr"])){
			echo $_GET["echostr"];
			exit;	
		}
	}else{
		exit;
	}
}

$msg_signature	= WPJAM_API::get_parameter('msg_signature');
$msg_input		= file_get_contents('php://input');

$weixin_openid	= WPJAM_API::get_parameter('openid');

$result			= $weixin_reply->response_msg($msg_input, $msg_signature, $weixin_openid);

if(is_wp_error($result)){
	trigger_error($result->get_error_message());
	echo ' ';
}

exit;


