<?php
include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR .'admin/posts/template-type.php';

add_filter('wpjam_card_tabs', function(){
	return [
		'setting'	=> ['title'=>'卡片设置',	'function'=>'wpjam_card_setting_page'],
		'content'	=> ['title'=>'卡片内容',	'function'=>'wpjam_card_content_page'],
	];
});

function wpjam_card_setting_page(){
	$card_types	= [
		1=>'小图模式：图片显示在左侧，尺寸为200x200。',
		2=>'大图模式：图片全屏显示，高度自适应。'
	];

	$fields = [
		'post_title'	=> ['title'=>'名称',	'type'=>'text'],
		'card_type'		=> ['title'=>'样式',	'type'=>'radio',	'options'=>$card_types,	'sep'=>'<br /><br />'],
		'post_id'		=> ['title'=>'',	'type'=>'hidden'],
	];

	$post_id		= wpjam_get_data_parameter('post_id') ?: 0;

	if($post_id){
		$post			= get_post($post_id);
		$post_title		= $post->post_title;
		$post_content	= $post->post_content;
		$content		= maybe_unserialize($post_content);
		$card_type		= $content['card_type'] ?? 1;
	}else{
		$post_title		= '';
		$card_type		= 1;
	}

	$data			= compact('post_title', 'card_type', 'post_id');
	$submit_text	= $post_id ? '编辑' : '新建';

	echo $post_id ? '<h2>卡片设置</h2>' : '<h1 class="wp-heading-inline">新建卡片</h1>';

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'data'			=> $data, 
		'submit_text'	=> $submit_text,
		'action'		=> 'save',
	]);	
}

function wpjam_card_content_page(){
	$post_id	= wpjam_get_data_parameter('post_id');

	$post			= get_post($post_id);
	$post_excerpt	= $post->post_excerpt;
	// $post_password	= $post->post_password;
	$post_content	= $post->post_content;
	$content		= maybe_unserialize($post_content);	
	$card_type		= $content['card_type'] ?? 1;
	$thumbnail		= $content['thumbnail'] ?? '';
	$price			= $content['price'] ?? '';
	$link			= $content['link'] ?? '';
	$weapp			= $content['weapp'] ?? [];

	$data		= compact('thumbnail', 'post_excerpt', 'price', 'link', 'weapp', 'post_id');
	$fields		= [
		'thumbnail'		=> ['title'=>'图片',	'type'=>'img',	'item_type'=>'url',	'size'=>'200x200'],
		'post_excerpt'	=> ['title'=>'简介',	'type'=>'text',	'placeholder'=>'一句话简介...'],
		'price'			=> ['title'=>'价格',	'type'=>'text',	'class'=>'',	'description'=>'输入价格会显示「去选购」按钮'],
		// 'post_password'	=> ['title'=>'密码',		'type'=>'text',		'value'=>$post_password,'class'=>'',	'description'=>'设置了密码保护，则前端必须输入密码才可查看'],
		'link'			=> ['title'=>'链接',	'type'=>'url'],
		'post_id'		=> ['title'=>'',	'type'=>'hidden'],
	];

	if($card_type == 2){
		$fields['thumbnail']['size']	= '1200x0';
		unset($fields['post_excerpt']);
		unset($fields['price']);
	}

	if(defined('WEAPP_PLUGIN_DIR')){
		if($weapps	= wpjam_get_setting('wpjam-content-template', 'weapps')){
			$weapps	= wp_list_pluck($weapps, 'name', 'appid');
		}else{
			$weapps	= [];
		}

		$weapps		= ['webview'=>'跳转网页','weapp'=>'本小程序'] + $weapps;

		$fields['weapp']	= ['title'=>'小程序',	'type'=>'fieldset',	'fieldset_type'=>'array',	'fields'=>[
			'appid'	=> ['title'=>'',	'type'=>'select',	'options'=>$weapps],
			'path'	=> ['title'=>'',	'type'=>'text',		'placeholder'=>'请输入小程序路径，不填则跳转首页'],
		]];
	}

	echo '<h2>卡片内容</h2>';	

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'data'			=> $data,
		'action'		=> 'set',
		'submit_text'	=> '保存'
	]);
}

function wpjam_card_ajax_response(){
	global $plugin_page; 

	$action	= $_POST['page_action'];

	check_ajax_referer($plugin_page.'-'.$action);

	$post_id	= wpjam_get_data_parameter('post_id');
	$data		= wp_parse_args($_POST['data']);

	if($action == 'save'){
		$post_title		= $data['post_title'] ?? '';
		$post_status	= 'publish';
 
		$card_type		= $data['card_type'] ?? 1;
		$meta_input		= ['_template_type'=>'card'];

		if($post_id){
			$post_content	= get_post($post_id)->post_content;
			$content		= maybe_unserialize($post_content);
			$content		= array_merge($content, compact('card_type'));
			$post_content	= maybe_serialize($content);

			$post_id		= WPJAM_Post::update($post_id, compact('post_title', 'post_content', 'post_status', 'meta_input'));
			$is_add			= false;
		}else{
			$post_type		= 'template';
			$post_content	= maybe_serialize(compact('card_type'));
			$post_id		= WPJAM_Post::insert(compact('post_type', 'post_title', 'post_content', 'post_status', 'meta_input'));
			$is_add			= true;
		}

		if(is_wp_error($post_id)){
			wpjam_send_json($post_id);
		}else{
			wpjam_send_json(compact('post_id', 'is_add'));
		}
	}elseif($action == 'set'){
		// $post_password	= $data['post_password'] ?? '';

		$thumbnail	= $data['thumbnail'] ?? '';
		$price		= $data['price'] ?? '';
		$link		= $data['link'] ?? 0;
		$weapp		= $data['weapp'] ?? [];

		$post_content	= get_post($post_id)->post_content;
		$content		= maybe_unserialize($post_content);
		$content		= array_merge($content, compact('price', 'thumbnail', 'link', 'weapp'));
		$post_content	= maybe_serialize($content);
		$post_excerpt	= $data['post_excerpt'] ?? '';
		
		$post_id		= WPJAM_Post::update($post_id, compact('post_excerpt', 'post_content'));
		
		if(is_wp_error($post_id)){
			wpjam_send_json($post_id);
		}else{
			wpjam_send_json(compact('post_id'));
		}
	}
}