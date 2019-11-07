<?php
add_filter('wpjam_basic_sub_pages', function($subs){
	if(!is_multisite() || !is_network_admin()){

		$subs['wpjam-hashtag']	=[
			'menu_title'	=> '#Hashtag#', 
			'page_file'		=> WPJAM_HASHTAG_PLUGIN_DIR .'admin/hashtag.php',
			'function'		=> 'tab'
		];
	}

	return $subs;
});