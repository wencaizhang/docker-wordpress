<?php
class WPJAM_PostType{
	protected static $field_post_ids_list;

	public static function get_posts($post_ids, $args=[]){
		if($post_ids){
			$post_ids 	= array_filter($post_ids);
			$post_ids 	= array_unique($post_ids);
		}

		if(empty($post_ids)) return [];

		$non_cached_ids = _get_non_cached_ids($post_ids, 'posts');
		if(!empty($non_cached_ids)){

			extract(wp_parse_args($args, array(
				'post_type'			=> 'any',
				'update_term_cache'	=> true,
				'update_meta_cache'	=> true,
				'update_wpjam_cache'=> false
			)));

			global $wpdb;

			$fresh_posts = $wpdb->get_results( sprintf( "SELECT $wpdb->posts.* FROM $wpdb->posts WHERE ID IN (%s)", join( ",", $non_cached_ids ) ) );

			if($fresh_posts){
				update_post_caches( $fresh_posts, $post_type, $update_term_cache, $update_meta_cache );
				if($update_wpjam_cache){
					self::update_field_post_ids_caches(array_column($fresh_posts, 'ID'), $fresh_posts[0]->post_type);
				}
			}
		}

		$posts = wp_cache_get_multi($post_ids, 'posts');

		return $posts ? array_values($posts):[];
	}

	public static function parse_for_json($post_id, $args=[]){
		if(empty($post_id))	return [];

		$args	= wp_parse_args($args, array(
			'thumbnail_size'		=> is_singular()?'750x0':'200x200',
			'require_content'		=> false,
			'parsed'				=> true,
			'basic'					=> false
		));

		extract($args);

		global $post;

		$post	= get_post($post_id);

		if(empty($post)) return []; 

		$post_json	= [];

		$post_type	= $post->post_type;

		$post_json['id']		= intval($post_id);
		$post_json['timestamp']	= intval(strtotime(get_gmt_from_date($post->post_date)));
		$post_json['time']		= wpjam_human_time_diff($post_json['timestamp']);
		$post_json['post_type']	= $post_type;
		$post_json['status']	= $post->post_status;
		$post_json['title']		= '';

		if(is_post_type_viewable($post_type)){
			$post_json['name']		= $post->post_name;
			$post_json['post_url']	= str_replace(home_url(), '', get_permalink($post_id));
		}

		if(post_type_supports($post_type, 'title')){
			$post_json['title']			= html_entity_decode(get_the_title($post));
			$post_json['page_title']	= $post_json['title'];
			$post_json['share_title']	= $post_json['title'];
		}

		if(post_type_supports($post_type, 'excerpt')){
			$post_json['excerpt']	= wp_strip_all_tags(apply_filters('the_excerpt', get_the_excerpt($post)));
		}

		if(is_singular($post_type) || $require_content){
			if(post_type_supports($post_type, 'editor')){
				$post_json['content']	= apply_filters('the_content', $post->post_content);
			}
		}
		
		$post_json['thumbnail']		= wpjam_get_post_thumbnail_url($post_id, $thumbnail_size);

		if($taxonomies = get_object_taxonomies($post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($taxonomy == 'post_format'){
					$_format	= get_the_terms($post_id, 'post_format');

					if(empty($_format)){
						$post_json['post_format'] = '';
					}else{
						$format = reset( $_format );
						$post_json['post_format'] =  str_replace('post-format-', '', $format->slug );
					}
				}else{
					if($terms	= get_the_terms($post_id, $taxonomy)){
						array_walk($terms, function(&$term) use ($taxonomy){ $term 	= wpjam_get_term($term, $taxonomy);});
						$post_json[$taxonomy]	= $terms;
					}else{
						$post_json[$taxonomy]	= [];
					}		
				}
			}
		}

		if(is_singular($post_type)){
			self::update_views($post_id);
			// 	$post_json['related']	= self::get_related($post_id, $args);
		}

		$post_json['views']	= self::get_views($post_id);

		if($basic) return apply_filters('wpjam_post_json', $post_json, $post_id, $args);

		if($post_fields = wpjam_get_post_fields($post_type)){
			foreach ($post_fields  as $field_key => $post_field) {
				$field_type		= $post_field['type']??'';

				if($field_type == 'fieldset'){
					if($sub_fields	= $post_field['fields']??[]){
						$fieldset_type	= $post_field['fieldset_type']??'single';

						if($fieldset_type == 'single'){
							foreach ($sub_fields as $sub_key => $sub_field) {
								$post_meta_value		= get_post_meta($post_id, $sub_key, true);
								$post_json[$sub_key]	= wpjam_parse_field_value($post_meta_value, $sub_field);
							}
						}else{
							$post_meta_value	= get_post_meta($post_id, $field_key, true);
							foreach ($sub_fields as $sub_key => $sub_field) {
								if(isset($post_meta_value[$sub_key])){
									$post_json[$field_key][$sub_key]	= wpjam_parse_field_value($post_meta_value[$sub_key], $sub_field);
								}
							}
						}
					}
				}else{
					$post_meta_value		= get_post_meta($post_id, $field_key, true);
					$post_json[$field_key]	= wpjam_parse_field_value($post_meta_value, $post_field);
				}

				if(!empty($post_field['data-type'])){
					$post_field['data_type']	= $post_field['data-type'];
				}

				if(!empty($post_field['data_type'])){
					if($post_field['data_type'] == 'vote'){
						$post_json['__vote_field']	= $field_key;
					}
				}
			}
		}

		return apply_filters('wpjam_post_json', $post_json, $post_id, $args);
	}

	public static function update_field_post_ids_cache($post_id, $post_fields, $update_cache=true){
		if(!isset(static::$field_post_ids_list)) {
			static::$field_post_ids_list = [];
		}

		if(empty(static::$field_post_ids_list[$post_id])){
			$cache_post_ids	= [];

			foreach ($post_fields  as $field_key => $post_field) {
				$post_meta_value	= get_post_meta($post_id, $field_key, true);
				if($field_post_ids	= wpjam_get_field_post_ids($post_meta_value, $post_field)){
					$cache_post_ids	= array_merge_recursive($cache_post_ids, $field_post_ids);
				}
			}

			static::$field_post_ids_list[$post_id]	= $cache_post_ids;
		}

		$cache_post_ids	= static::$field_post_ids_list[$post_id];

		if($update_cache){
			foreach ($cache_post_ids as $post_type => $the_post_ids) {
				$update_term_cache	= ($post_type == 'attachment')?false:true;
				self::get_posts($the_post_ids, compact('post_type', 'update_term_cache'));
			}
		}

		return $cache_post_ids;
	}

	public static function update_field_post_ids_caches($post_ids, $post_type){
		$cache_post_ids	= [];

		if(post_type_supports($post_type, 'thumbnail')){
			foreach ($post_ids as $post_id) {
				$cache_post_ids['attachment'][]	= get_post_thumbnail_id($post_id);
			}
		}
	
		if($post_fields = wpjam_get_post_fields($post_type)){
			foreach ($post_ids as $post_id) {
				if($field_post_ids	= self::update_field_post_ids_cache($post_id, $post_fields, $update_cache=false)){
					$cache_post_ids	= array_merge_recursive($cache_post_ids, $field_post_ids);
				}
			}
		}

		if($taxonomies = get_object_taxonomies($post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($term_fields = wpjam_get_term_options($taxonomy)){
					$term_ids = [];
					foreach ($post_ids as $post_id) {
						if($terms	= get_the_terms($post_id, $taxonomy)){
							$term_ids = array_merge($term_ids, array_column($terms, 'term_id'));
						}
					}

					$term_ids	= array_unique(array_filter($term_ids));

					if($term_ids){
						update_termmeta_cache($term_ids);
						foreach ($term_ids as $term_id) {
							if($field_post_ids	= WPJAM_Taxonomy::update_field_post_ids_cache($term_id, $term_fields, $update_cache=false)){
								$cache_post_ids	= array_merge_recursive($cache_post_ids, $field_post_ids);
							}
						}
					}
				}
			}
		}
		
		foreach ($cache_post_ids as $post_type => $post_ids) {
			$update_term_cache	= ($post_type == 'attachment')?false:true;
			self::get_posts($post_ids, compact('post_type', 'update_term_cache'));
		}	

		return $cache_post_ids;
	}

	public static function validate($post_id, $post_type='', $action=''){
		$the_post	= get_post($post_id);
		if(!$the_post){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		if($post_type && $post_type != 'any' && $post_type != $the_post->post_type){
			return new WP_Error('post_type_error', '文章类型错误');
		
			if($action){
				$post_type_object	= get_post_type_object($post_type);
				$actions	= $post_type_object->actions ?? [];

				if(empty($actions) || !in_array($action, $actions)){
					return new WP_Error('action_not_support', '操作不支持');
				}
			}
		}

		return $the_post;
	}

	public static function get_views($post_id, $type='views'){
		$views = wp_cache_get($post_id, $type);
		if($views === false){
			$views = get_post_meta($post_id, $type, true);
		}
		return intval($views);
	}

	public static function update_views($post_id, $type='views'){
		$views	= self::get_views($post_id, $type)+1;

		if(wp_using_ext_object_cache()){
			wp_cache_set($post_id, $views, $type);
			if($views%10 == 0){
				update_post_meta($post_id, $type, $views);   
			}
		}else{
			update_post_meta($post_id, $type, $views);
		}
	}

	public static function get_excerpt($post=null, $excerpt_length=240){
		$the_post = get_post($post);
		if(empty($the_post)) return '';

		$post_excerpt = $the_post->post_excerpt;
		if($post_excerpt == ''){
			$post_content   = strip_shortcodes($the_post->post_content);
			$post_content   = wp_strip_all_tags( $post_content );
			$excerpt_length = apply_filters('excerpt_length', $excerpt_length);	 
			$excerpt_more   = apply_filters('excerpt_more', ' ' . '...');
			$post_excerpt   = wpjam_get_first_p($post_content); // 获取第一段
			if(mb_strwidth($post_excerpt) < $excerpt_length*1/3 || mb_strwidth($post_excerpt) > $excerpt_length){ // 如果第一段太短或者太长，就获取内容的前 $excerpt_length 字
				$post_excerpt = mb_strimwidth($post_content,0,$excerpt_length,$excerpt_more,'utf-8');
			}
		}else{
			$post_excerpt = wp_strip_all_tags( $post_excerpt );	
		}
		
		$post_excerpt = trim( preg_replace( "/[\n\r\t ]+/", ' ', $post_excerpt ), ' ' );

		return $post_excerpt;
	}

	public static function related_query($number=5, $post_type=null){
		$the_post	= get_post();

		if(empty($the_post)){
			return false;
		}

		$post_id		= $the_post->ID;
		$post_type		= $post_type ?: $the_post->post_type;
		$number			= $number ?: 5;

		$last_changed	= wp_cache_get_last_changed('posts');
		$cache_key		= $post_id.':'.maybe_serialize($post_type).':'.$number.':'.$last_changed;

		$related_query = wp_cache_get($cache_key, 'wpjam_related_posts_query');
		if( $related_query === false) {

			$term_taxonomy_ids = [];
			if($taxonomies = get_object_taxonomies($the_post->post_type)){
				foreach ($taxonomies as $taxonomy) {
					if($terms	= get_the_terms($post_id, $taxonomy)){
						$term_taxonomy_ids = array_merge($term_taxonomy_ids, array_column($terms, 'term_taxonomy_id'));
					}

					$term_taxonomy_ids	= array_unique(array_filter($term_taxonomy_ids));
				}
			}

			if($term_taxonomy_ids){
				add_filter('posts_join', function($posts_join, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						global $wpdb;
						return "INNER JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
					}

					return $posts_join;
				},10,2);

				add_filter('posts_where', function($posts_where, $wp_query) use($term_taxonomy_ids){
					if(!empty($wp_query->query_vars['related_query'])){
						global $wpdb;
						return $posts_where . " AND tr.term_taxonomy_id IN (".implode(",",$term_taxonomy_ids).")";
					}

					return $posts_where;
				},10,2);

				add_filter('posts_groupby',	function($posts_groupby, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						return " tr.object_id";
					}

					return $posts_groupby;
				},10,2);

				add_filter('posts_orderby',function($posts_orderby, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						global $wpdb;
						return " cnt DESC, {$wpdb->posts}.post_date_gmt DESC";
					}

					return $posts_orderby;
				},10,2);

				add_filter('posts_fields',function($posts_fields, $wp_query){
					if(!empty($wp_query->query_vars['related_query'])){
						return $posts_fields.", count(tr.object_id) as cnt";
					}	

					return $posts_fields;
				},10,2);	
			}

			$related_query = new WP_Query([
				'no_found_rows'			=> true,
				'ignore_sticky_posts'	=> true,

				'post_type'				=> $post_type,
				'posts_per_page'		=> $number,
				'post__not_in'			=> [$post_id],
				
				'related_post_id'		=> $post_id,
				'related_query'			=> true,
			]);

			wp_cache_set($cache_key, $related_query, 'wpjam_related_posts_query', HOUR_IN_SECONDS*10);
		}

		return $related_query;
	}

	public static function get_related($post_id=null, $args=[]){
		$args	= apply_filters('wpjam_related_posts_args', $args);
		$args	= wp_parse_args($args, array(
			'number'	=> 5,
			'thumb'		=> true,
			'excerpt'	=> true,
			'size'		=> ['width'=>100, 'height'=>100],
			'post_type'	=> null
		));

		extract($args);

		$the_post	= get_post($post_id);

		if(empty($the_post)) return [];

		$related_json	= [];

		$related_query	= self::related_query($number, $post_type);

		if($related_query->have_posts()){
			foreach ($related_query->posts as $related_post) {
				$post_json	= [];

				$relate_post_id			= (int)$related_post->ID;

				$post_json['id']		= $relate_post_id;
				$post_json['timestamp']	= (int)strtotime(get_gmt_from_date($related_post->post_date));
				$post_json['time']		= wpjam_human_time_diff($post_json['timestamp']);
				
				$post_json['title']		= html_entity_decode(get_the_title($related_post));

				if(is_post_type_viewable($related_post->post_type)){
					$post_json['name']		= $the_post->post_name;
					$post_json['post_url']	= str_replace(home_url(), '', get_permalink($relate_post_id));
				}

				if($excerpt){
					$post_json['excerpt']	= wp_strip_all_tags(apply_filters('the_excerpt', get_the_excerpt($related_post)));
				}

				if($thumb){
					$post_json['thumbnail']	= wpjam_get_post_thumbnail_url($related_post->ID, $size, $crop=true, 2);
				}

				$related_json[]	= apply_filters('wpjam_related_post_json', $post_json, $related_post->ID, $args);
			}
		}

		return $related_json;
	}

	public static function parse_post_list($wpjam_query, $args=[]){
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
}

add_filter('the_posts', function($posts, $wp_query){
	if(!is_wpjam_json() || !$posts)	return $posts;

	if(!empty($wp_query->query['related_query'])) return $posts;

	$post_type	= $posts[0]->post_type;

	if(count($posts) >= 1){
		update_post_caches($posts, $post_type, $update_post_term_cache=true, $update_post_meta_cache=true);
	}

	WPJAM_PostType::update_field_post_ids_caches(array_column($posts, 'ID'), $post_type);

	return $posts;
}, 10, 2);
