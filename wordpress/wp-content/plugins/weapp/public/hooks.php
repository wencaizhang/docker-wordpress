<?php
add_filter('wpjam_current_user', function($current_user){
	if(is_weapp() || isset($_GET['appid'])){
		$openid	= weapp_get_current_openid();

		if(is_wp_error($openid)){
			return $openid;
		}else{
			return WEAPP_User::parse_for_json($openid);
		}
	}

	return $current_user;
});

add_action('wpjam_api_template_redirect', function($json){
	if(is_weapp() || isset($_GET['appid'])){
		$openid		= weapp_get_current_openid();
		
		if($openid && !is_wp_error($openid)){
			if($form_id = wpjam_get_parameter('form_id',array('method'=>'POST'))){
				weapp_add_form_id($openid, $form_id);
			}
		}
	}

	$weapp_apis	= [
		'auth.signon',
		'user.login',
		'user.info',
		'user.update',
		'form_id',
		'form_ids',

		'message.reply',
		'upload.media',
		'weapp.access_token',
		'login.wxacode',
		'wxacode.code',
		'wxacode.bind'
	];

	if(in_array($json, $weapp_apis)){
		include WEAPP_PLUGIN_DIR . 'api/function.php';
		include WEAPP_PLUGIN_DIR . 'api/'.$json.'.php';
		exit;
	}elseif(strpos($json, 'weapp.') === 0 && strpos($json, 'weapp.qrcode.') === false){
		$json_file	= WEAPP_PLUGIN_DIR . 'api/'.str_replace('weapp.', '', $json).'.php';
		if(file_exists($json_file)){
			include WEAPP_PLUGIN_DIR . 'api/function.php';
			include $json_file;
			exit;
		}
	}
}, 1);


// if(!is_admin()){
	
// 	remove_action('plugins_loaded', 'wp_maybe_load_embeds',  0);
// 	remove_action('plugins_loaded', '_wp_customize_include' );

// 	remove_action('sanitize_comment_cookies',   'sanitize_comment_cookies');

// 	remove_action('init', 'smilies_init', 5);
// 	remove_action('init', 'kses_init');
// 	remove_action('init', 'ms_subdomain_constants');
// 	remove_action('init', 'check_theme_switched',99);
// 	remove_action('init', 'maybe_add_existing_user_to_blog');
// 	remove_action('init', 'wpjam_redirect_to_mapped_domain');
// 	remove_action('wp_loaded', '_custom_header_background_just_in_time' );

// 	remove_filter('request', '_post_format_request');
// }

	