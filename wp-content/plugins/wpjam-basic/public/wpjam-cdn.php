<?php
function wpjam_cdn_get_setting($setting_name){
	return wpjam_get_setting('wpjam-cdn', $setting_name);
}

add_action('wp_loaded', function(){	// HTML 替换，镜像 CDN 主函数
	// 定义CDN和本地域名网址
	define('LOCAL_HOST',	untrailingslashit(wpjam_cdn_get_setting('local') ?: get_option('home')));
	define('CDN_HOST',		untrailingslashit(wpjam_cdn_get_setting('host') ?: get_option('home')));
	define('CDN_NAME',		wpjam_cdn_get_setting('cdn_name') ?: '');	// CDN 名称

	if(CDN_NAME){
		// add_filter('wp_get_attachment_image_src', function($image){
		// 	$image[0]	= wpjam_get_thumbnail($image[0]);
		// 	return $image;
		// });

		add_filter('wp_get_attachment_url', function($url){
			return $url ? wpjam_get_thumbnail($url) : $url;
		});

		add_filter('wpjam_thumbnail', function($img_url, $thumb_args){
			return wpjam_cdn_replace_local_hosts($img_url);
		}, 10, 2);

		$cdn_extend = apply_filters('wpjam_cdn_extend', WPJAM_BASIC_PLUGIN_DIR.'extends/cdn/'.CDN_NAME.'.php', CDN_NAME);

		if(file_exists($cdn_extend)){
			include($cdn_extend);
		}

		remove_filter('the_content', 'wp_make_content_images_responsive');

		add_filter('the_content', function($content){
			$content = wpjam_cdn_replace_local_hosts($content,false);
			return WPJAM_Thumbnail::content_images($content);
		}, 1);

		add_filter('wp_resource_hints', function($urls, $relation_type){
			if($relation_type == 'dns-prefetch'){
				$urls[]	= CDN_HOST;
			}
			return $urls;
		}, 10, 2);
	
		if(WPJAM_Thumbnail::can_remote_image()){
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
		$html = apply_filters('wpjam_html_replace',$html);

		if(is_admin() || empty(CDN_NAME)){
			return $html;
		}

		$html		= wpjam_cdn_replace_local_hosts($html, false);

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

// 很多客户端不支持中文图片名
// add_filter('wp_get_attachment_url', function($url){
// 	return is_admin() ? $url : wpjam_urlencode_img_cn_name($url);
// });

add_filter('wp_update_attachment_metadata', function ($data){
    if(isset($data['thumb'])){
        $data['thumb'] = basename($data['thumb']);
    }

    return $data;
});

function wpjam_cdn_replace_local_hosts($html, $to_cdn=true){
	$local_hosts	= wpjam_cdn_get_setting('locals') ?: [];

	if($local_hosts){
		$local_hosts	= array_map('untrailingslashit', $local_hosts);	
	}

	if(strpos('https://', LOCAL_HOST) !== false){
		$local_hosts[]	= str_replace('https://', 'http://', LOCAL_HOST);
	}else{
		$local_hosts[]	= str_replace('http://', 'https://', LOCAL_HOST);
	}

	$local_hosts	= apply_filters('wpjam_cdn_local_hosts', $local_hosts);

	if($to_cdn){
		$local_hosts[]	= LOCAL_HOST;
		$html	= str_replace($local_hosts, CDN_HOST, $html);
	}else{
		$html	= str_replace($local_hosts, LOCAL_HOST, $html);
	}

	return $html;
}

function wpjam_image_hwstring($size, $retina=false){
	$size	= wpjam_parse_size($size);
	$width	= $retina ? intval($size['width']/2) : $size['width'];
	$height	= $retina ? intval($size['height']/2) : $size['height'];
	return image_hwstring($width, $height);
}

function wpjam_parse_size($size){
	return WPJAM_Thumbnail::parse_size($size);
}

function wpjam_content_images($content, $max_width=750){
	$content = wpjam_cdn_replace_local_hosts($content, false);
	return WPJAM_Thumbnail::content_images($content, $max_width);
}

// 获取远程图片
function wpjam_get_content_remote_img_url($img_url, $post_id=0){
	return WPJAM_Thumbnail::get_content_remote_img_url($img_url, $post_id);
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
	return call_user_func_array(['WPJAM_Thumbnail','get_thumbnail'], func_get_args());
}

/* default thumbnail */
function wpjam_get_default_thumbnail_url($size, $crop=1, $retina=1){
	return WPJAM_Thumbnail::get_default_thumbnail($size);
}

/* post thumbnail */
function wpjam_has_post_thumbnail(){
	return wpjam_get_post_thumbnail_url() ? true : false;
}

function wpjam_post_thumbnail($size='thumbnail', $crop=1, $class='wp-post-image', $retina=2){
	echo wpjam_get_post_thumbnail(null, $size, $crop, $class, $retina);
}

function wpjam_get_post_thumbnail($post=null, $size='thumbnail', $crop=1, $class='wp-post-image', $retina=2){
	if($post_thumbnail_url = wpjam_get_post_thumbnail_url($post, $size, $crop, $retina)){
		return '<img src="'.$post_thumbnail_url.'" alt="'.the_title_attribute(['echo'=>false]).'" class="'.$class.'"'.wpjam_image_hwstring($size).' />';
	}else{
		return '';
	}
}

function wpjam_get_post_thumbnail_url($post=null, $size='full', $crop=1, $retina=1){
	return WPJAM_Thumbnail::get_post_thumbnail($post, $size, $crop, $retina);
}

function wpjam_get_post_first_image($post_content='', $size='full'){
	return WPJAM_Thumbnail::get_post_first_image($post_content, $size);
}

/* term thumbnail */
function wpjam_has_term_thumbnail(){
	return wpjam_get_term_thumbnail_url()? true : false;
}

function wpjam_term_thumbnail($size='thumbnail', $crop=1, $class="wp-term-image", $retina=2){
	echo  wpjam_get_term_thumbnail(null, $size, $crop, $class);
}

function wpjam_get_term_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-term-image", $retina=2){
	if($term_thumbnail_url = wpjam_get_term_thumbnail_url($term, $size, $crop, $retina)){
		return  '<img src="'.$term_thumbnail_url.'" class="'.$class.'"'.wpjam_image_hwstring($size).' />';
	}else{
		return '';
	}
}

function wpjam_get_term_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
	return WPJAM_Thumbnail::get_term_thumbnail_url($term, $size, $crop, $retina);
}

/* tag thumbnail */
function wpjam_has_tag_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_tag_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);
}

function wpjam_get_tag_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-tag-image", $retina=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $retina);
}

function wpjam_tag_thumbnail($size='thumbnail', $crop=1, $class="wp-tag-image", $retina=2){
	wpjam_term_thumbnail($size, $crop, $class, $retina);
}

/* category thumbnail */
function wpjam_has_category_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_category_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);
}

function wpjam_get_category_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-category-image", $retina=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $retina);
}

function wpjam_category_thumbnail($size='thumbnail', $crop=1, $class="wp-category-image", $retina=2){
	wpjam_term_thumbnail($size, $crop, $class, $retina);
}

// add_filter('pre_option_wpjam-cdn', function($value){
// 	if($old_value = get_option('wpjam-qiniutek')){
// 		$old_value['cdn_name']	= 'qiniu';
// 		$old_value['local']		= $old_value['local'] ?: get_option('home');
// 		return $old_value;
// 	}else{
// 		return $value;
// 	}
// });

// add_filter('option_wpjam-cdn', function($value){
// 	if(is_multisite()){
// 		return wp_parse_args($value, get_site_option('wpjam-cdn')?:[]);
// 	}else{
// 		return $value;
// 	}
// });