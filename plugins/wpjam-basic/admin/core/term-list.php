<?php
include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-terms-list-table.php';

global $wpjam_list_table;

do_action('wpjam_term_list_page_file', $taxonomy);

include WPJAM_BASIC_PLUGIN_DIR.'admin/core/term.php';

if(empty($wpjam_list_table)){
	$wpjam_list_table	= new WPJAM_Terms_List_Table([
		'taxonomy'	=> $taxonomy
	]);
}