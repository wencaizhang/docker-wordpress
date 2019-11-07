<?php
/*
Plugin Name: 高级分享
Plugin URI: 
Description: 可以单独设置每篇文章的分享标题，链接，缩略图，摘要，页面是否隐藏网页右上角按钮和底部导航栏等。
Version: 1.0
Author URI: http://blog.wpjam.com/
*/

//foreach (array('weixin_hide_toolbar','weixin_hide_option_menu','weixin_share_title','weixin_share_desc','weixin_share_img','weixin_share_url') as $weixin_share_filter) {
foreach (array('weixin_hide_option_menu','weixin_share_title','weixin_share_desc','weixin_share_img','weixin_share_url') as $weixin_share_filter) {
	add_filter( $weixin_share_filter, 'weixin_robot_advanced_share' );
}

function weixin_robot_advanced_share($original){
	$current_filter = current_filter();
	if( is_singular() && ( $filtered = get_post_meta( get_the_ID(), $current_filter, true ) ) ) {
		return $filtered;
	}
	return $original;
}







