<?php
class WPJAM_Post{
	public static function get_posts($post_ids, $args=[]){
		$posts = self::update_caches($post_ids, $args);

		return $posts ? array_values($posts) : [];
	}

	public static function validate($post_id, $post_type='', $action=''){
		$post	= get_post($post_id);

		if(!$post){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		if($post_type && $post_type != 'any' && $post_type != $post->post_type){
			return new WP_Error('invalid_post_type', '非法文章类型');
		}

		if($action){
			if($action == 'comment'){
				if(!post_type_supports($post_type, 'comments')){
					return new WP_Error('action_not_support', '操作不支持');
				}
			}else{
				$action		= str_replace('un', '', $action);

				if(!post_type_supports($post_type, $action) && !post_type_supports($post_type, $action.'s')){
					return new WP_Error('action_not_support', '操作不支持');
				}
			}
		}

		return $post;
	}

	public static function get_views($post_id, $addon=false){
		$views	= intval(get_post_meta($post_id, 'views', true));
		if($addon){
			return $views + apply_filters('wpjam_post_views_addon', 0, $post_id);
		}else{
			return $views;
		}
	}

	public static function update_views($post_id){
		static $post_viewed;

		if(!empty($post_viewed)){
			return;
		}

		$post_viewed	= true;

		$views	= self::get_views($post_id);
		$views++;
		
		return update_post_meta($post_id, 'views', $views);
	}

	public static function get_content($post=null, $raw=false){
		$post	= get_post($post);

		if(!($post instanceof WP_Post)){
			return '';
		}

		if(post_password_required($post)){
			if(is_singular()){
				return get_the_password_form($post);	
			}else{
				return '';
			}
		}

		$content	= $post->post_content;

		if($raw){
			return $content;
		}

		$content	= apply_filters('the_content', $content);
		$content	= str_replace(']]>', ']]&gt;', $content);

		return $content;
	}

	public static function get_excerpt($post=null, $excerpt_length=200){
		$post	= get_post($post);

		if(!($post instanceof WP_Post)){
			return '';
		}

		$text = $post->post_excerpt;
		if($text){
			$text	= wp_strip_all_tags($text);	
		}else{
			if(post_password_required($post)){
				return '';
			}

			$text	= self::get_content($post, true);
			$text	= strip_shortcodes($text);
			$text	= function_exists('excerpt_remove_blocks') ? excerpt_remove_blocks($text) : $text;
			$text	= apply_filters('the_content', $text);
			$text	= str_replace(']]>', ']]&gt;', $text);
			$text	= wp_strip_all_tags($text);
			
			$excerpt_length	= apply_filters('excerpt_length', $excerpt_length);
			$excerpt_more	= apply_filters('excerpt_more', ' ' . '&hellip;');
			
			$text	= mb_strimwidth($text, 0, $excerpt_length, $excerpt_more, 'utf-8');
		}

		return trim(preg_replace("/[\n\r\t ]+/", ' ', $text), ' ');
	}

	public static function get_thumbnail_url($post=null, $size='thumbnail', $crop=1){
		$post	= get_post($post);

		if(!($post instanceof WP_Post)){
			return '';
		}

		if(post_type_supports($post->post_type, 'thumbnail') && has_post_thumbnail($post)){
			$thumbnail_url	= wp_get_attachment_image_url(get_post_thumbnail_id($post), 'full');
		}else{
			$thumbnail_url	= apply_filters('wpjam_post_thumbnail_url', '', $post);
			$thumbnail_url	= apply_filters_deprecated('wpjam_post_thumbnail_uri', [$thumbnail_url, $post], 'WPJAM Basic 3.2', 'wpjam_post_thumbnail_url');
		}

		return $thumbnail_url ? wpjam_get_thumbnail($thumbnail_url, $size, $crop) : '';
	}

	public static function get_first_image_url($content=null, $size='full'){
		if(is_null($content) || is_object($content)){
			$post	= $content;
			
			if(post_password_required($post)){
				return '';
			}

			$content	= self::get_content($post);
		}

		if($content){
			preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $content, $matches );
			if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
				$image_id = $matches[1][0];
				return wp_get_attachment_image_url($image_id, $size);
			}

			preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches);
			if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
				return wpjam_get_thumbnail($matches[1][0], $size);
			}
		}
			
		return '';
	}

	public static function get_author($post=null, $size=96){
		$post	= get_post($post);
		
		if(!($post instanceof WP_Post)){
			return null;
		}

		$author_id	= $post->post_author;
		$author		= get_userdata($author_id);

		if($author){
			return [
				'id'		=> intval($author_id),
				'name'		=> $author->display_name,
				'avatar'	=> get_avatar_url($author_id, 200),
			];
		}else{
			return null;
		}
	}

	public static function get_related_query($post=null, $number=5, $post_type=null){
		$post	= get_post($post);

		if(!($post instanceof WP_Post)){
			return [];
		}

		$post_id	= $post->ID;
		$post_type	= $post_type ?: $post->post_type;
		$number		= $number ?: 5;

		$term_taxonomy_ids = [];
		if($taxonomies = get_object_taxonomies($post->post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($terms	= get_the_terms($post_id, $taxonomy)){
					$term_taxonomy_ids = array_merge($term_taxonomy_ids, array_column($terms, 'term_taxonomy_id'));
				}
			}

			$term_taxonomy_ids	= array_unique(array_filter($term_taxonomy_ids));
		}
		
		return new WP_Query([
			'cache_it'				=> true,
			'no_found_rows'			=> true,
			'ignore_sticky_posts'	=> true,
			'cache_results'			=> true,
			'related_query'			=> true,
			'post_status'			=> 'publish',
			'post_type'				=> $post_type,
			'posts_per_page'		=> $number,
			'post__not_in'			=> [$post_id],
			'term_taxonomy_ids'		=> $term_taxonomy_ids,
		]);
	}

	public static function get_related($post_id=null, $args=[]){
		$post	= get_post($post_id);

		if(!($post instanceof WP_Post)){
			return [];
		}

		$args	= apply_filters('wpjam_related_posts_args', $args);
		$args	= wp_parse_args($args, [
			'post_type'	=> null,
			'number'	=> 5
		]);

		$related_query	= self::get_related_query($post_id, $args['number'], $args['post_type']);

		return self::get_list($related_query, 'wpjam_related_post_json', $args);
	}

	public static function get_list($wpjam_query, $fileter='', $args=[]){
		$args	= wp_parse_args($args, [
			'size'		=> ['width'=>200, 'height'=>200]
		]);

		$posts_json	= [];

		if($wpjam_query->have_posts()){

			while($wpjam_query->have_posts()){
				$wpjam_query->the_post();

				global $post;
				
				$post_json	= [];

				$post_json['id']		= $post->ID;
				$post_json['timestamp']	= intval(strtotime(get_gmt_from_date($post->post_date)));
				$post_json['time']		= wpjam_human_time_diff($post_json['timestamp']);
				
				$post_json['title']		= html_entity_decode(get_the_title($post));

				if(is_post_type_viewable($post->post_type)){
					$post_json['name']		= urldecode($post->post_name);
					$post_json['post_url']	= str_replace(home_url(), '', get_permalink($post->ID));
				}

				if(post_type_supports($post->post_type, 'author')){
					$post_json['author']	= self::get_author($post);
				}

				if(post_type_supports($post->post_type, 'excerpt')){
					$post_json['excerpt']	= html_entity_decode(apply_filters('the_excerpt', get_the_excerpt($post)));
				}

				$post_json['thumbnail']		= self::get_thumbnail_url($post, $args['size']);

				$post_json		= apply_filters($fileter, $post_json, $post->ID, $args);

				$posts_json[]	= $post_json;
			}
		}

		wp_reset_postdata();

		return $posts_json;
	}

	public static function get($post_id){
		$args	= ['require_content'=>true];

		if(is_admin()){
			$args['raw_content']	= true;
		}

		return self::parse_for_json($post_id, $args);
	}

	public static function insert($data){
		$data['post_status']	= $data['post_status']	?? 'publish';
		$data['post_author']	= $data['post_author']	?? get_current_user_id();
		$data['post_date']		= $data['post_date']	?? get_date_from_gmt(date('Y-m-d H:i:s', time()));

		return wp_insert_post($data, true);
	}

	public static function update($post_id, $data){
		$post	= get_post($post_id);
		if(!$post){
			return new WP_Error('post_not_exists', '文章不存在');
		}
		
		$data['ID'] = $post_id;

		return wp_update_post($data, true);
	}

	public static function delete($post_id){
		$post	= get_post($post_id);
		if(!$post){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		$result		= wp_delete_post($post_id);

		if(!$result){
			return new WP_Error('delete_failed', '删除失败');
		}else{
			return true;
		}
	}

	public static function parse_for_json($post_id, $args=[]){
		$args	= wp_parse_args($args, array(
			'thumbnail_size'	=> is_singular()?'750x0':'200x200',
			'require_content'	=> false,
			'raw_content'		=> false
		));

		if(empty($post_id))	{
			return null;
		}

		global $post;

		$post	= get_post($post_id);

		if(empty($post)){
			return null;
		}

		$post_id	= intval($post->ID);
		$post_type	= $post->post_type;

		if(!post_type_exists($post_type)){
			return [];
		}

		$post_json	= [];

		$post_json['id']		= $post_id;
		$post_json['post_type']	= $post_type;
		$post_json['status']	= $post->post_status;

		if($post->post_password){
			$post_json['password_protected']	= true;
			if(post_password_required($post)){
				$post_json['passed']	= false;
			}else{
				$post_json['passed']	= true;
			}
		}else{
			$post_json['password_protected']	= false;
		}

		$post_json['timestamp']			= intval(strtotime(get_gmt_from_date($post->post_date)));
		$post_json['time']				= wpjam_human_time_diff($post_json['timestamp']);
		$post_json['modified_timestamp']= intval(strtotime($post->post_modified_gmt));
		$post_json['modified']			= wpjam_human_time_diff($post_json['modified_timestamp']);

		if(is_post_type_viewable($post_type)){
			$post_json['name']		= urldecode($post->post_name);
			$post_json['post_url']	= str_replace(home_url(), '', get_permalink($post_id));
		}

		$post_json['title']		= '';
		if(post_type_supports($post_type, 'title')){
			$post_json['title']			= html_entity_decode(get_the_title($post));
			$post_json['page_title']	= $post_json['title'];
			$post_json['share_title']	= $post_json['title'];
		}

		$post_json['thumbnail']		= self::get_thumbnail_url($post, $args['thumbnail_size']);

		if(post_type_supports($post_type, 'author')){
			$post_json['author']	= self::get_author($post);
		}
		
		if(post_type_supports($post_type, 'excerpt')){
			$post_json['excerpt']	= html_entity_decode(apply_filters('the_excerpt', get_the_excerpt($post)));
		}

		if(post_type_supports($post_type, 'page-attributes')){
			$post_json['menu_order']	= intval($post->menu_order);
		}

		if($taxonomies = get_object_taxonomies($post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($taxonomy == 'post_format'){
					$_format	= get_the_terms($post_id, 'post_format');

					if(empty($_format)){
						$post_json['format']	= '';
					}else{
						$format = reset($_format);
						$post_json['format'] 	= str_replace('post-format-', '', $format->slug );
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

		if(is_singular($post_type) || $args['require_content']){
			if(post_type_supports($post_type, 'editor')){
				if($args['raw_content']){
					$post_json['raw_content']	= self::get_content($post, true);
				}

				$post_json['content']	= self::get_content($post);	
			}

			if(is_singular($post_type)){
				self::update_views($post_id);
			}
		}

		$post_json['views']	= self::get_views($post_id);

		return apply_filters('wpjam_post_json', $post_json, $post_id, $args);
	}

	public static function get_by_ids($post_ids){
		return self::update_caches($post_ids);
	}
	
	public static function update_caches($post_ids, $args=[]){
		if($post_ids){
			$post_ids 	= array_filter($post_ids);
			$post_ids 	= array_unique($post_ids);
		}

		if(empty($post_ids)) {
			return [];
		}

		$update_term_cache	= $args['update_post_term_cache'] ?? true;
		$update_meta_cache	= $args['update_post_meta_cache'] ?? true;

		_prime_post_caches($post_ids, $update_term_cache, $update_meta_cache);

		return wp_cache_get_multi($post_ids, 'posts');
	}


}

class WPJAM_PostType extends WPJAM_Post{
	// 兼容，以后去掉
}
