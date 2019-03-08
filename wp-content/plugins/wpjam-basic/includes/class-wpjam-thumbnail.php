<?php
class WPJAM_Thumbnail{
	public static function get_thumbnail(){
		$args_num	= func_num_args();
		$args		= func_get_args();

		$img_url	= $args[0];

		// if(empty($img_url))	return '';

		if(CDN_NAME == '') return $img_url;

		if(strpos($img_url, '?') === false){
			$img_url	= str_replace(['%3A','%2F'], [':','/'], urlencode(urldecode($img_url)));
		}

		if($args_num == 1){	
			// 1. $img_url 简单替换一下 CDN 域名

			$thumb_args = [];
		}elseif($args_num == 2){		
			// 2. $img_url, ['width'=>100, 'height'=>100]	// 这个为最标准版本
			// 3. $img_url, [100,100]
			// 4. $img_url, 100x100
			// 5. $img_url, 100		

			$thumb_args = self::parse_size($args[1]);
		}else{
			if(is_numeric($args[1])){
				// 6. $img_url, 100, 100, $crop=1, $retina=1

				$width	= $args[1] ?? 0;
				$height	= $args[2] ?? 0;
				$crop	= $args[3] ?? 1;
				$retina	= $args[4] ?? 1;
			}else{
				// 7. $img_url, array(100,100), $crop=1, $retina=1

				$size	= self::parse_size($args[1]);
				$width	= $size['width'];
				$height	= $size['height'];
				$crop	= $args[2]??1;
				$retina	= $args[3]??1;
			}

			$thumb_args = compact('width','height','crop','retina');
		}

		return apply_filters('wpjam_thumbnail', $img_url, $thumb_args);
	}

	public static function get_default_thumbnail($size='thumbnail', $crop=1, $retina=1){
		$thumbnail_url	= wpjam_cdn_get_setting('default');
		$thumbnail_url	= apply_filters_deprecated('wpjam_default_thumbnail_uri', [$thumbnail_url], 'WPJAM Basic 3.2','wpjam_default_thumbnail_url');	
		$thumbnail_url	= apply_filters('wpjam_default_thumbnail_url', $thumbnail_url);

		if($thumbnail_url){
			return self::get_thumbnail($thumbnail_url, $size, $crop, $retina);
		}else{
			return '';
		}
	}

	public static function get_post_first_image($post_content='', $size='full'){
		if(!$post_content || is_object($post_content)){
			$the_post		= get_post($post_content);
			$post_content	= $the_post->post_content;
			$post_id		= $the_post->ID;
		}else{
			$post_id		= 0;
		}

		preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post_content, $matches );
		if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
			$image_id = $matches[1][0];
			return wp_get_attachment_image_url($image_id, $size);
		}

		preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
		if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
			$thumbnail_url	= $matches[1][0];

			if(strpos($thumbnail_url, CDN_HOST) === false && strpos($thumbnail_url, LOCAL_HOST) === false){
				$thumbnail_url = self::get_content_remote_img_url($thumbnail_url, $post_id);
			}

			return $thumbnail_url;

		}
			
		return false;
	}

	public static function get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $retina=1){
		$post	= get_post($post);

		if(!$post){
			return false;
		}

		$thumbnail_size	= CDN_NAME ? 'full' : $size;	// 有第三方CDN的话，就获取原图

		$thumbnail_url	= '';
		if(has_post_thumbnail($post)){
			$thumbnail_url	= wp_get_attachment_image_url(get_post_thumbnail_id($post), $thumbnail_size);
		}else{
			$thumbnail_url	= self::get_post_thumbnail_by_orders($post);
		}

		$thumbnail_url	= apply_filters_deprecated('wpjam_post_thumbnail_uri', [$thumbnail_url, $post], 'WPJAM Basic 3.2', 'wpjam_post_thumbnail_url');
		$thumbnail_url	= apply_filters('wpjam_post_thumbnail_url', $thumbnail_url, $post);
		$thumbnail_url	= $thumbnail_url ?: self::get_default_thumbnail();

		if($thumbnail_url){
			return self::get_thumbnail($thumbnail_url, $size, $crop, $retina);
		}else{
			return '';
		}
	}

	public static function get_post_thumbnail_by_orders($post){
		$post	= get_post($post);

		$thumbnail_orders	= wpjam_cdn_get_setting('post_thumbnail_orders') ?: [];

		if(!$thumbnail_orders){
			return '';
		}

		$term_thumbnail_type		= wpjam_cdn_get_setting('term_thumbnail_type') ?: '';
		$term_thumbnail_taxonomies	= wpjam_cdn_get_setting('term_thumbnail_taxonomies') ?: [];
		$post_taxonomies			= get_post_taxonomies($post);

		foreach ($thumbnail_orders as $thumbnail_order) {
			if($thumbnail_order['type'] == 'first'){
				if($post_first_image = self::get_post_first_image($post)){
					return $post_first_image;
				}
			}elseif($thumbnail_order['type'] == 'post_meta'){
				if($post_meta 	= $thumbnail_order['post_meta']){
					if($post_meta_url = get_post_meta($post->ID, $post_meta, true)){
						return $post_meta_url;
					}
				}
			}elseif($thumbnail_order['type'] == 'term'){
				$taxonomy	= $thumbnail_order['taxonomy'];

				if($term_thumbnail_type && $taxonomy && $term_thumbnail_taxonomies && in_array($taxonomy, $term_thumbnail_taxonomies)){
					if($post_taxonomies && in_array($taxonomy, $post_taxonomies)){
						if($terms = get_the_terms($post,$taxonomy)){
							foreach ($terms as $term) {
								if($term_thumbnail = self::get_term_thumbnail_url($term)){
									return $term_thumbnail;
								}
							}
						}
					}
				}
			}
		}
	}

	public static function get_term_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
		$term	= ($term)?:get_queried_object();
		$term	= get_term($term);

		if(!$term) {
			return false;
		}

		$thumbnail_url	= '';

		$thumbnail_url	= get_term_meta($term->term_id, 'thumbnail', true);
		$thumbnail_url	= apply_filters('wpjam_term_thumbnail_url', $thumbnail_url, $term);

		if($thumbnail_url){
			return self::get_thumbnail($thumbnail_url, $size, $crop, $retina);
		}else{
			return '';
		}
	}

	public static function parse_size($size){
		global $content_width, $_wp_additional_image_sizes;	

		if(is_array($size)){
			if(wpjam_is_assoc_array($size)){
				return $size;
			}else{
				$width	= intval($size[0]??0);
				$height	= intval($size[1]??0);
			}
		}else{
			if(strpos($size, 'x')){
				$size	= explode('x', $size);
				$width	= intval($size[0]);
				$height	= intval($size[1]);
			}elseif(is_numeric($size)){
				$width	= $size;
				$height	= 0;
			}elseif($size == 'thumb' || $size == 'thumbnail' || $size == 'post-thumbnail'){
				$width	= 
				$height = 150;
			}elseif($size == 'medium'){
				$width	= 
				$height = 300;
			}elseif($size == 'medium_large'){
				$width	= 768;
				$height	= 0;
			}elseif($size == 'large'){
				$width	= 1024;
				$height	= 0;
			}elseif(isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$size])){
				$width	= intval($_wp_additional_image_sizes[$size]['width']);
				$height	= intval($_wp_additional_image_sizes[$size]['height']);
			}else{
				$width	= 0;
				$height	= 0;
			}
		}

		return compact('width','height');
	}

	public static function content_images($content, $max_width=0){
		return preg_replace_callback('/<img.*?src=[\'"](.*?)[\'"].*?>/i', function($matches) use($max_width){
			$img_url	= trim($matches[1]);
			$img_tag	= $matches[0];

			if(empty($img_url)) {
				return $img_tag;
			}

			if(strpos($img_url, CDN_HOST) === false && strpos($img_url, LOCAL_HOST) === false){
				if(!self::can_remote_image($img_url)){
					return $img_tag;
				}else{
					$img_url	= self::get_content_remote_img_url($img_url);
				}
			}

			$size	= [
				'width'		=> 0,
				'height'	=> 0,
				'retina'	=> 2,
				'content'	=> true
			];

			preg_match_all('/(width|height)=[\'"](.*?)[\'"]/i', $img_tag, $hw_matches);
			if($hw_matches[0]){
				$hw_arr	= array_flip($hw_matches[1]);
				$size	= array_merge($size, array_combine($hw_matches[1], $hw_matches[2]));
				if($size['width'] > 10000){
					$size['width']	= 0;
				}

				if($size['height'] > 10000){
					$size['height']	= 0;
				}
			}

			$max_width	= $max_width ?: intval(apply_filters('wpjam_content_image_width', wpjam_cdn_get_setting('width')));

			if($max_width) {
				if($size['width'] >= $max_width){
					if($size['height']){
						$size['height']	= intval(($max_width / $size['width']) * $size['height']);

						$index			= $hw_arr['height'];
						$img_tag		= str_replace($hw_matches[0][$index], 'height="'.$size['height'].'"', $img_tag);
					}
					
					$size['width']	= $max_width;
					$index			= $hw_arr['width'];
					$img_tag		= str_replace($hw_matches[0][$index], 'width="'.$size['width'].'"', $img_tag);

				}elseif($size['width'] == 0){
					if($size['height'] == 0){
						$size['width']	= $max_width;
					}
				}
			}
			
			$thumbnail	= self::get_thumbnail($img_url, $size);

			return str_replace($matches[1], $thumbnail, $img_tag);

		}, $content);
	}

	public static function can_remote_image($img_url=''){
		if(!apache_mod_loaded('mod_rewrite', true) && empty($GLOBALS['is_nginx']) && !iis7_supports_permalinks()){
			return false;
		}
		
		if(!extension_loaded('gd') || get_option('permalink_structure') == false){
			return false;
		}

		if(wpjam_cdn_get_setting('remote') == false){
			return false;	//	没开启选项
		}

		if($img_url){
			$exceptions	= explode("\n", wpjam_cdn_get_setting('exceptions'));	// 后台设置不加载的远程图片

			if($exceptions){
				foreach ($exceptions as $exception) {
					if(trim($exception) && strpos($img_url, trim($exception)) !== false ){
						return false;
					}
				}
			}
		}

		return true;
	}

	public static function get_content_remote_img_url($img_url, $post_id=0){
		if(self::can_remote_image($img_url)){
			$img_type = strtolower(pathinfo($img_url, PATHINFO_EXTENSION));
			if($img_type != 'gif'){
				$img_type	= ($img_type == 'png')?'png':'jpg';
				$post_id	= $post_id ?: get_the_ID();
				$img_url	= CDN_HOST.'/'.CDN_NAME.'/'.$post_id.'/image/'.md5($img_url).'.'.$img_type;
			}
		}
		
		return $img_url;
	}
}