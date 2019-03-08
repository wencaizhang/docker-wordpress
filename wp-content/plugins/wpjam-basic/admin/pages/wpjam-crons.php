<?php
include(WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-cron.php');

add_filter('wpjam_crons_list_table', function(){
	return [
		'title'		=> '定时作业',
		'plural'	=> 'crons',
		'singular' 	=> 'cron',
		'fixed'		=> false,
		'ajax'		=> true,
		'model'		=> 'WPJAM_Cron'
	];
});