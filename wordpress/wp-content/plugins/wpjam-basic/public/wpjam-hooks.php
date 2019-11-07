<?php
function wpjam_basic_get_setting($setting_name){
	return wpjam_get_setting('wpjam-basic', $setting_name);
}

function wpjam_basic_update_setting($setting, $value){
	return wpjam_update_setting('wpjam-basic', $setting, $value);
}

add_filter('wpjam_option_use_site_default', function($status, $option_name){
	if(in_array($option_name, ['wpjam-basic', 'wpjam-cdn', 'wpjam-extends'])){
		return true;
	}

	return $status;
}, 10, 2);

//移除 WP_Head 无关紧要的代码
if(wpjam_basic_get_setting('remove_head_links')){
	remove_action( 'wp_head', 'wp_generator');					//删除 head 中的 WP 版本号
	foreach (['rss2_head', 'commentsrss2_head', 'rss_head', 'rdf_header', 'atom_head', 'comments_atom_head', 'opml_head', 'app_head'] as $action) {
		remove_action( $action, 'the_generator' );
	}

	remove_action( 'wp_head', 'rsd_link' );						//删除 head 中的 RSD LINK
	remove_action( 'wp_head', 'wlwmanifest_link' );				//删除 head 中的 Windows Live Writer 的适配器？ 

	remove_action( 'wp_head', 'feed_links_extra', 3 );		  	//删除 head 中的 Feed 相关的link
	//remove_action( 'wp_head', 'feed_links', 2 );	

	remove_action( 'wp_head', 'index_rel_link' );				//删除 head 中首页，上级，开始，相连的日志链接
	remove_action( 'wp_head', 'parent_post_rel_link', 10); 
	remove_action( 'wp_head', 'start_post_rel_link', 10); 
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10);

	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );	//删除 head 中的 shortlink
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10);	// 删除头部输出 WP RSET API 地址

	remove_action( 'template_redirect',	'wp_shortlink_header', 11);		//禁止短链接 Header 标签。	
	remove_action( 'template_redirect',	'rest_output_link_header', 11);	// 禁止输出 Header Link 标签。
}

//让用户自己决定是否书写正确的 WordPress
if(wpjam_basic_get_setting('remove_capital_P_dangit')){
	remove_filter( 'the_content', 'capital_P_dangit', 11 );
	remove_filter( 'the_title', 'capital_P_dangit', 11 );
	remove_filter( 'wp_title', 'capital_P_dangit', 11 );
	remove_filter( 'comment_text', 'capital_P_dangit', 31 );
}

// 屏蔽字符转码
if(wpjam_basic_get_setting('disable_texturize')){
	add_filter('run_wptexturize', '__return_false');
}

//移除 admin bar
if(wpjam_basic_get_setting('remove_admin_bar')){
	add_filter('show_admin_bar', '__return_false');
}

//禁用 XML-RPC 接口
if(wpjam_basic_get_setting('disable_xml_rpc')){
	if(wpjam_basic_get_setting('diable_block_editor')){
		add_filter( 'xmlrpc_enabled', '__return_false' );
		remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
	}
}

// 屏蔽 Emoji
if(wpjam_basic_get_setting('disable_emoji')){  
	remove_action('admin_print_scripts','print_emoji_detection_script');
	remove_action('admin_print_styles',	'print_emoji_styles');

	remove_action('wp_head',			'print_emoji_detection_script',	7);
	remove_action('wp_print_styles',	'print_emoji_styles');

	remove_action('embed_head',			'print_emoji_detection_script');

	remove_filter('the_content_feed',	'wp_staticize_emoji');
	remove_filter('comment_text_rss',	'wp_staticize_emoji');
	remove_filter('wp_mail',			'wp_staticize_emoji_for_email');

	add_filter('emoji_svg_url',		'__return_false');
	add_filter('tiny_mce_plugins',	function($plugins){ 
		return array_diff($plugins, ['wpemoji']); 
	});
}

add_filter('register_taxonomy_args', function($args){
	// 屏蔽 REST API
	if(wpjam_basic_get_setting('disable_rest_api')){
		$args['show_in_rest']	= false;
	}

	return $args;
});

add_filter('register_post_type_args', function($args){
	// 屏蔽 REST API
	if(wpjam_basic_get_setting('disable_rest_api')){
		$args['show_in_rest']	= false;
	}

	if(!empty($args['supports']) && is_array($args['supports'])){
		// 屏蔽 Trackback
		if(wpjam_basic_get_setting('disable_trackbacks')){
			$args['supports']	= array_diff($args['supports'], ['trackbacks']);
		}

		//禁用日志修订功能
		if(wpjam_basic_get_setting('diable_revision')){
			$args['supports']	= array_diff($args['supports'], ['revisions']);
		}
	}

	return $args;
});

//禁用日志修订功能
if(wpjam_basic_get_setting('diable_revision')){
	define('WP_POST_REVISIONS', false);
	remove_action('pre_post_update', 'wp_save_post_revision' );

	// 自动保存设置为10个小时
	define('AUTOSAVE_INTERVAL', 36000 ); 
}

// 屏蔽Trackbacks
if(wpjam_basic_get_setting('disable_trackbacks')){
	if(wpjam_basic_get_setting('disable_xml_rpc')){
		//彻底关闭 pingback
		add_filter('xmlrpc_methods',function($methods){
			$methods['pingback.ping'] = '__return_false';
			$methods['pingback.extensions.getPingbacks'] = '__return_false';
			return $methods;
		});
	}

	//禁用 pingbacks, enclosures, trackbacks 
	remove_action( 'do_pings', 'do_all_pings', 10 );

	//去掉 _encloseme 和 do_ping 操作。
	remove_action( 'publish_post','_publish_post_hook',5 );
}

// 屏蔽 REST API
if(wpjam_basic_get_setting('disable_rest_api')){
	if(wpjam_basic_get_setting('disable_post_embed') && wpjam_basic_get_setting('diable_block_editor')){
		remove_action('init',			'rest_api_init' );
		remove_action('rest_api_init',	'rest_api_default_filters', 10 );
		remove_action('parse_request',	'rest_api_loaded' );

		add_filter('rest_enabled',		'__return_false');
		add_filter('rest_jsonp_enabled','__return_false');

		// 移除头部 wp-json 标签和 HTTP header 中的 link 
		remove_action('wp_head',			'rest_output_link_wp_head', 10 );
		remove_action('template_redirect',	'rest_output_link_header', 11);

		remove_action('xmlrpc_rsd_apis',	'rest_output_rsd');

		remove_action('auth_cookie_malformed',		'rest_cookie_collect_status');
		remove_action('auth_cookie_expired',		'rest_cookie_collect_status');
		remove_action('auth_cookie_bad_username',	'rest_cookie_collect_status');
		remove_action('auth_cookie_bad_hash',		'rest_cookie_collect_status');
		remove_action('auth_cookie_valid',			'rest_cookie_collect_status');
		remove_filter('rest_authentication_errors',	'rest_cookie_check_errors', 100 );
	}
}

//禁用 Auto OEmbed
if(wpjam_basic_get_setting('disable_autoembed')){ 
	remove_filter('the_content',			[$GLOBALS['wp_embed'], 'run_shortcode'], 8);
	remove_filter('widget_text_content',	[$GLOBALS['wp_embed'], 'run_shortcode'], 8);

	remove_filter('the_content',			[$GLOBALS['wp_embed'], 'autoembed'], 8);
	remove_filter('widget_text_content',	[$GLOBALS['wp_embed'], 'autoembed'], 8);

	remove_action('edit_form_advanced',		[$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
	remove_action('edit_page_form',			[$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
}

// 屏蔽文章Embed
if(wpjam_basic_get_setting('disable_post_embed')){  
	
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );
	remove_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4 );

	add_filter( 'embed_oembed_discover', '__return_false' );

	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
	remove_filter( 'oembed_response_data',   'get_oembed_response_data_rich',  10, 4 );

	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	add_filter( 'tiny_mce_plugins', function ($plugins){
		return array_diff( $plugins, array( 'wpembed' ) );
	});

	add_filter('query_vars', function ($public_query_vars) {
		return array_diff($public_query_vars, array('embed'));
	});
}

// 屏蔽站点Feed
if(wpjam_basic_get_setting('disable_feed') ) {
	function wpjam_feed_disabled() {
		wp_die('Feed已经关闭, 请访问网站<a href="'.get_bloginfo('url').'">首页</a>！');
	}

	add_action('do_feed',		'wpjam_feed_disabled', 1);
	add_action('do_feed_rdf',	'wpjam_feed_disabled', 1);
	add_action('do_feed_rss',	'wpjam_feed_disabled', 1);
	add_action('do_feed_rss2',	'wpjam_feed_disabled', 1);
	add_action('do_feed_atom',	'wpjam_feed_disabled', 1);
}

// 屏蔽自动更新
if(wpjam_basic_get_setting('disable_auto_update')){  
	add_filter('automatic_updater_disabled', '__return_true');
	remove_action('init', 'wp_schedule_update_checks');
}

// 禁止使用 admin 用户名尝试登录
if(wpjam_basic_get_setting('no_admin')){
	add_filter( 'wp_authenticate',  function ($user){
		if($user == 'admin') exit;
	});

	add_filter('sanitize_user', function ($username, $raw_username, $strict){
		if($raw_username == 'admin' || $username == 'admin'){
			exit;
		}
		return $username;
	}, 10, 3);
}

add_action('init',function(){
	//删除中文包中的一些无用代码
	remove_action( 'admin_init', 'zh_cn_l10n_legacy_option_cleanup' );
	remove_action( 'admin_init', 'zh_cn_l10n_settings_init' );
	wp_embed_unregister_handler('tudou');
	wp_embed_unregister_handler('youku');
	wp_embed_unregister_handler('56com');

	global $wp_rewrite;
	
	add_rewrite_rule($wp_rewrite->root.'api/([^/]+)/(.*?)\.json?$', 'index.php?module=json&action=mag.$matches[1].$matches[2]', 'top');
	add_rewrite_rule($wp_rewrite->root.'api/([^/]+)\.json?$', 'index.php?module=json&action=$matches[1]', 'top');
	// add_rewrite_tag('%json%', '([^/]+)', "module=json&action=");
	// add_permastruct('json', 'api/%json%.json', ['with_front'=>false, 'paged'=>false, 'feed'=>false]);

	if(!is_admin()){
		//阻止非法访问
		//if(strlen($_SERVER['REQUEST_URI']) > 255 ||
		if(
			strpos($_SERVER['REQUEST_URI'], "eval(") ||
			strpos($_SERVER['REQUEST_URI'], "base64") ||
			strpos($_SERVER['REQUEST_URI'], "/**/")
		) {
			@header("HTTP/1.1 414 Request-URI Too Long");
			@header("Status: 414 Request-URI Too Long");
			@header("Connection: Close");
			@exit;
		}
	}
});

add_filter('rewrite_rules_array', function($rules){
	return array_merge(apply_filters('wpjam_rewrite_rules', []), $rules);
});

add_filter('cron_schedules', function($schedules){
	$schedules['five_minutes']		= ['interval' => 300, 'display' => '每5分钟一次'];
	$schedules['fifteen_minutes']	= ['interval' => 900, 'display' => '每15分钟一次'];
	return $schedules;
});

//前台不加载语言包
if(wpjam_basic_get_setting('locale')){
	add_filter('locale', function($locale){ 
		if(is_admin()){
			return $locale;
		}
		
		global $wpjam_locale;

		if(!isset($wpjam_locale)){
			$wpjam_locale	= $locale;	
		}

		if(in_array('get_language_attributes', wp_list_pluck(debug_backtrace(), 'function'))){
			return $wpjam_locale;
		}else{
			return 'en_US';
		}
	});
}

if(wpjam_basic_get_setting('search_optimization')){  
	//当搜索结果只有一篇时直接重定向到日志
	add_action('template_redirect', function () {
		if (is_search() && get_query_var('module') == '') {
			global $wp_query;
			$paged	= get_query_var('paged');
			if ($wp_query->post_count == 1 && empty($paged)) {
				wp_redirect( get_permalink( $wp_query->posts['0']->ID ) );
			}
		}
	});
}

if(wpjam_basic_get_setting('404_optimization')){ 
	// 解决日志改变 post type 之后跳转错误的问题，
	// WP 原始解决函数 'wp_old_slug_redirect' 和 'redirect_canonical'

	add_filter('old_slug_redirect_post_id', function($post_id){
		if(!$post_id){
			return wpjam_find_post_id_by_old_slug(get_query_var('name'));
		}

		return $post_id;
	});


	// add_action('template_redirect', function(){
	// 	if(is_404() && get_query_var('name') != '') {
	// 		global $wpdb;

	// 		$post_id = wpjam_guess_post_id_by_post_name(get_query_var('name'));
			
	// 		if(!$post_id){
	// 			return false;
	// 		}

	// 		if (get_query_var('feed')){
	// 			$link =  get_post_comments_feed_link($post_id, get_query_var('feed'));
	// 		}elseif (get_query_var('page')){
	// 			$link =  trailingslashit(get_permalink($post_id)) . user_trailingslashit(get_query_var('page'), 'single_paged');
	// 		}else{
	// 			$link =  get_permalink($post_id);
	// 		}

	// 		if ( $link ){
	// 			wp_redirect( $link, 301 ); 
	// 			exit;
	// 		}
	// 	}
	// }, 1);
}

// 去掉URL中category
if(wpjam_basic_get_setting('no_category_base')){
	add_filter('pre_term_link', function($term_link, $term){
		global $wp_rewrite;

		if($wp_rewrite->use_verbose_page_rules){
			return $term_link;
		}
		
		if($term->taxonomy == 'category'){
			return '%category%';
		}

		return $term_link;
	}, 10, 2);

	add_filter('request', function($query_vars) {
		global $wp_rewrite;

		if($wp_rewrite->use_verbose_page_rules){
			return $query_vars;
		}

		if(!isset($query_vars['module']) && !isset($_GET['page_id']) && !isset($_GET['pagename']) && !empty($query_vars['pagename'])){
			$pagename	= strtolower($query_vars['pagename']);
			$pagename	= wp_basename($pagename);

			$categories	= get_categories(['hide_empty'=>false]);
			$categories	= wp_list_pluck($categories, 'slug');

			if(in_array($pagename, $categories)){
				$query_vars['category_name']	= $pagename;
				unset($query_vars['pagename']);
			}
		}
		
		return $query_vars;
	});
}

// 优化文章摘要
$excerpt_optimization = wpjam_basic_get_setting('excerpt_optimization');
if($excerpt_optimization){ 
	remove_filter('get_the_excerpt', 'wp_trim_excerpt');

	if($excerpt_optimization != 2){
		add_filter('get_the_excerpt',function($text='', $post=null){
			$excerpt_length	= wpjam_basic_get_setting('excerpt_length') ?: 200;		
			return WPJAM_Post::get_excerpt($post, $excerpt_length);
		});
	}
}

// Gravatar加速
add_filter('pre_get_avatar_data', function($args, $id_or_email){
	$email_hash	= '';
	$user		= $email = false;
	
	if(is_object($id_or_email) && isset($id_or_email->comment_ID)){
		$id_or_email	= get_comment($id_or_email);
	}

	if(is_numeric($id_or_email)){
		$user	= get_user_by('id', absint($id_or_email));
	}elseif($id_or_email instanceof WP_User){	// User Object
		$user	= $id_or_email;
	}elseif($id_or_email instanceof WP_Post){	// Post Object
		$user	= get_user_by('id', intval($id_or_email->post_author));
	}elseif($id_or_email instanceof WP_Comment){	// Comment Object
		if(!empty($id_or_email->user_id)){
			$user	= get_user_by('id', intval($id_or_email->user_id));
		}elseif(!empty($id_or_email->comment_author_email)){
			$email	= $id_or_email->comment_author_email;
		}
	}elseif(is_string($id_or_email)){
		if(strpos($id_or_email, '@md5.gravatar.com')){
			list($email_hash)	= explode('@', $id_or_email);
		} else {
			$email	= $id_or_email;
		}
	}

	if($user){
		$user_avatar = get_user_meta($user->ID, 'avatarurl', true);

		if($user_avatar){
			$user_avatar	= str_replace('http://thirdwx.qlogo.cn', 'https://thirdwx.qlogo.cn', $user_avatar);
			$user_avatar	= wpjam_get_thumbnail($user_avatar, [$args['width'],$args['height']]);

			$args['url']			= $user_avatar;		
			$args['found_avatar']	= true;

			return $args;
		}else{
			$args	= apply_filters('wpjam_default_avatar_data', $args, $user->ID);
			if($args['found_avatar']){
				return $args;
			}else{
				$email = $user->user_email;
			}
		}
	}
	
	if(!$email_hash){
		if($email){
			$email_hash = md5(strtolower(trim($email)));
		}
	}

	if($email_hash){
		$args['found_avatar']	= true;
	}

	if(wpjam_basic_get_setting('gravatar') == 'v2ex'){
		$url	= 'http://cdn.v2ex.com/gravatar/'.$email_hash;
	}else{
		$url	= 'http://cn.gravatar.com/avatar/'.$email_hash;
	}

	$url_args	= array_filter([
		's'	=> $args['size'],
		'd'	=> $args['default'],
		'f'	=> $args['force_default'] ? 'y' : false,
		'r'	=> $args['rating'],
	]);

	$url			= add_query_arg(rawurlencode_deep($url_args), set_url_scheme($url, $args['scheme']));
	$args['url']	= apply_filters('get_avatar_url', $url, $id_or_email, $args);

	return $args;

}, 10, 2);

add_action('wpjam_remove_invalid_crons', function(){
	foreach (_get_cron_array() as $timestamp => $wp_cron) {
		foreach ($wp_cron as $hook => $dings) {
			if(!has_filter($hook)){			// 系统不存在的定时作业，清理掉
				foreach( $dings as $key=>$data ) {
					wp_unschedule_event($timestamp, $hook, $data['args']);
				}
			}
		}
	}
});

// 屏蔽后台隐私
if(wpjam_basic_get_setting('disable_privacy')){
	remove_action( 'user_request_action_confirmed', '_wp_privacy_account_request_confirmed' );
	remove_action( 'user_request_action_confirmed', '_wp_privacy_send_request_confirmation_notification', 12 ); // After request marked as completed.
	remove_action( 'wp_privacy_personal_data_exporters', 'wp_register_comment_personal_data_exporter' );
	remove_action( 'wp_privacy_personal_data_exporters', 'wp_register_media_personal_data_exporter' );
	remove_action( 'wp_privacy_personal_data_exporters', 'wp_register_user_personal_data_exporter', 1 );
	remove_action( 'wp_privacy_personal_data_erasers', 'wp_register_comment_personal_data_eraser' );
	remove_action( 'init', 'wp_schedule_delete_old_privacy_export_files' );
	remove_action( 'wp_privacy_delete_old_export_files', 'wp_privacy_delete_old_export_files' );

	add_filter('schedule_event', function($event){
		if($event && in_array($event->hook, ['wp_privacy_delete_old_export_files'])){
			return false;
		}
		
		return $event;
	});
}

// 定制登录页面链接的连接
add_filter('login_headerurl', function (){
	return home_url();
});


// 定制登录页面链接的标题
add_filter('login_headertext', function (){
	return get_bloginfo('name');
});


// 定制登录页面 HEAD
add_action('login_head', function (){
	echo wpjam_basic_get_setting('login_head'); 
});


add_action('login_footer', function(){ 
	echo wpjam_basic_get_setting('login_footer');
});


add_filter('login_redirect', function ( $redirect_to, $request, $user ) {
	if($request) return $request;

	return wpjam_basic_get_setting('login_redirect')?:$redirect_to;
}, 10, 3);

add_action('wp_head', function (){
	if(!is_admin()){
		echo wpjam_basic_get_setting('head');
	}
}, 1);

add_action('wp_footer', function (){
	if(!is_admin()){
		echo wpjam_basic_get_setting('footer');

		if(wpjam_basic_get_setting('optimized_by_wpjam')){
			echo '<p id="optimized_by_wpjam_basic">Optimized by <a href="https://blog.wpjam.com/project/wpjam-basic/">WPJAM Basic</a>。</p>';
		}
	}	
}, 99);