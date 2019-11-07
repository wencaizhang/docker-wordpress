<?php
// WP_Query 缓存
function wpjam_query($args=[], $cache_time='600'){
	$args['no_found_rows']	= $args['no_found_rows'] ?? true;
	$args['cache_results']	= $args['cache_results'] ?? true;

	$args['cache_it']	= true;

	return new WP_Query($args);
}

function wpjam_validate_post($post_id, $post_type='', $action=''){
	return WPJAM_Post::validate($post_id, $post_type, $action);
}

function wpjam_get_post($post_id, $args=[]){
	return WPJAM_Post::parse_for_json($post_id, $args);
}

function wpjam_get_posts($post_ids, $args=[]){
	return WPJAM_Post::get_posts($post_ids, $args);
}

function wpjam_get_post_views($post_id, $addon=true){
	return WPJAM_Post::get_views($post_id, $addon);
}

function wpjam_update_post_views($post_id, $type='views'){
	return WPJAM_Post::update_views($post_id, $type);
}

function wpjam_get_post_excerpt($post=null, $excerpt_length=240){
	return WPJAM_Post::get_excerpt($post, $excerpt_length);
}

function wpjam_has_post_thumbnail(){
	return wpjam_get_post_thumbnail_url() ? true : false;
}

function wpjam_post_thumbnail($size='thumbnail', $crop=1, $class='wp-post-image', $retina=2){
	echo wpjam_get_post_thumbnail(null, $size, $crop, $class, $retina);
}

function wpjam_get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $class='wp-post-image', $retina=2){
	$size	= wpjam_parse_size($size, $retina);
	if($post_thumbnail_url = wpjam_get_post_thumbnail_url($post, $size, $crop)){
		$image_hwstring	= image_hwstring($size['width']/$retina, $size['height']/$retina);
		return '<img src="'.$post_thumbnail_url.'" alt="'.the_title_attribute(['echo'=>false]).'" class="'.$class.'"'.$image_hwstring.' />';
	}else{
		return '';
	}
}

function wpjam_get_post_thumbnail_url($post=null, $size='full', $crop=1){
	return WPJAM_Post::get_thumbnail_url($post, $size, $crop);
}

function wpjam_get_post_first_image_url($post=null, $size='full'){
	return WPJAM_Post::get_first_image_url($post, $size);
}

function wpjam_get_related_posts_query($number=5, $post_type=null){
	return WPJAM_Post::get_related_query(null, $number, $post_type);
}

function wpjam_related_posts($args=[]){
	echo wpjam_get_related_posts($args);
}

function wpjam_get_related_posts($args=[]){
	$args	= apply_filters('wpjam_related_posts_args', $args);

	$post_type	= $args['post_type'] ?? null;
	$number		= $args['number'] ?? null;

	$related_query	= wpjam_get_related_posts_query($number, $post_type);
	$related_posts	= wpjam_get_post_list($related_query, $args);

	return $related_posts;
}

function wpjam_get_new_posts($args=[]){
	$wpjam_query	= wpjam_query(array(
		'posts_per_page'=> $args['number'] ?? 5, 
		'post_type'		=> $args['post_type'] ?? 'post', 
		'orderby'		=> $args['orderby'] ?? 'date', 
	));

	return wpjam_get_post_list($wpjam_query, $args);
}

function wpjam_new_posts($args=[]){
	echo wpjam_get_new_posts($args);
}

function wpjam_get_top_viewd_posts($args=[]){
	$date_query	= array();

	if(isset($args['days'])){
		$date_query	= array(array(
			'column'	=> $args['column']??'post_date_gmt',
			'after'		=> $args['days'].' days ago',
		));
	}

	$wpjam_query	= wpjam_query(array(
		'posts_per_page'=> $args['number'] ?? 5, 
		'post_type'		=> $args['post_type']??['post'], 
		'orderby'		=> 'meta_value_num', 
		'meta_key'		=> 'views', 
		'date_query'	=> $date_query 
	));

	return wpjam_get_post_list($wpjam_query, $args);
}

function wpjam_top_viewd_posts($args=[]){
	echo wpjam_get_top_viewd_posts($args);
}

function wpjam_get_post_list($wpjam_query, $args=[]){
	if(!$wpjam_query){
		return false;
	}
	
	extract(wp_parse_args($args, array(
		'title'			=> '',
		'div_id'		=> '',
		'class'			=> '', 
		'thumb'			=> true,	
		'excerpt'		=> false, 
		'size'			=> 'thumbnail', 
		'crop'			=> true, 
		'thumb_class'	=> 'wp-post-image'
	)));

	if($thumb)	{
		$class	= $class.' has-thumb';
	}

	if($class){
		$class	= ' class="'.$class.'"';	
	}

	if(is_singular()){
		$post_id	= get_the_ID();
	}

	$output = '';
	$i = 0;

	if($wpjam_query->have_posts()){
		while($wpjam_query->have_posts()){
			$wpjam_query->the_post();

			$li = '';

			if($thumb || $excerpt){
				if($thumb){
					$li .=	wpjam_get_post_thumbnail(null, $size, $crop, $thumb_class)."\n";
				}

				$li .=	'<h4>'.get_the_title().'</h4>';

				if($excerpt){
					$li .= '<p>'.get_the_excerpt().'</p>';
				}
			}else{
				$li .= get_the_title();
			}

			if(!is_singular() || (is_singular() && $post_id != get_the_ID())) {
				$li =	'<a href="'.get_permalink().'" title="'.the_title_attribute(['echo'=>false]).'">'.$li.'</a>';
			}

			$output .=	'<li>'.$li.'</li>'."\n";
		}

		$output = '<ul '.$class.'>'."\n".$output.'</ul>'."\n";

		if($title){
			$output	= '<h3>'.$title.'</h3>'."\n".$output;
		}

		if($div_id){
			$output	= '<div id="'.$div_id.'">'."\n".$output.'</div>'."\n";
		}
	}

	wp_reset_postdata();
	return $output;	
}

function wpjam_find_post_id_by_old_slug($post_name, $post_type=''){
	global $wpdb;

	$sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_old_slug' AND meta_value = %s", $post_name);

	$post_ids	= $wpdb->get_col($sql);

	if(empty($post_ids)){
		return false;
	}

	$posts		= wpjam_get_posts($post_ids);
	if(empty($posts)){
		return false;
	}

	$post_id	= current($posts)->ID;

	if($post_type && $post_type != 'any'){ // 指定 post_type 则获取首先获取 post_type 相同的
		$filtered_posts	= array_filter($posts, function($post) use($post_type){
			return $post->post_type == $post_type;
		});

		if($filtered_posts){
			$post_id	= current($filtered_posts)->ID;
		}
	}

	return $post_id;
}

function wpjam_guess_post_id_by_post_name($post_name, $post_type=''){
	global $wpdb;

	$post_types	= get_post_types(['public' => true]);
	unset($post_types['attachment']);
	$post_types	= "'" . implode("','", $post_types) . "'";

	$where	= $wpdb->prepare("post_name LIKE %s", $wpdb->esc_like($post_name) . '%');
	$posts	= $wpdb->get_results("SELECT ID, post_type FROM $wpdb->posts WHERE $where AND post_type in ($post_types) AND post_status = 'publish'");

	if(empty($posts)){
		return false;
	}

	$post_id	= current($posts)->ID;

	if($post_type && $post_type != 'any'){	// 指定 post_type 则获取首先获取 post_type 相同的
		$filtered_posts	= array_filter($posts, function($post) use($post_type){
			if(is_array($post_type)){
				return in_array($post->post_type, $post_type);
			}else{
				return $post->post_type == $post_type;
			}
		});

		if($filtered_posts){
			$post_id	= current($filtered_posts)->ID;
		}
	}

	return $post_id;
}

function wpjam_get_terms(){
	$args_num	= func_num_args();
	$func_args	= func_get_args();

	if($func_args[0] && wp_is_numeric_array($func_args[0])){
		$term_ids	= $func_args[0];
		$args		= [];

		if($args_num == 2){
			$args	= $func_args[1];
		}

		$terms	= WPJAM_Term::update_caches($term_ids, $args);

		return $terms ? array_values($terms) : [];
	}else{
		$args		= $func_args[0];
		$max_depth	= -1;

		if($args_num == 2){
			$max_depth	= $func_args[1];
		}

		return WPJAM_Term::get_terms($args, $max_depth);
	}
}

function wpjam_get_term($term, $taxonomy){
	return WPJAM_Term::get_term($term, $taxonomy);
}

function wpjam_get_parent_terms($term){
	return WPJAM_Term::get_parents($term);
}

function wpjam_has_term_thumbnail(){
	return wpjam_get_term_thumbnail_url()? true : false;
}

function wpjam_term_thumbnail($size='thumbnail', $crop=1, $class="wp-term-image", $retina=2){
	echo  wpjam_get_term_thumbnail(null, $size, $crop, $class);
}

function wpjam_get_term_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-term-image", $retina=2){
	$size	= wpjam_parse_size($size, $retina);
	
	if($term_thumbnail_url = wpjam_get_term_thumbnail_url($term, $size, $crop)){
		$image_hwstring	= image_hwstring($size['width']/$retina, $size['height']/$retina);
		
		return  '<img src="'.$term_thumbnail_url.'" class="'.$class.'"'.$image_hwstring.' />';
	}else{
		return '';
	}
}

function wpjam_get_term_thumbnail_url($term=null, $size='full', $crop=1){
	return WPJAM_Term::get_thumbnail_url($term, $size, $crop);
}






add_filter('post_password_required',function ($required, $post){
	if(!$required){
		return $required;
	}

	$password	= $_REQUEST['post_password'] ?? '';

	if(empty($password)){
		return $required;
	}

	require_once ABSPATH . WPINC . '/class-phpass.php';
	$hasher	= new PasswordHash( 8, true );
	$hash	= wp_unslash($password);

	if(0 !== strpos($hash, '$P$B')) {
		return true;
	}
	
	return ! $hasher->CheckPassword( $post->post_password, $hash );
}, 10, 2);

add_action('parse_query', function (&$wp_query){

	$orderby	= $wp_query->get('orderby');

	if($orderby){
		$meta_keys	= ['views', 'favs', 'likes'];

		if(in_array($orderby, $meta_keys)){
			$order	= $wp_query->get('order'); 
			$order	= $order ?: 'DESC';

			$wp_query->set('orderby',	['meta_value_num'=>$order, 'date'=>$order]);
			$wp_query->set('meta_key',	$orderby);
		}
	}
});

add_filter('posts_clauses',	function ($clauses, $wp_query){
	if($wp_query->get('related_query')){
		if($term_taxonomy_ids	= $wp_query->get('term_taxonomy_ids')){
			global $wpdb;
			$clauses['fields']	.= ", count(tr.object_id) as cnt";
			$clauses['join']	.= "INNER JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
			$clauses['where']	.= " AND tr.term_taxonomy_id IN (".implode(",",$term_taxonomy_ids).")";
			$clauses['groupby']	.= " tr.object_id";
			$clauses['orderby']	= " cnt DESC, {$wpdb->posts}.post_date_gmt DESC";	
		}
	}
	return $clauses;
}, 10, 2);
