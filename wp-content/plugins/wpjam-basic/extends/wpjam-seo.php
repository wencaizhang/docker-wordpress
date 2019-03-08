<?php
/*
Plugin Name: 简单 SEO
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 设置简单快捷，功能强大的 WordPress SEO 功能。
Version: 1.0
*/

add_action('wp_head', function (){
	global $paged;

	if(is_singular()){
		if(wpjam_basic_get_setting('seo_individual')){
			$post_id = get_the_ID();
			if(!$meta_description = addslashes_gpc(get_post_meta($post_id,'seo_description',true))){
				$meta_description = addslashes_gpc(get_post_excerpt());
			}
			
			if(!$meta_keywords = addslashes_gpc(get_post_meta($post_id,'seo_keywords',true))){
				$meta_keywords = array();
				if($tags = get_the_tags($post_id)){
					foreach ($tags as $tag ) {
			        	$meta_keywords[] = $tag->name;
			    	}
			    	if($meta_keywords){
			    		$meta_keywords = implode(',', $meta_keywords);
			    	}
				}
			}
		}else{
			$meta_description = addslashes_gpc(get_post_excerpt());
		}
	}elseif($paged<2){
		if(is_home() || is_front_page()) {
			$meta_description	= wpjam_basic_get_setting('seo_home_description');
			$meta_keywords		= wpjam_basic_get_setting('seo_home_keywords');
			$module = get_query_var('module');
			if(empty($module)){
				$canonical_link 	= home_url();
			}
		}elseif(is_tag() || is_category() || is_tax()){
			if(wpjam_basic_get_setting('seo_individual')){
				$term_id		= get_queried_object_id();
				$meta_keywords	= addslashes_gpc(get_term_meta($term_id,'seo_keywords',true));
				if(!$meta_description = addslashes_gpc(get_term_meta($term_id,'seo_description',true))){
					$meta_description = wpjam_get_plain_text(term_description());
				}
			}else{
				$meta_description = wpjam_get_plain_text(term_description());
			}
		}elseif(is_post_type_archive()){
			$post_type = get_queried_object();
			//var_dump($post_type);
			//$post_type = get_post_type_object( get_query_var( 'post_type' ) );
			if($post_type){
				if(!$meta_description = wpjam_basic_get_setting('seo_'.$post_type->name.'_description')){
					$meta_description = $post_type->description;
				}
				$meta_keywords = wpjam_basic_get_setting('seo_'.$post_type->name.'_keywords');
			}
	    }
	}

	if(is_singular() || is_home() || is_tag() || is_category() || is_tax() || is_post_type_archive()){
		$meta_robots = "index,follow";
	}elseif(is_404() || is_search()){
		$meta_robots = "noindex,noarchive";
	}elseif(is_archive()){
		$meta_robots = "noarchive";
	}

	if ( !empty( $meta_description )){
		echo "<meta name='description' content='{$meta_description }' />\n";
	}
	if ( !empty( $meta_keywords )){
		echo "<meta name='keywords' content='{$meta_keywords }' />\n";
	}
	if ( !empty( $meta_robots ) ){
		echo "<meta name='robots' content='{$meta_robots}' />\n";
	}
	if ( !empty( $canonical_link ) ){
		echo "<link rel='canonical' href='{$canonical_link}' />\n";
	}
});

add_filter('pre_get_document_title', function ($title){
	global $paged;
	
	if(is_singular()){
		if(wpjam_basic_get_setting('seo_individual')){
			if($seo_title = get_post_meta(get_the_ID(),'seo_title',true)){
				return $seo_title;
			}
		}
	}elseif($paged<2){
		if(is_home()){
			if(wpjam_basic_get_setting('seo_home_title')){
				return wpjam_basic_get_setting('seo_home_title');
			}
		}elseif(is_tag() || is_category() || is_tax()){
			if(wpjam_basic_get_setting('seo_individual')){
				$term_id	= get_queried_object_id();
				if($seo_title	= get_term_meta($term_id,'seo_title',true)){
					return $seo_title;
				}
			}
		}elseif(is_post_type_archive()){
			$post_type = get_queried_object();
			if(wpjam_basic_get_setting('seo_'.$post_type->name.'_title')){
				return wpjam_basic_get_setting('seo_'.$post_type->name.'_title');
			}
		}
	}
	return $title;
},99);


add_filter('robots_txt', function ($output, $public){
	if ( '0' == $public ) {
		return "Disallow: /\n";
	} else {
		return wpjam_basic_get_setting('seo_robots');
	}
},10,2);

add_action('init',function(){
	global $wp_rewrite;

	add_rewrite_rule($wp_rewrite->root.'sitemap\.xml?$', 'index.php?module=sitemap', 'top');
	add_rewrite_rule($wp_rewrite->root.'sitemap-(.*?)\.xml?$', 'index.php?module=sitemap&action=$matches[1]', 'top');
});

// add_filter('wpjam_rewrite_rules', function ($wpjam_rules){
// 	global $wp_rewrite;

// 	$wpjam_rules[$wp_rewrite->root .'sitemap\.xml$']		= 'index.php?module=sitemap';
// 	$wpjam_rules[$wp_rewrite->root .'sitemap-(.*?)\.xml$']	= 'index.php?module=sitemap&action=$matches[1]';
// 	return $wpjam_rules;
// });


add_filter('wpjam_template', function ($wpjam_template, $module, $action){
	if($module == 'sitemap'){
		return WPJAM_BASIC_PLUGIN_DIR.'template/sitemap.php';
	}
	return $wpjam_template;
}, 10, 3);
