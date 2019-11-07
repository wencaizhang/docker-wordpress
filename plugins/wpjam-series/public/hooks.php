<?php
function wpjam_get_series_post_types(){
	$post_types	= wpjam_basic_get_setting('series_post_types');

	if(is_null($post_types)){
		$post_types	= ['post'];
	}

	return $post_types;
}

add_filter('wpjam_taxonomies', function ($wpjam_taxonomies){
	$wpjam_taxonomies['series'] = [
		'object_type'	=> wpjam_get_series_post_types(),
		'args'			=> [
			'label'				=> '专题',
			'hierarchical'		=> true, 
			'public'			=> true,
			'show_ui'			=> true,
			'show_admin_column'	=> false,
			'query_var'			=> true, 
			'tax'				=> 'series',
			'show_in_rest'		=> true,
			'levels'			=> 1,
			'rewrite'			=> ['slug'=>'series', 'with_front'=>false]
		]
	];

	return $wpjam_taxonomies;
});

add_filter('the_content', function($content) {
	$post_id	= get_the_ID();

	if(doing_filter('get_the_excerpt') || !is_singular() || $post_id != get_queried_object_id()){ 
		return $content;
	}
	
	$post_types	= wpjam_get_series_post_types();

	if(empty($post_types) || !in_array(get_post_type(), $post_types)){
		return $content;
	}

	$series = get_the_terms($post_id, 'series');

	if(!$series){
		return $content;
	}

	$series	= current($series);
	
	$series_posts_query = new WP_Query([
		'post_type'					=> $post_types, 
		'posts_per_page'			=> -1, 
		'cache_it'					=> true,
		'no_found_rows'				=> true,
		'update_post_term_cache'	=> false,
		'update_post_meta_cache'	=> false,
		'orderby'					=> 'date', 
		'order'						=> 'ASC',
		'tax_query'					=> [
			[
				'taxonomy'	=>'series',
				'field'		=>'term_id',
				'terms'		=>$series->term_id
			]
		]
	]);

	$content	.= "\n\n".'
	<div id="series_posts">
		<p>专题：<strong><a href="'.get_term_link($series).'" title="专题：'.esc_attr($series->name).'">'.$series->name.'</a></strong>：</p>
		'.wpjam_get_post_list($series_posts_query, ['class'=>'posts_lists',	'thumb'=>false]).'
	</div>';

	if(get_term_meta($series->term_id, 'guide', true)){
		$content	= $series->description."\n\n".$content;
	}

	return $content;
}, 1);

add_action('pre_get_posts',  function($wp_query) {
	if($wp_query->is_main_query()){
		if(is_tax('series')){
			$wp_query->set('orderby','date');
			$wp_query->set('order','ASC');
			$wp_query->set('posts_per_page','-1');

			if(!is_admin()){
				$wp_query->set('post_type', wpjam_get_series_post_types());
			}
		}
	}
}, 99);