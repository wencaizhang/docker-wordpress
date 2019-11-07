<?php
/*
Plugin Name: 简单 SEO
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 设置简单快捷，功能强大的 WordPress SEO 功能。
Version: 1.0
*/
remove_action( 'wp_head', 'noindex', 1 );

add_action('wp_head', function (){
	global $paged;

	$meta_keywords	= $meta_description	= '';

	$seo_individual	= wpjam_basic_get_setting('seo_individual');

	if(is_singular()){
		$post_id = get_the_ID();

		if($seo_individual){
			$seo_post_types	= wpjam_basic_get_setting('seo_post_types') ?? ['post'];

			if($seo_post_types && in_array(get_post_type(), $seo_post_types)){
				if($seo_description = get_post_meta($post_id, 'seo_description', true)){
					$meta_description = $seo_description;
				}

				if($seo_keywords = get_post_meta($post_id, 'seo_keywords', true)){
					$meta_keywords	= $seo_keywords;
				}
			}	
		}

		if(empty($meta_description)){
			$meta_description	= get_the_excerpt();
		}

		if(empty($meta_keywords)){
			if($tags = get_the_tags($post_id)){
				$meta_keywords = implode(',', wp_list_pluck($tags, 'name'));
			}
		}
	}elseif($paged<2){
		if(is_home() || is_front_page()){
			$meta_description	= wpjam_basic_get_setting('seo_home_description') ?: '';
			$meta_keywords		= wpjam_basic_get_setting('seo_home_keywords') ?: '';
		}elseif(is_tag() || is_category() || is_tax()){
			if($seo_individual){
				$seo_taxonomies	= wpjam_basic_get_setting('seo_taxonomies') ?? ['category'];

				if($seo_taxonomies && in_array(get_queried_object()->taxonomy, $seo_taxonomies)){
					$term_id	= get_queried_object_id();

					if($seo_description	= get_term_meta($term_id, 'seo_description', true)){
						$meta_description = $seo_description;
					}

					if($seo_keywords = get_term_meta($term_id, 'seo_keywords', true)){
						$meta_keywords	= $seo_keywords;
					}
				}
			}

			if(empty($meta_description) && term_description()){
				$meta_description	= term_description();
			}
		}elseif(is_post_type_archive()){
			// $post_type_obj = get_queried_object();
			
			// if(!$meta_description = wpjam_basic_get_setting('seo_'.$post_type->name.'_description')){
			// 	$meta_description = $post_type->description;
			// }
			// $meta_keywords = wpjam_basic_get_setting('seo_'.$post_type->name.'_keywords');
	    }
	}

	if($meta_description){
		$meta_description	= addslashes_gpc(wpjam_get_plain_text($meta_description));
		echo "<meta name='description' content='{$meta_description}' />\n";
	}

	if($meta_keywords){
		$meta_keywords	= addslashes_gpc(wpjam_get_plain_text($meta_keywords));
		echo "<meta name='keywords' content='{$meta_keywords}' />\n";
	}
});

add_filter('pre_get_document_title', function ($title){
	global $paged;

	$seo_individual	= wpjam_basic_get_setting('seo_individual');
	
	if(is_singular()){
		if($seo_individual){
			$seo_post_types	= wpjam_basic_get_setting('seo_post_types') ?? ['post'];

			if($seo_post_types && in_array(get_post_type(), $seo_post_types)){
				if($seo_title = get_post_meta(get_the_ID(), 'seo_title', true)){
					return $seo_title;
				}
			}
		}
	}elseif($paged<2){
		if(is_home() || is_front_page()){
			if($seo_title = wpjam_basic_get_setting('seo_home_title')){
				return $seo_title;
			}
		}elseif(is_tag() || is_category() || is_tax()){
			if($seo_individual){
				$seo_taxonomies	= wpjam_basic_get_setting('seo_taxonomies') ?? ['category'];

				if($seo_taxonomies && in_array(get_queried_object()->taxonomy, $seo_taxonomies)){
					if($seo_title	= get_term_meta(get_queried_object_id(), 'seo_title', true)){
						return $seo_title;
					}
				}
			}
		}elseif(is_post_type_archive()){
			// $post_type = get_queried_object();
			// if(wpjam_basic_get_setting('seo_'.$post_type->name.'_title')){
			// 	return wpjam_basic_get_setting('seo_'.$post_type->name.'_title');
			// }
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
