<?php
include __DIR__ .'/class-walker-collection-checklist.php';
include __DIR__ .'/hooks.php';

add_action('wpjam_post_list_page_file', function($post_type){
	if($post_type == 'attachment'){
		include __DIR__ .'/attachment-list.php';
	}
});

add_action('wpjam_post_page_file', function($taxonomy){
	if($taxonomy == 'attachment'){
		include __DIR__ .'/attachment.php';
	}
});