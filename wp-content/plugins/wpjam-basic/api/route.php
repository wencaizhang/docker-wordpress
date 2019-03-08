<?php
global $wpjam_json, $wp;

$api_setting	= wpjam_get_api_setting($wpjam_json);

if(!$api_setting){
	wpjam_send_json([
		'errcode'	=> 'api_not_defined',
		'errmsg'	=> '接口未定义！',
	]);
}

$response		= ['errcode'=>0, 'current_user'=>null];
$current_user	= apply_filters('wpjam_current_user', null);

if(!is_wp_error($current_user)){
	$response['current_user']	= $current_user;
}elseif($api_setting['auth']){
	wpjam_send_json($current_user);
}

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

		$action	= str_replace(
			['unreply', 'unapply', 'like.delete', 'fav.delete'], 
			['reply.delete', 'apply.delete', 'unlike', 'unfav'], 
			$action
		);

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
		}else{
			$template	= apply_filters('wpjam_api_post_action_template', $template, $action);
		}
	}elseif($type == 'taxonomy'){
		$template	= WPJAM_BASIC_PLUGIN_DIR.'api/taxonomy.list.php';
	}elseif($type == 'setting'){
		$template	= WPJAM_BASIC_PLUGIN_DIR.'api/setting.php';
	}elseif($type == 'other'){
		$template	= WPJAM_BASIC_PLUGIN_DIR.'api/other.php';
	}

	$template	= apply_filters('wpjam_api_template_include', $template, $type, $action);

	if($template && is_file($template)){
		$wp->set_query_var('template_type',	$type);

		include($template);
	}
}

$response	= apply_filters('wpjam_json', $response, $api_setting, $wpjam_json);

wpjam_send_json($response);

