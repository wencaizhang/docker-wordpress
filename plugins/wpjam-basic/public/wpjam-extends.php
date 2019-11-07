<?php
$wpjam_extends	= get_option('wpjam-extends');
$wpjam_extends	= $wpjam_extends ? array_filter($wpjam_extends) : [];

if(is_multisite() && $wpjam_sitewide_extends = get_site_option('wpjam-extends')){
	$wpjam_extends	= array_merge($wpjam_extends, array_filter($wpjam_sitewide_extends));
}

if($wpjam_extends){
	if(isset($wpjam_extends['plugin_page'])){
		unset($wpjam_extends['plugin_page']);	
	}
	
	$wpjam_extend_dir 	= WPJAM_BASIC_PLUGIN_DIR.'extends/';

	foreach (array_keys($wpjam_extends) as $wpjam_extend_file) {
		if(is_file($wpjam_extend_dir.$wpjam_extend_file)){
			include $wpjam_extend_dir.$wpjam_extend_file;

			if(is_admin() && is_file($wpjam_extend_dir.'admin/'.$wpjam_extend_file)){
				include $wpjam_extend_dir.'admin/'.$wpjam_extend_file;
			}
		}
	}
}