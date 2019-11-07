<?php
add_filter('query_vars', function ($query_vars) {
	$query_vars[]	= 'module';
	$query_vars[]	= 'action';

	// 如果 $custom_taxonomy_key.'_id' 不用在 rewrite ，下面代码无效
	// if($custom_taxonomies = get_taxonomies(array('public' => true, '_builtin' => false))){
	// 	foreach ($custom_taxonomies as $custom_taxonomy_key => $custom_taxonomy) {
	// 		$query_vars[]	= $custom_taxonomy_key.'_id';
	// 	}
	// }

	return $query_vars;
});

add_filter('request', function ($query_vars){
	$module = $query_vars['module'] ?? '';
	$action = $query_vars['action'] ?? '';

	if($module == 'json' && strpos($action, 'mag.') === 0){
		return $query_vars;
	}

	if(!empty($_REQUEST['tag_id'])){
		$query_vars['tag_id'] = $_REQUEST['tag_id'];
	}

	if($custom_taxonomies = get_taxonomies(['_builtin' => false])){
		$tax_query	= [];

		foreach ($custom_taxonomies as $custom_taxonomy) {

			$current_term_id = $query_vars[$custom_taxonomy.'_id'] ?? ($_REQUEST[$custom_taxonomy.'_id'] ?? '');

			if($current_term_id && $current_term_id != -1){
				if($term = get_term($current_term_id, $custom_taxonomy)){	// wp 本身的 cache 有问题， WP_Term::get_instance
					$tax_query[$custom_taxonomy]	= array(
						'taxonomy'	=> $custom_taxonomy,
						'terms'		=> array( $current_term_id ),
						'field'		=> 'id',
					);
				}else{
					wp_die('非法'.$custom_taxonomy.'_id');
				}
			}
		}

		if($tax_query){
			$query_vars['tax_query']				= array_values($tax_query);
			$query_vars['tax_query']['relation']	= 'AND';
		}
	}
	
	return $query_vars;
});

//设置 headers
add_action('send_headers', function ($wp){
	if(wpjam_basic_get_setting('x-frame-options')){
		header('X-Frame-Options: '.wpjam_basic_get_setting('x-frame-options'));
	}

	$module = $wp->query_vars['module'] ?? '';
	$action = $wp->query_vars['action'] ?? '';

	if($module == 'json'){
		wpjam_send_origin_headers();

		if(strpos($action, 'mag.') === 0){
			global $wp, $wpjam_json;
			
			$wpjam_json	= str_replace(['mag.','/'], ['','.'], $action);

			do_action('wpjam_api_template_redirect', $wpjam_json);

			$api_setting	= wpjam_get_api_setting($wpjam_json);

			if(!$api_setting){
				wpjam_send_json([
					'errcode'	=> 'api_not_defined',
					'errmsg'	=> '接口未定义！',
				]);
			}

			$response		= ['errcode'=>0];

			$current_user	= apply_filters('wpjam_current_user', null);

			if(is_wp_error($current_user)){
				if(!empty($api_setting['auth'])){
					wpjam_send_json($current_user);
				}else{
					$current_user	= null;
				}
			}

			$response['current_user']	= $current_user;
			$response['page_title']		= $api_setting['page_title'] ?? '';
			$response['share_title']	= $api_setting['share_title'] ?? '';
			$response['share_image']	= !empty($api_setting['share_image']) ? wpjam_get_thumbnail($api_setting['share_image'], '500x400') : '';

			foreach ($api_setting['modules'] as $module){
				if(!$module['type'] || !$module['args']){
					continue;
				}
				
				if(is_array($module['args'])){
					$args	= $module['args'];
				}else{
					$args	= wpjam_parse_shortcode_attr(stripslashes_deep($module['args']), 'module');
				}

				$type		= $module['type'];
				$action		= $args['action'] ?? '';

				$output		= $args['output'] ?? '';
				$template	= '';

				if($type == 'post_type'){
					if(empty($action)){
						wpjam_send_json([
							'errcode'	=> 'empty_action',
							'errmsg'	=> '没有设置 action',
						]);
					}

					global $wpjam_pre_query_vars;	// 两个 post 模块的时候干扰。。。。

					if(empty($wpjam_pre_query_vars)){
						$wpjam_pre_query_vars	= $wp->query_vars; 
					}else{
						$wp->query_vars	= $wpjam_pre_query_vars;
					}

					$post_type	= $args['post_type'] ?? ($_GET['post_type'] ?? null);

					if($action == 'list'){
						$template	= WPJAM_BASIC_PLUGIN_DIR.'api/post.list.php';
					}elseif($action == 'get'){
						$template	= WPJAM_BASIC_PLUGIN_DIR.'api/post.get.php';
					}elseif(in_array($action, ['comment', 'like', 'fav', 'unlike', 'unfav'])){
						$template	= WPJAM_BASIC_PLUGIN_DIR.'api/post.action.php';
					}elseif(in_array($action, ['comment.list', 'like.list', 'fav.list'])){
						$template	= WPJAM_BASIC_PLUGIN_DIR.'api/post.action.list.php';
					}

					$template	= apply_filters('wpjam_api_post_template', $template, $action);
				}elseif($type == 'taxonomy'){
					$template	= WPJAM_BASIC_PLUGIN_DIR.'api/term.list.php';
				}elseif($type == 'setting'){
					$template	= WPJAM_BASIC_PLUGIN_DIR.'api/setting.php';
				}elseif($type == 'other'){
					$template	= WPJAM_BASIC_PLUGIN_DIR.'api/other.php';
				}

				$template	= apply_filters('wpjam_api_template_include', $template, $type, $action);

				if($template && is_file($template)){
					$wp->set_query_var('template_type',	$type);

					include $template;
				}
			}

			$response = apply_filters('wpjam_json', $response, $api_setting, $wpjam_json);

			wpjam_send_json($response);
		}else{
			if(!isset($_GET['debug'])){ 
				if(isset($_GET['callback']) || isset($_GET['_jsonp'])){
					$content_type	= 'application/javascript';	
				}else{
					$content_type	= 'application/json';
				}

				@header('Content-Type: ' .  $content_type.'; charset=' . get_option('blog_charset'));
			}
		}
	}

	if($module){
		remove_action('template_redirect', 'redirect_canonical');

		do_action('wpjam_module', $module, $action);
	}
});

add_filter('template_include', function ($template){
	$module	= get_query_var('module');
	$action	= get_query_var('action');

	if($module){
		$action = ($action == 'new' || $action == 'add')?'edit':$action;

		if($action){
			$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/'.$action.'.php';
		}else{
			$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/index.php';
		}

		$wpjam_template		= apply_filters('wpjam_template', $wpjam_template, $module, $action);

		if(is_file($wpjam_template)){
			return $wpjam_template;
		}else{
			wp_die('路由错误！');
		}
	}

	return $template;
});

add_action('wpjam_api_template_redirect', function($json){
	remove_filter('the_excerpt', 'convert_chars');
	remove_filter('the_excerpt', 'wpautop');
	remove_filter('the_excerpt', 'shortcode_unautop');

	remove_filter('the_title', 'convert_chars');

	// add_filter('the_password_form',	function($output){
	// 	if(get_queried_object_id() == get_the_ID()){
	// 		return '';
	// 	}else{
	// 		return $output;
	// 	}
	// });
});

function is_module($module='', $action=''){
	$current_module	= get_query_var('module');
	$current_action	= get_query_var('action');

	// 没设置 module
	if(!$current_module){
		return false;
	}
	
	// 不用确定当前是什么 module
	if(!$module){
		return true;
	}
	
	if($module != $current_module){
		return false;
	}

	if(!$action){
		return true;
	}

	if($action != $current_action){
		return false;
	}
	
	return true;
}

function wpjam_send_origin_headers(){
	header('X-Content-Type-Options: nosniff');

	$origin = get_http_origin();

	if ( $origin ) {
		// Requests from file:// and data: URLs send "Origin: null"
		if ( 'null' !== $origin ) {
			$origin = esc_url_raw( $origin );
		}

		@header( 'Access-Control-Allow-Origin: ' . $origin );
		@header( 'Access-Control-Allow-Methods: GET, POST' );
		@header( 'Access-Control-Allow-Credentials: true' );
		@header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
		@header( 'Vary: Origin' );

		if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
			exit;
		}
	}
	
	if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
		status_header( 403 );
		exit;
	}
}

function wpjam_post_list_get_parameter($key, $use_get=true){
	global $post_list_args, $is_main_query;

	$value = $post_list_args[$key] ?? null;

	if($use_get && $is_main_query){
		
		if($get = wpjam_get_parameter($key)){
			$value = $get;
		}	
	}

	return $value;
}

function wpjam_get_json(){
	global $wpjam_json;

	return $wpjam_json ?? '';
}

function is_wpjam_json($json=''){
	global $wpjam_json;

	if(!empty($wpjam_json)){
		if($json){
			return ($wpjam_json == $json);
		}else{
			return $wpjam_json;
		}
	}else{
		return false;
	}
}