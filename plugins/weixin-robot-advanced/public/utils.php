<?php
wp_cache_add_global_groups([
	'weixin_messages'
]);

function weixin_get_setting($appid=''){
	return get_option('weixin-robot');
}

function weixin_has_feature($feature, $appid=''){
	$weixin_setting	= weixin_get_setting($appid);
	return boolval($weixin_setting[$feature] ?? false);
}

add_filter('option_weixin-robot', function($value){
	if(!isset($value['weixin_reply'])){
		$value['weixin_reply']	= 1;
	}
	return $value;
});

function weixin_get_appid(){
	$setting = weixin_get_setting();
	return trim($setting['weixin_app_id']);
}

function weixin_get_type($appid=''){
	$setting = weixin_get_setting($appid);
	return $setting['weixin_type']??'';
}

function weixin($appid='', $secret=''){
	$appid	= ($appid)?:weixin_get_appid();

	if(empty($appid)) {
		trigger_error('empty_appid');
		wp_die('公众号 appid 为空',	'empty_appid');
	}

	$weixin_setting	= weixin_get_setting($appid);

	if(empty($weixin_setting)){
		wp_die('公众号设置信息为空',	'请先在后台小程序设置中加入该小程序');
	}

	$secret	= ($secret)?:$weixin_setting['secret']??$weixin_setting['weixin_app_secret'];

	static $weixins;

	$weixins	= ($weixins)??array();

	if(isset($weixins[$appid])) return $weixins[$appid];

	$weixins[$appid]	= new WEIXIN($appid, $secret);

	return $weixins[$appid];
}

function weixin_exists($appid, $secret){
	$weixin			= new WEIXIN($appid, $secret);
	$access_token	= $weixin->get_access_token($force=true);
	return (is_wp_error($access_token))?false:true;
}

function weixin_get_extends($appid=''){
	if(is_multisite() && is_network_admin()){
		return get_site_option('weixin_extends');
	}else{
		$appid	= $appid ?: weixin_get_appid();
		$weixin_extends	= get_option('weixin_'.$appid.'_extends');

		$weixin_extends	= $weixin_extends ? array_filter($weixin_extends, function($value){ return $value; }):[];

		if(is_multisite() && $weixin_sitewide_extends = get_site_option('weixin_extends')){
			$weixin_sitewide_extends	= array_filter($weixin_sitewide_extends, function($value){ return $value; });
			$weixin_extends	= array_merge($weixin_extends, $weixin_sitewide_extends);
		}

		return $weixin_extends;
	}
}

function weixin_parse_mp_article($mp_url){
	$mp_html	= wpjam_remote_request($mp_url, ['need_json_decode'=>false]);

	if(is_wp_error($mp_html)){
		return $mp_html;
	}

	$title = $digest = $author = $content = $content_source_url = $thumb_url = '';
	$show_cover_pic = 0;

	if(preg_match_all('/var msg_title = \"(.*?)\";/i', $mp_html, $matches)){
		$title	= str_replace(['&nbsp;','&amp;'], [' ','&'], $matches[1][0]);
	}

	if(preg_match_all('/var msg_desc = \"(.*?)\";/i', $mp_html, $matches)){
		$digest	= str_replace(['&nbsp;','&amp;'], [' ','&'], $matches[1][0]);
	}

	if(preg_match_all('/<em class=\"rich_media_meta rich_media_meta_text\">(.*?)<\/em>/i', $mp_html, $matches)){
		$author	= str_replace(['&nbsp;','&amp;'], [' ','&'], $matches[1][0]);
	}

	if(preg_match_all('/<div class=\"rich_media_content \" id=\"js_content\">[\s\S]{106}([\s\S]*?)[\s\S]{22}<\/div>/i', $mp_html, $matches)){
		$content	= $matches[1][0];
	}

	if(preg_match_all('/var msg_source_url = \'(.*?)\';/i', $mp_html, $matches)){
		$content_source_url	= $matches[1][0];
	}
	
	if(preg_match_all('/var msg_cdn_url = \"(.*?)\";/i', $mp_html, $matches)){
		$thumb_url	= str_replace('/640', '/0', $matches[1][0]);
	}

	return compact('title','thumb_url','author','digest','show_cover_pic','content','content_source_url');	
}

function weixin_include_extends($admin=false){
	$weixin_extends	= weixin_get_extends();
	if(!$weixin_extends) return;

	$weixin_extend_dir 	= $admin? WEIXIN_ROBOT_PLUGIN_DIR.'extends/admin': WEIXIN_ROBOT_PLUGIN_DIR.'extends';
	foreach ($weixin_extends as $weixin_extend => $value) {
		if(!$value) continue;

		$weixin_extend_file	= $weixin_extend_dir.'/'.$weixin_extend;
		if(is_file($weixin_extend_file)){
			include($weixin_extend_file);
		}
	}
}

add_filter('init', function (){
	add_rewrite_tag('%weixin%', '([^/]+)', "module=weixin&action=");
	add_permastruct('weixin', 'weixin/%weixin%', ['with_front'=>false, 'paged'=>false, 'feed'=>false]);
});


add_filter('wpjam_template', function($template, $module, $action){
	if($module == 'weixin'){
		if(is_weixin()){
			if(weixin_get_type() == 4){
				WEIXIN_User::oauth_request();
			}

			$openid	= WEIXIN_User::get_current_openid();

			if(is_wp_error($openid)){
				wp_die('未登录');
			}else{
				$weixin_user	= WEIXIN_User::get($openid);

				if(!$weixin_user || !$weixin_user['subscribe']){
					$weixin_user	= WEIXIN_User::get($openid, array('force'=>true));	// 由于缓存的问题，强抓一次
				}

				if($weixin_user && $weixin_user['subscribe']){
					return is_file($template)? $template : apply_filters('weixin_template', WEIXIN_ROBOT_PLUGIN_DIR.'template/user/'.$action.'.php', $action);
				}else{
					wp_die('未关注');
				}
			}
		}else{
			if($action == 'reply'){
				return WEIXIN_ROBOT_PLUGIN_DIR.'template/'.$action.'.php';
			}else{
				wp_die('请在微信中访问');
			}
		}
	}elseif($module == 'json' && $action && strpos($action, 'weixin') !== false ){
		$appid	= weixin_get_appid();
		$today	= date('Y-m-d', current_time('timestamp'));

		if($action != 'get_weixin_js_api_ticket'){

			$token	= $_GET['access_token'] ?? '';

			if(empty($token)){
				wp_die('未授权1');
			}

			$weixin_api_access_tokens	= get_option('weixin_'.weixin_get_appid().'_api_access_tokens');
			if(empty($weixin_api_access_tokens)){
				wpjam_send_json([
					'errcode'	=> 'invalid_access_token',
					'errmsg'	=> '非法 Access Token'
				]);
			}
			if(!isset($weixin_api_access_tokens[$token])){
				wpjam_send_json([
					'errcode'	=> 'invalid_access_token',
					'errmsg'	=> '非法 Access Token'
				]);
			}

			
			if($weixin_api_access_tokens[$token] && $weixin_api_access_tokens[$token] < $today){
				wpjam_send_json([
					'errcode'	=> 'access_token_expired',
					'errmsg'	=> 'Access Token 已经过期'
				]);
			}
		}

		$limits	= array(
			'get_weixin_access_token'	=> 100,
			'get_weixin_js_api_ticket'	=> 1000,
			'get_weixin_user'			=> 100000,
		);
		
		$max_times	= $limits[$action] ?? 100;

		$current_times	= wp_cache_get($action, 'wpjam_api_times');
		$current_times	= $current_times?:0;

		if($current_times > $max_times){
			wpjam_send_json([
				'errcode'	=> 'exceed_quota',
				'errmsg'	=> 'API 调用次数超限'
			]);
		}else{
			wp_cache_set($action.'_'.$today, $current_times+1, 'wpjam_api_times', DAY_IN_SECONDS);
		}

		if(strpos($action, 'stats') !== false){
			return WEIXIN_ROBOT_PLUGIN_DIR.'/template/json/stats/'.$action.'.php';
		}else{
			if(is_weixin() && weixin_get_type() == 4){
				WEIXIN_User::oauth_request();
			}
			return WEIXIN_ROBOT_PLUGIN_DIR.'/template/json/'.$action.'.php';
		}
	}
	return $template;
}, 10, 3);

add_action('plugins_loaded',function(){
	if(is_weixin()){

		include(WEIXIN_ROBOT_PLUGIN_DIR.'public/jssdk.php');		// 微信页面JSSDK

		if(wp_doing_ajax()) {
			return;
		}

		if(weixin_get_type() == 4){
			// $weixin_setting	= weixin_get_setting(); 
			// if(weixin_has_feature('weixin_oauth20')){
			// 	WEIXIN_User::oauth_request();	// 发起 OAuth 请求
			// }
			WEIXIN_User::redirect();			// 微信活动跳转，用于支持第三方活动
		}else{
			$access_token	= $_GET['weixin_access_token']??'';
			WEIXIN_User::set_access_token_cookie($access_token);	// 订阅号就保存用户 access_token 到 cookie 里
		}
	}
});

