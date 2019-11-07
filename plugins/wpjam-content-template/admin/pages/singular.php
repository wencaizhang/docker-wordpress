<?php
add_filter('wpjam_content_template_setting', function(){
	if(wp_doing_ajax()){
		$referer_origin	= parse_url(wpjam_get_referer());
		$referer_args	= wp_parse_args($referer_origin['query']);
		$post_type		= $referer_args['post_type'] ?? 'post';

	}else{
		$post_type		= $_GET['post_type'] ?? 'post';	
	}

	$fields	= [
		$post_type.'_top'		=> ['title'=>'顶部内容模板',	'type'=>'text',	'data_type'=>'post_type',	'post_type'=>'template',	'class'=>'all-options',	'placeholder'=>'请输入模板ID或关键字搜索...'],
		$post_type.'_bottom'	=> ['title'=>'底部内容模板',	'type'=>'mu-text',	'data_type'=>'post_type',	'post_type'=>'template',	'class'=>'all-options',	'placeholder'=>'请输入模板ID或关键字搜索...']
	];

	return compact('fields');
});