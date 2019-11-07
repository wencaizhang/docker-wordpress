<?php
$user_id	= wpjam_get_current_user_id();
$post_id	= wpjam_get_parameter('post_id', ['method'=>'POST', 'type'=>'int', 'required'=>true]);
$the_post	= wpjam_validate_post($post_id, $post_type, $action);
if(is_wp_error($the_post)){
	wpjam_send_json($the_post);
}

if($action == 'comment'){
	$comment	= wpjam_get_parameter('comment',	['method'=>'POST', 'required'=>true]);
	$parent		= wpjam_get_parameter('parent',		['method'=>'POST', 'default'=>0]);

	$comment_id	= WPJAM_Comment::insert(compact('post_id', 'user_id', 'comment', 'parent'));
}else{
	$comment_id	= WPJAM_Comment::action($user_id, $post_id, $action);
}

if(is_wp_error($comment_id)){
	wpjam_send_json($comment_id);
}

$response['user']	= WPJAM_Comment::get_author($user_id);