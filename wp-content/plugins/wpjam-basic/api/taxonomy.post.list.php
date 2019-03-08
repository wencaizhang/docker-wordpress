<?php
trigger_error('文章分类列表');

global $wp, $wp_query;

$taxonomy	= $args['taxonomy'];
unset($args['taxonomy']);
$output		= ($output)?:$taxonomy.'s';

if($taxonomy == 'category'){
	$taxonomy_key	= 'cat';
}elseif($taxonomy == 'post_tag'){
	$taxonomy_key	= 'tag_id';
}else{
	$taxonomy_key	= $taxonomy.'_id';
}

$term_ids	= ($args[$taxonomy_key])??'';
if($term_ids){
	$term_ids = explode(',', $term_ids);
	foreach ($term_ids as $term_id) {
		$term_json = wpjam_get_term($term_id, $taxonomy);
		if(is_wp_error($term_json)){
			wpjam_send_json($term_json);
		}

		foreach ($args as $key => $value) {
			if(in_array($key, array('post_type','posts_per_page','order','orderby','meta_key','meta_value'))){
				$wp->set_query_var($key, $value);
			}
		}

		if($taxonomy_key == 'cat' || $taxonomy_key == 'tag_id'){
			$wp->set_query_var($taxonomy_key, $term_id);
		}else{
			$wp->set_query_var('taxonomy', $taxonomy);
			$wp->set_query_var('term', get_term($term_id)->slug);
		}

		$wp->query_posts();

		$posts_json = array();

		if($wp_query->have_posts()){
			$posts_json	= array_map(function($post) use ($args){
				if($args['post_type'] == 'product' && class_exists('WPJAM_ShopProduct')){
					return WPJAM_ShopProduct::synthesize_one($post->ID);
				}else{
					return wpjam_get_post($post->ID, $args);
				}
			}, $wp_query->posts);
		}

		$sub_output	= ($args['sub_output'])??$args['post_type'].'s';
		$term_json[$sub_output]	= $posts_json;
		$response[$output][]	= $term_json;
	}
}