<?php
add_action('wpjam_post_page_file', function($post_type){
	include WPJAM_BASIC_PLUGIN_DIR.'extends/admin/posts/post-type-switcher.php';
});