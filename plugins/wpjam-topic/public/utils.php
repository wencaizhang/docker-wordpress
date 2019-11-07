<?php
function wpjam_get_topic_blog_id(){
	if(is_multisite()){
		return apply_filters('wpjam_topic_blog_id', 0);
	}else{
		return get_current_blog_id();
	}
}

function wpjam_is_topic_blog(){
	if(is_multisite()){
		return get_current_blog_id() == wpjam_get_topic_blog_id();	
	}else{
		return true;
	}
}

function wpjam_topic_switch_to_blog(){
	return is_multisite() ? switch_to_blog(wpjam_get_topic_blog_id()) : false;
}