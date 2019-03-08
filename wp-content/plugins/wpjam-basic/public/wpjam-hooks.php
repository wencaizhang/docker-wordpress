<?php
function wpjam_basic_get_setting($setting_name){
	return wpjam_get_setting('wpjam-basic', $setting_name);
}

add_filter('wpjam_option_use_site_default', function($status, $option_name){
	if(in_array($option_name, ['wpjam-basic', 'wpjam-cdn', 'wpjam-extends'])){
		return true;
	}

	return $status;
}, 10, 2);

if(wpjam_basic_get_setting('x-frame-options')){
	header('X-Frame-Options: '.wpjam_basic_get_setting('x-frame-options'));
}

function wpjam_include_extends($admin=false){
	$wpjam_extends	= get_option('wpjam-extends');
	$wpjam_extends	= ($wpjam_extends)?array_filter($wpjam_extends, function($value){ return $value; }):array();

	if(is_multisite() && $wpjam_sitewide_extends = get_site_option('wpjam-extends')){
		$wpjam_sitewide_extends	= array_filter($wpjam_sitewide_extends, function($value){ return $value; });
		$wpjam_extends	= array_merge($wpjam_extends, $wpjam_sitewide_extends);
	}
	
	if($wpjam_extends){
		unset($wpjam_extends['plugin_page']);

		$wpjam_extend_files	= array_keys($wpjam_extends);
		$wpjam_extend_dir 	= WPJAM_BASIC_PLUGIN_DIR.'extends/';
		
		if($admin) $wpjam_extend_dir	.= 'admin/';

		foreach ($wpjam_extend_files as $wpjam_extend_file) {
			if(is_file($wpjam_extend_dir.$wpjam_extend_file)){
				include($wpjam_extend_dir.$wpjam_extend_file);
			}
		}
	}
}

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

if(wpjam_basic_get_setting('disable_widgets')){
	remove_action('init', 'wp_widgets_init', 1);
}

//让用户自己决定是否书写正确的 WordPress
if(wpjam_basic_get_setting('remove_capital_P_dangit')){
	remove_filter( 'the_content', 'capital_P_dangit', 11 );
	remove_filter( 'the_title', 'capital_P_dangit', 11 );
	remove_filter( 'wp_title', 'capital_P_dangit', 11 );
	remove_filter( 'comment_text', 'capital_P_dangit', 31 );
}

// remove_filter('the_content', 'do_shortcode', 11 );
//让 Shortcode 优先于 wpautop 执行。
// if(wpjam_basic_get_setting('shortcode_first')){
// 	remove_filter('the_content', 'wpautop' );
// 	add_filter('the_content', 'wpautop' , 12);
// 	remove_filter('the_content', 'shortcode_unautop'  );
// 	add_filter('the_content', 'shortcode_unautop', 13  );
// }

//禁用日志修订功能
if(wpjam_basic_get_setting('diable_revision')){
	define('WP_POST_REVISIONS', false);
	remove_action('pre_post_update', 'wp_save_post_revision' );

	// 自动保存设置为10个小时
	define('AUTOSAVE_INTERVAL', 36000 ); 
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
	
	WPJAM_Cache::init();	// 缓存主循环

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

	if(is_admin()) return;

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
	
	$wpjam_locale = get_locale();

	add_filter('language_attributes',function ($language_attributes) use($wpjam_locale){

		if ( function_exists( 'is_rtl' ) && is_rtl() )
			$attributes[] = 'dir="rtl"';

		if($wpjam_locale){
			if (get_option('html_type') == 'text/html')
				$attributes[] = 'lang="'.$wpjam_locale.'"';

			if(get_option('html_type') != 'text/html')
				$attributes[] = 'xml:lang="'.$wpjam_locale.'"';
		}

		return implode(' ', $attributes);
	});

	add_filter('locale', function($locale) { return (is_admin()) ? 'zh_CN' : 'en_US'; });
}

if(wpjam_basic_get_setting('strict_user')){
	add_filter('sanitize_user', function ($username, $raw_username, $strict){
		if(is_admin()){
			// 设置用户名只能大小写字母和 - . _
			$username = preg_replace( '|[^a-z0-9_.\-]|i', '', $username );
			
			//检测待审关键字和黑名单关键字
			if(wpjam_blacklist_check($username))
				$username = '';
		}

		return $username;
	}, 3, 3);
}

// 屏蔽 Emoji
if(wpjam_basic_get_setting('disable_emoji')){  
	remove_action( 'admin_print_scripts',	'print_emoji_detection_script');
	remove_action( 'admin_print_styles',	'print_emoji_styles');

	remove_action( 'wp_head',				'print_emoji_detection_script',	7);
	remove_action( 'wp_print_styles',		'print_emoji_styles');

	remove_action('embed_head',				'print_emoji_detection_script');

	remove_filter( 'the_content_feed',		'wp_staticize_emoji');
	remove_filter( 'comment_text_rss',		'wp_staticize_emoji');
	remove_filter( 'wp_mail',				'wp_staticize_emoji_for_email');

	add_filter( 'tiny_mce_plugins', function ($plugins){ return array_diff( $plugins, array('wpemoji') ); });
	
	add_filter( 'emoji_svg_url', '__return_false' );
}

// 屏蔽 REST API
if(wpjam_basic_get_setting('disable_rest_api')){
	if(wpjam_basic_get_setting('disable_post_embed') && wpjam_basic_get_setting('diable_block_editor')){
		remove_action( 'init',          'rest_api_init' );
		remove_action( 'rest_api_init', 'rest_api_default_filters', 10 );
		remove_action( 'parse_request', 'rest_api_loaded' );

		add_filter('rest_enabled', '__return_false');
		add_filter('rest_jsonp_enabled', '__return_false');

		// 移除头部 wp-json 标签和 HTTP header 中的 link 
		remove_action('wp_head', 'rest_output_link_wp_head', 10 );
		remove_action('template_redirect', 'rest_output_link_header', 11 );

		remove_action( 'xmlrpc_rsd_apis',            'rest_output_rsd' );

		remove_action( 'auth_cookie_malformed',      'rest_cookie_collect_status' );
		remove_action( 'auth_cookie_expired',        'rest_cookie_collect_status' );
		remove_action( 'auth_cookie_bad_username',   'rest_cookie_collect_status' );
		remove_action( 'auth_cookie_bad_hash',       'rest_cookie_collect_status' );
		remove_action( 'auth_cookie_valid',          'rest_cookie_collect_status' );
		remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
	}
}

//禁用 Auto OEmbed
if(wpjam_basic_get_setting('disable_autoembed')){ 
	//remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
	remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
	//remove_action( 'pre_post_update', array( $GLOBALS['wp_embed'], 'delete_oembed_caches' ) );
	//remove_action( 'edit_form_advanced', array( $GLOBALS['wp_embed'], 'maybe_run_ajax_cache' ) );
}

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

if(wpjam_basic_get_setting('disable_auto_update')){  
	add_filter('automatic_updater_disabled', '__return_true');
	remove_action('init', 'wp_schedule_update_checks');
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
	//remove_action( 'template_redirect', 'wp_old_slug_redirect');
	//remove_action( 'template_redirect', 'redirect_canonical');
	//解决日志改变 post type 之后跳转错误的问题，
	add_action( 'template_redirect', function () {
		if(is_404() && get_query_var('name') != '') {
			global $wpdb;

			$post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_old_slug' AND meta_value = %s", get_query_var('name')));

			if(!$post_id){
				if( get_query_var('name') == 'feed'){
					return false;
				}

				$post_types	= get_post_types(['public' => true]);
				unset($post_types['attachment']);
				$post_types	= "'" . implode("','", $post_types) . "'";

				$where = $wpdb->prepare("post_name LIKE %s", $wpdb->esc_like( get_query_var('name') ) . '%');

				$post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE $where AND post_type in ($post_types) AND post_status = 'publish'");
				if (!$post_id){
					return false;
				}
				if (get_query_var('feed')){
					$link =  get_post_comments_feed_link($post_id, get_query_var('feed'));
				}elseif (get_query_var('page')){
					$link =  trailingslashit(get_permalink($post_id)) . user_trailingslashit(get_query_var('page'), 'single_paged');
				}else{
					$link =  get_permalink($post_id);
				}
			}else{
				$link = get_permalink($post_id);
			}

			if ( $link ){
				wp_redirect( $link, 301 ); 
				exit;
			}
		}
	}, 1);
}

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
			$pagename	= $query_vars['pagename'];
			$categories	= get_categories(['hide_empty'=>false]);
			$categories	= wp_list_pluck($categories, 'slug');

			if(in_array($pagename, $categories)){
				$query_vars['category_name']	= $query_vars['pagename'];
				unset($query_vars['pagename']);
			}
		}
		
		return $query_vars;
	});
}

if(wpjam_basic_get_setting('excerpt_optimization')){ 
	remove_filter('get_the_excerpt', 'wp_trim_excerpt');
	add_filter('get_the_excerpt',function($post_excerpt){
		return get_post_excerpt();
	});
}

add_filter('get_avatar_url', function($url, $id_or_email, $args){
	if(is_numeric($id_or_email)){
		$user_id = $id_or_email;
	}elseif(is_object($id_or_email) && ! empty( $id_or_email->ID ) ) {
		$user_id = intval($id_or_email->ID);
	}else{
		$user_id = '';
	}

	if($user_id && ($user_avatar = get_user_meta($user_id, 'avatarurl', true))){
		return $user_avatar;
	}else{
		return str_replace(["www.gravatar.com", "0.gravatar.com", "1.gravatar.com", "2.gravatar.com"], "cn.gravatar.com", $url);
	}
}, 10, 3);

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

remove_action('wp_scheduled_delete',            'wp_scheduled_delete');
remove_action('wp_scheduled_auto_draft_delete', 'wp_delete_auto_drafts');

add_filter('schedule_event', function($event){
	if($event && in_array($event->hook, ['wp_scheduled_delete','wp_scheduled_auto_draft_delete'])){
		return false;
	}
	
	return $event;
});

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

add_action('wpjam_scheduled_auto_draft_delete', function(){
	global $wpdb;
	
	$old_posts = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'auto-draft' AND DATE_SUB( NOW(), INTERVAL 7 DAY ) > post_date" );
	if($old_posts){
		wp_delete_post($old_posts[0], true );
	}

	if(!is_admin()){
		exit;
	}
});

add_action('wpjam_scheduled_delete', function(){
	global $wpdb;

	$delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );

	$posts_to_delete = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_trash_meta_time' AND meta_value < %d LIMIT 0,3", $delete_timestamp), ARRAY_A);

	foreach ( (array) $posts_to_delete as $post ) {
		$post_id = (int) $post['post_id'];
		if ( !$post_id )
			continue;

		$del_post = get_post($post_id);

		if ( !$del_post || 'trash' != $del_post->post_status ) {
			delete_post_meta($post_id, '_wp_trash_meta_status');
			delete_post_meta($post_id, '_wp_trash_meta_time');
		} else {
			wp_delete_post($post_id);
		}
	}

	if(!is_admin()){
		exit;
	}
});

// 定制后台登录页面链接的连接
add_filter('login_headerurl', function (){
	return home_url();
});


// 定制后台登录页面链接的标题
add_filter('login_headertitle', function (){
	return get_bloginfo('name');
});


// 定制后台登录页面 HEAD
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

	if(is_admin())	return;

	echo wpjam_basic_get_setting('head');

}, 1);


add_action('wp_footer', function (){
	if(is_admin())	return;

	echo wpjam_basic_get_setting('footer');

	if(is_singular()){
		echo get_post_meta(get_the_ID(), 'custom_footer', true);
	}

	if(wpjam_basic_get_setting('optimized_by_wpjam')){
		echo '<p id="optimized_by_wpjam_basic">Optimized by <a href="https://blog.wpjam.com/project/wpjam-basic/">WPJAM Basic</a>。</p>';
	}
}, 99);


