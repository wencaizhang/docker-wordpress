<?php
function wpjam_cdn_get_setting($setting_name){
	return wpjam_get_setting('wpjam-cdn', $setting_name);
}

add_action('wp_loaded', function(){	// HTML 替换，镜像 CDN 主函数
	define('LOCAL_HOST',	untrailingslashit(wpjam_cdn_get_setting('local') ? set_url_scheme(wpjam_cdn_get_setting('local')): site_url()));
	define('CDN_HOST',		untrailingslashit(wpjam_cdn_get_setting('host') ?: site_url()));
	define('CDN_NAME',		wpjam_cdn_get_setting('cdn_name') ?: '');	// CDN 名称

	if(CDN_NAME){
		$cdn_extend = apply_filters('wpjam_cdn_extend', WPJAM_BASIC_PLUGIN_DIR.'extends/cdn/'.CDN_NAME.'.php', CDN_NAME);

		if(file_exists($cdn_extend)){
			include($cdn_extend);
		}

		add_filter('wp_get_attachment_url', function($url){
			return $url ? wpjam_get_thumbnail($url) : $url;
		});

		if(wpjam_get_content_width()){
			remove_filter('the_content', 'wp_make_content_images_responsive');
		}

		add_filter('the_content', function($content){
			if(doing_filter('get_the_excerpt')){
				return $content;
			}

			$post		= get_post();
			$max_width	= wpjam_get_content_width();
			$cache_key	= $post ? $post->ID.'_'.$max_width.'_'.strtotime($post->post_modified_gmt) : '';

			return wpjam_content_images($content, $max_width, $cache_key);
		}, 5);

		add_filter('image_downsize', function($out, $id, $size){
			if(!wp_attachment_is_image($id)){	
				return false;
			}

			$meta		= wp_get_attachment_metadata($id);
			$img_url	= wp_get_attachment_url($id);	

			$size		= wpjam_parse_size($size);

			if($size['crop']){
				$size['width']	= min($size['width'],  $meta['width']);
				$size['height']	= min($size['height'],  $meta['height']);
			}else{
				list($width, $height)	= wp_constrain_dimensions($meta['width'], $meta['height'], $size['width'], $size['height']);

				$size['width']	= $width;
				$size['height']	= $height;
			}

			if($size['width'] < $meta['width'] || $size['height'] <  $meta['height']){
				$img_url	= wpjam_get_thumbnail($img_url, $size);
			}else{
				$img_url	= wpjam_get_thumbnail($img_url);
			}

			return [$img_url, $size['width'], $size['height'], 1];
		},10 ,3);

		add_filter('wp_resource_hints', function($urls, $relation_type){
			if($relation_type == 'dns-prefetch'){
				$urls[]	= CDN_HOST;
			}
			return $urls;
		}, 10, 2);
	
		if(wpjam_can_remote_image()){
			add_rewrite_rule(CDN_NAME.'/([0-9]+)/image/([^/]+)?$', 'index.php?p=$matches[1]&'.CDN_NAME.'=$matches[2]', 'top');

			// 远程图片的 Query Var
			add_filter('query_vars', function($query_vars) {
				$query_vars[] = CDN_NAME;
				return $query_vars;
			});

			// 远程图片加载模板
			add_action('template_redirect',	function(){
				if(get_query_var(CDN_NAME)){
					include(WPJAM_BASIC_PLUGIN_DIR.'template/image.php');
					exit;
				}
			}, 5);
		}
	}
	
	ob_start(function ($html){
		
		if(wpjam_basic_get_setting('google_fonts') == 'ustc'){
			$html	= str_replace(
				[
					'//fonts.googleapis.com',
					'//ajax.googleapis.com',
					'//themes.googleusercontent.com',
					'//fonts.gstatic.com',
				], 
				[
					'//fonts.lug.ustc.edu.cn',
					'//ajax.lug.ustc.edu.cn',
					'//google-themes.lug.ustc.edu.cn',
					'//fonts-gstatic.lug.ustc.edu.cn',
				], 
				$html
			);
		}

		$html	= apply_filters('wpjam_html_replace',$html);

		if(is_admin() || empty(CDN_NAME)){
			return $html;
		}

		$html	= wpjam_cdn_replace_local_hosts($html, false);

		$cdn_exts	= wpjam_cdn_get_setting('exts');

		if($cdn_exts){
			if($cdn_dirs = wpjam_cdn_get_setting('dirs')){
				$cdn_dirs	= str_replace(['-','/'],['\-','\/'], $cdn_dirs);

				$regex	=  '/'.str_replace('/','\/',LOCAL_HOST).'\/(('.$cdn_dirs.')\/[^\s\?\\\'\"\;\>\<]{1,}.('.$cdn_exts.'))([\"\\\'\s\]\?]{1})/';
				$html	=  preg_replace($regex, CDN_HOST.'/$1$4', $html);
			}else{
				$regex	= '/'.str_replace('/','\/',LOCAL_HOST).'\/([^\s\?\\\'\"\;\>\<]{1,}.('.$cdn_exts.'))([\"\\\'\s\]\?]{1})/';
				$html	=  preg_replace($regex, CDN_HOST.'/$1$3', $html);
			}
		}	

		return $html;
	});
});

add_filter('wpjam_post_thumbnail_url',	function ($thumbnail_url, $post){
	$thumbnail_orders	= wpjam_cdn_get_setting('post_thumbnail_orders') ?: [];

	if($thumbnail_orders){
		foreach ($thumbnail_orders as $thumbnail_order) {
			if($thumbnail_order['type'] == 'first'){
				if($post_first_image = wpjam_get_post_first_image_url($post)){
					return $post_first_image;
				}
			}elseif($thumbnail_order['type'] == 'post_meta'){
				if($post_meta 	= $thumbnail_order['post_meta']){
					if($post_meta_url = get_post_meta($post->ID, $post_meta, true)){
						return $post_meta_url;
					}
				}
			}elseif($thumbnail_order['type'] == 'term'){
				$term_thumbnail_type	= wpjam_cdn_get_setting('term_thumbnail_type') ?: '';

				if($term_thumbnail_type){
					$taxonomy	= $thumbnail_order['taxonomy'];

					$thumbnail_taxonomies	= $thumbnail_taxonomies ?? wpjam_cdn_get_setting('term_thumbnail_taxonomies');
					$post_taxonomies		= $post_taxonomies ?? get_post_taxonomies($post);

					if($taxonomy && $thumbnail_taxonomies && $post_taxonomies && in_array($taxonomy, $thumbnail_taxonomies) && in_array($taxonomy, $post_taxonomies)){
					
						if($terms = get_the_terms($post, $taxonomy)){
							foreach ($terms as $term) {
								if($term_thumbnail = wpjam_get_term_thumbnail_url($term)){
									return $term_thumbnail;
								}
							}
						}
					}
				}
			}
		}	
	}

	return wpjam_get_default_thumbnail_url();
}, 1, 2);

add_filter('wpjam_term_thumbnail_url', function($thumbnail_url, $term){
	if(wpjam_cdn_get_setting('term_thumbnail_type')){
		$thumbnail_url	= get_term_meta($term->term_id, 'thumbnail', true);
	}

	return $thumbnail_url;
}, 1, 2);

add_filter('wp_update_attachment_metadata', function ($data){
    if(isset($data['thumb'])){
        $data['thumb'] = basename($data['thumb']);
    }
    
    return $data;
});

function wpjam_cdn_replace_local_hosts($html, $to_cdn=true){
	$local_hosts	= wpjam_cdn_get_setting('locals') ?: [];

	if($to_cdn){
		$local_hosts[]	= str_replace('https://', 'http://', LOCAL_HOST);
		$local_hosts[]	= str_replace('http://', 'https://', LOCAL_HOST);
	}else{
		if(strpos(LOCAL_HOST, 'https://') !== false){
			$local_hosts[]	= str_replace('https://', 'http://', LOCAL_HOST);
		}else{
			$local_hosts[]	= str_replace('http://', 'https://', LOCAL_HOST);
		}
	}

	$local_hosts	= apply_filters('wpjam_cdn_local_hosts', $local_hosts);
	$local_hosts	= array_unique($local_hosts);
	$local_hosts	= array_map('untrailingslashit', $local_hosts);	

	if($to_cdn){
		$html	= str_replace($local_hosts, CDN_HOST, $html);
	}else{
		$html	= str_replace($local_hosts, LOCAL_HOST, $html);
	}

	return $html;
}

function wpjam_image_hwstring($size){
	$width	= intval($size['width']);
	$height	= intval($size['height']);
	return image_hwstring($width, $height);
}

function wpjam_parse_size($size, $retina=1){
	global $content_width;	

	$_wp_additional_image_sizes = wp_get_additional_image_sizes();

	if(is_array($size)){
		if(wpjam_is_assoc_array($size)){
			$size['width']	= $size['width'] ?? 0;
			$size['height']	= $size['height'] ?? 0;
			$size['width']	*= $retina;
			$size['height']	*= $retina;
			$size['crop']	= !empty($size['width']) && !empty($size['height']);
			return $size;
		}else{
			$width	= intval($size[0]??0);
			$height	= intval($size[1]??0);
			$crop	= $width && $height;
		}
	}else{
		if(strpos($size, 'x')){
			$size	= explode('x', $size);
			$width	= intval($size[0]);
			$height	= intval($size[1]);
			$crop	= $width && $height;
		}elseif(is_numeric($size)){
			$width	= $size;
			$height	= 0;
			$crop	= false;
		}elseif($size == 'thumb' || $size == 'thumbnail'){
			$width	= intval(get_option('thumbnail_size_w'));
			$height = intval(get_option('thumbnail_size_h'));
			$crop	= get_option('thumbnail_crop');

			if(!$width && !$height){
				$width	= 128;
				$height	= 96;
			}

		}elseif($size == 'medium'){

			$width	= intval(get_option('medium_size_w')) ?: 300;
			$height = intval(get_option('medium_size_h')) ?: 300;
			$crop	= get_option('medium_crop');

		}elseif( $size == 'medium_large' ) {

			$width	= intval(get_option('medium_large_size_w'));
			$height	= intval(get_option('medium_large_size_h'));
			$crop	= get_option('medium_large_crop');

			if(intval($content_width) > 0){
				$width	= min(intval($content_width), $width);
			}

		}elseif($size == 'large'){

			$width	= intval(get_option('large_size_w')) ?: 1024;
			$height	= intval(get_option('large_size_h')) ?: 1024;
			$crop	= get_option('large_crop');

			if (intval($content_width) > 0) {
				$width	= min(intval($content_width), $width);
			}
		}elseif(isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$size])){
			$width	= intval($_wp_additional_image_sizes[$size]['width']);
			$height	= intval($_wp_additional_image_sizes[$size]['height']);
			$crop	= $_wp_additional_image_sizes[$size]['crop'];

			if(intval($content_width) > 0){
				$width	= min(intval($content_width), $width);
			}
		}else{
			$width	= 0;
			$height	= 0;
			$crop	= 0;
		}
	}

	$width	= $width * $retina;
	$height	= $height * $retina;

	return compact('width','height', 'crop');
}

function wpjam_get_content_width(){
	return intval(apply_filters('wpjam_content_image_width', wpjam_cdn_get_setting('width')));
}

function wpjam_content_images($content, $max_width=0, $cache_key=''){
	$content		= wpjam_cdn_replace_local_hosts($content, false);
	$max_width		= $max_width ?: wpjam_get_content_width();
	$content_images	= $cache_key ? wp_cache_get($cache_key, 'wpjam_content_images') : false;

	if($content_images === false){
		if(preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches)){
			$search		= $replace	= $matches[0];
			$img_urls	= $matches[1];

			foreach ($search as $i => $img_tag){
			 	$img_url	= $img_urls[$i];

			 	if(empty($img_url)){
			 		continue;
			 	}

			 	if(strpos($img_url, CDN_HOST) === false && strpos($img_url, LOCAL_HOST) === false){
					if(!wpjam_can_remote_image($img_url)){
						continue;
					}

					$img_url	= wpjam_get_content_remote_image_url($img_url);
				}

				$size	= [
					'width'		=> 0,
					'height'	=> 0,
					'content'	=> true
				];

				preg_match_all('/(width|height)=[\'"]([0-9]+)[\'"]/i', $img_tag, $hw_matches);
				if($hw_matches[0]){
					$hw_arr	= array_flip($hw_matches[1]);
					$size	= array_merge($size, array_combine($hw_matches[1], $hw_matches[2]));
				}

				if($max_width) {
					if($size['width'] >= $max_width){
						if($size['height']){
							$size['height']	= intval(($max_width / $size['width']) * $size['height']);

							$index		= $hw_arr['height'];
							$img_tag	= str_replace($hw_matches[0][$index], 'height="'.$size['height'].'"', $img_tag);
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

				$size['width']	= $size['width']*2;
				$size['height']	= $size['height']*2;
				
				$thumbnail		= wpjam_get_thumbnail($img_url, $size);
				$replace[$i]	= str_replace($img_urls[$i], $thumbnail, $img_tag);
			}
		}else{
			$search	= $replace = [];
		}

		$content_images	= compact('search', 'replace');

		if($cache_key){
			wp_cache_set($cache_key, $content_images, 'wpjam_content_images', HOUR_IN_SECONDS);
		}
	}

	if($content_images['search']){
		$content	= str_replace($content_images['search'], $content_images['replace'], $content);
	}

	return $content;
}

function wpjam_can_remote_image($img_url=''){
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
		$exceptions	= wpjam_cdn_get_setting('exceptions');	// 后台设置不加载的远程图片

		if($exceptions){
			$exceptions	= explode("\n", $exceptions);
			foreach ($exceptions as $exception) {
				if(trim($exception) && strpos($img_url, trim($exception)) !== false ){
					return false;
				}
			}
		}
	}

	return true;
}

// 获取远程图片
function wpjam_get_content_remote_image_url($img_url, $post_id=null){
	if(wpjam_can_remote_image($img_url)){
		$img_type = strtolower(pathinfo($img_url, PATHINFO_EXTENSION));
		if($img_type != 'gif'){
			$img_type	= ($img_type == 'png')?'png':'jpg';
			$post_id	= $post_id ?: get_the_ID();
			$img_url	= CDN_HOST.'/'.CDN_NAME.'/'.$post_id.'/image/'.md5($img_url).'.'.$img_type;
		}
	}
	
	return $img_url;
}

function wpjam_attachment_url_to_postid($url){
	$post_id = wp_cache_get($url, 'attachment_url_to_postid');

	if($post_id === false){
		global $wpdb;

		$upload_dir	= wp_get_upload_dir();
		$path		= str_replace(parse_url($upload_dir['baseurl'], PHP_URL_PATH).'/', '', parse_url($url, PHP_URL_PATH));

		$post_id	= $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s", $path));

		wp_cache_set($url, $post_id, 'attachment_url_to_postid', DAY_IN_SECONDS);
	}

	return (int) apply_filters( 'attachment_url_to_postid', $post_id, $url );
}

function wpjam_urlencode_img_cn_name($img_url){
	return $img_url;
	return str_replace(['%3A','%2F'], [':','/'], urlencode($img_url));
}



// 1. $img_url 简单替换一下 CDN 域名
// 2. $img_url, array('width'=>100, 'height'=>100)	// 这个为最标准版本
// 3. $img_url, 100x100
// 4. $img_url, 100
// 5. $img_url, array(100,100)
// 6. $img_url, array(100,100), $crop=1, $retina=1
// 7. $img_url, 100, 100, $crop=1, $retina=1
function wpjam_get_thumbnail(){
	$args_num	= func_num_args();
	$args		= func_get_args();

	$img_url	= $args[0];

	if(CDN_NAME == ''){
		return $img_url;
	}

	if(strpos($img_url, '?') === false){
		$img_url	= str_replace(['%3A','%2F'], [':','/'], urlencode(urldecode($img_url)));	// 中文名
	}

	$img_url	= wpjam_cdn_replace_local_hosts($img_url);

	if($args_num == 1){	
		// 1. $img_url 简单替换一下 CDN 域名

		$thumb_args = [];
	}elseif($args_num == 2){		
		// 2. $img_url, ['width'=>100, 'height'=>100]	// 这个为最标准版本
		// 3. $img_url, [100,100]
		// 4. $img_url, 100x100
		// 5. $img_url, 100		

		$thumb_args = wpjam_parse_size($args[1]);
	}else{
		if(is_numeric($args[1])){
			// 6. $img_url, 100, 100, $crop=1, $retina=1

			$width	= $args[1] ?? 0;
			$height	= $args[2] ?? 0;
			$crop	= $args[3] ?? 1;
			// $retina	= $args[4] ?? 1;
		}else{
			// 7. $img_url, array(100,100), $crop=1, $retina=1

			$size	= wpjam_parse_size($args[1]);
			$width	= $size['width'];
			$height	= $size['height'];
			$crop	= $args[2]??1;
			// $retina	= $args[3]??1;
		}

		// $width		= intval($width)*$retina;
		// $height		= intval($height)*$retina;

		$thumb_args = compact('width','height','crop');
	}

	return apply_filters('wpjam_thumbnail', $img_url, $thumb_args);
}

/* default thumbnail */
function wpjam_get_default_thumbnail_url($size='full', $crop=1){
	$thumbnail_url	= wpjam_cdn_get_setting('default');
	$thumbnail_url	= apply_filters_deprecated('wpjam_default_thumbnail_uri', [$thumbnail_url], 'WPJAM Basic 3.2','wpjam_default_thumbnail_url');	
	$thumbnail_url	= apply_filters('wpjam_default_thumbnail_url', $thumbnail_url);

	return $thumbnail_url ? wpjam_get_thumbnail($thumbnail_url, $size, $crop) : '';
}