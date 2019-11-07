<?php
/*
Plugin Name: 301 跳转
Plugin URI: http://blog.wpjam.com/project/wpjam-basic/
Description: 网站上的 404 页面跳转到正确页面。
Version: 1.0
*/
add_action('template_redirect',function(){
	if(!is_404()) return;

	$request_url =  wpjam_get_current_page_url();

	if(strpos( $request_url, 'feed/atom/')  !== false){
		wp_redirect(str_replace('feed/atom/', '', $request_url),301);
		exit;
	}

	if(strpos( $request_url, 'comment-page-')  !== false){
		wp_redirect(preg_replace('/comment-page-(.*)\//', '',  $request_url),301);
		exit;
	}

	if(strpos( $request_url, 'page/')  !== false){
		wp_redirect(preg_replace('/page\/(.*)\//', '',  $request_url),301);
		exit;
	}

	if($wpjam_301_redirects = get_option('301-redirects')){
		foreach ($wpjam_301_redirects as $wpjam_301_redirect) {
			if($wpjam_301_redirect['request'] == $request_url){
				wp_redirect($wpjam_301_redirect['destination'],301);
				exit;
			}
		}
	}
	
},99);
