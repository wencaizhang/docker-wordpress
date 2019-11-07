<?php
include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-posts-list-table.php';

global $wpjam_list_table;

do_action('wpjam_post_list_page_file', $post_type);

if(empty($wpjam_list_table)){
	$wpjam_list_table	= new WPJAM_Posts_List_Table([
		'post_type'	=> $post_type
	]);
}

