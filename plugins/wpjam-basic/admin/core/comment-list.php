<?php
do_action('wpjam_comment_list_page_file');

// 显示留言 ID
add_filter('comment_row_actions',function ($actions, $comment){
	if($comment->comment_type){
		return [];
	}else{
		$actions['comment_id'] = 'ID：'.$comment->comment_ID;
		return $actions;
	}

}, 10, 2);

add_filter('comment_author', function($author, $comment_id){
	global $pagenow;

	if($pagenow == 'edit-comments.php'){
		$comment	= get_comment($comment_id);

		if($comment->user_id){
			return	'<a href="'.admin_url('edit-comments.php?user_id='.$comment->user_id).'">'.$author.'</a>';
		}
	}

	return $author;
}, 99, 2);

add_filter('comment_text', function($comment_text, $comment=null){
	if($comment){
		$type	= $comment->comment_type;
		$types	= WPJAM_Comment::get_types();
		$value	= $types[$type] ?? $type;

		return ($type ? '<strong>'.$value.'</strong>' : '' ). $comment_text;
	}

	return $comment_text;
		
}, 10, 2);



