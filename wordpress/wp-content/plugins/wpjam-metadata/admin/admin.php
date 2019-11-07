<?php
add_filter('wpjam_pages', function($wpjam_pages){
	if(!is_multisite() || !is_network_admin()){
		$capability	= is_multisite() ? 'manage_sites' : 'manage_options';

		$subs['wpjam-post-metas']	= [
			'menu_title'	=> 'Post Meta', 
			'function'		=> 'list',
			'capability'	=> $capability,
			'page_file'		=> WPJAM_METADATA_PLUGIN_DIR.'admin/post-metas.php'
		];

		$subs['wpjam-term-metas']	= [
			'menu_title'	=> 'Term Meta', 
			'function'		=> 'list',
			'capability'	=> $capability,
			'page_file'		=> WPJAM_METADATA_PLUGIN_DIR.'admin/term-metas.php'
		];

		$subs['wpjam-user-metas']	= [
			'menu_title'	=> 'User Meta', 
			'function'		=> 'list',
			'capability'	=> $capability,
			'page_file'		=> WPJAM_METADATA_PLUGIN_DIR.'admin/user-metas.php'
		];

		$subs['wpjam-comment-metas']	= [
			'menu_title'	=> 'Comment Meta', 
			'function'		=> 'list',
			'capability'	=> $capability,
			'page_file'		=> WPJAM_METADATA_PLUGIN_DIR.'admin/comment-metas.php'
		];

		$wpjam_pages['wpjam-post-metas']	= [
			'menu_title'	=> 'Meta Data', 
			'function'		=> 'list',
			'capability'	=> $capability,
			'icon'			=> 'dashicons-tide',
			'position'		=> '58.101',
			'subs'			=> $subs
		];
	}

	return $wpjam_pages;
});