<?php
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );

if (!isset($_FILES['media'])) {
	wpjam_send_json(array(
		'errcode'	=> 'empty_media',
		'errmsg'	=> '媒体流不能为空！'
	));	
}

$type		= wpjam_get_parameter('type',array('method'=>'REQUEST'));

if($type == 'attachment'){
	$post_id		= wpjam_get_parameter('post_id',array('method'=>'POST', 'type'=>'int', 'default'=>0));
	$attachment_id	= media_handle_upload('media', $post_id);

	if(is_wp_error($attachment_id)){
		wpjam_send_json($attachment_id);
	}

	$media_url		= wp_get_attachment_url($attachment_id);
}else{
	$upload_file	= wp_handle_upload($_FILES['media'], ['test_form' => false]);

	if(isset($upload_file['error'])){
		wpjam_send_json(array(
			'errcode'	=> 'upload_error',
			'errmsg'	=> $upload_file['error']
		));	
	}

	$media_url		= $upload_file['url'];
}

wpjam_send_json(array(
	'errcode'	=> 0,
	'url'		=> wpjam_get_thumbnail($media_url)
));