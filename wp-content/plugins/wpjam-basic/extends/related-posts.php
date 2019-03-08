<?php
/*
Plugin Name: 相关文章
Plugin URI: http://blog.wpjam.com/project/wpjam-basic/
Description: 在文章末尾显示相关文章。
Version: 1.0
*/
add_filter('wpjam_related_posts_args', function($args){
	$args['number']		= $args['number']	?? wpjam_basic_get_setting('related_posts_number');
	$args['excerpt']	= $args['excerpt']	?? wpjam_basic_get_setting('related_posts_excerpt');
	$args['post_type']	= $args['post_type']?? wpjam_basic_get_setting('related_posts_post_types');
	$args['class']		= $args['class']	?? wpjam_basic_get_setting('related_posts_class');
	$args['div_id']		= $args['div_id']	?? wpjam_basic_get_setting('related_posts_div_id');
	$args['title']		= $args['title']	?? wpjam_basic_get_setting('related_posts_title');
	$args['thumb']		= $args['thumb']	?? wpjam_basic_get_setting('related_posts_thumbnail');
	
	if($args['thumb']){
		$args['size']	= [
			'width'		=> wpjam_basic_get_setting('related_posts_width'),
			'height'	=> wpjam_basic_get_setting('related_posts_height'),
		];
	}

	return $args;
});

if(wpjam_basic_get_setting('related_posts_auto')){
	add_filter('the_content', function($content){
		if(is_single() && !wpjam_get_json()){
			$post_types	= wpjam_basic_get_setting('related_posts_post_types');

			if(!$post_types || ($post_types && in_array(get_post_type(), $post_types))){
				$content	= $content.wpjam_get_related_posts();
			}
		}
		return $content;
	});

	add_filter('wpjam_post_json', function($post_json){
		if(is_single()){
			$post_types	= wpjam_basic_get_setting('related_posts_post_types');

			if(!$post_types || ($post_types && in_array(get_post_type(), $post_types))){
				$post_json['related']	= WPJAM_PostType::get_related();
			}
		}

		return $post_json;
	}, 10, 2);
}



