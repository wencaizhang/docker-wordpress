<?php
add_filter('wpjam_pages', function ($wpjam_pages){

	if(defined('WEIXIN_ROBOT_PLUGIN_DIR')){
		$wpjam_pages['posts']['subs']['grabmp'] = array(
			'menu_title'	=>'抓取图文',
			'page_file'		=> WPJAM_GRABMP_PLUGIN_DIR.'admin/grabmp.php'
		);
	}

	return $wpjam_pages;
});