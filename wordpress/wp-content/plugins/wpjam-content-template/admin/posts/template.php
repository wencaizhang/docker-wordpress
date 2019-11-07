<?php
include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR .'admin/posts/template-type.php';

global $pagenow;

if($pagenow == 'post-new.php'){
	// if(empty($_GET['template_type'])){
	// 	wp_redirect(admin_url('edit.php?post_type=template&page=wpjam-new-template'));
	// 	exit;
	// }
}else{
	$post_id	= $_GET['post'] ?? 0;

	if($post_id){
		$action	= $_GET['action'] ?? '';

		if($action == 'edit'){
			if($template_type = get_post_meta($post_id, '_template_type', true)){
				wp_redirect(admin_url('edit.php?post_type=template&page=wpjam-'.$template_type.'&post_id='.$post_id));
				exit;
			}
		}	
	}
}

add_filter('wpjam_template_post_options', function($post_options){
	global $post;

	$post_options['shortcode_meta_box']	=	[
		'title'		=> '短代码',
		'context'	=> 'side',
		'fields'	=> [
			'shortcode'	=> ['title'=>'',	'type'=>'view',	'value'=>'[template id="'.$post->ID.'"]'],
		]
	];

	return $post_options;
});

add_filter('wpjam_html_replace', function($html){
	return preg_replace('/<a href=".*?" class="page-title-action">.*?<\/a>/i', '<a href="javascript:;" class="page-title-action wpjam-new-template">新建模板</a>', $html);
});