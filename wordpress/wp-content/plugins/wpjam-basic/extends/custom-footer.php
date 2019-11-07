<?php
/*
Plugin Name: 文章页代码
Plugin URI: http://blog.wpjam.com/project/wpjam-basic/
Description: 在文章编辑页面可以单独设置每篇文章 Footer 代码
Version: 1.0
*/

add_action('wp_footer', function (){
	if(!is_admin()){
		if(is_singular()){
			echo get_post_meta(get_the_ID(), 'custom_footer', true);
		}
	}	
});