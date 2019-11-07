<?php
add_filter('wpjam_basic_sub_pages',function($subs){
	$subs['wpjam-seo']	= [
		'menu_title'	=>'SEO设置',
		'function'		=>'option', 
		'option_name'	=>'wpjam-basic', 
		'page_file'		=>WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-seo.php'
	];

	return $subs;
});

if(wpjam_basic_get_setting('seo_individual')){

	function wpjam_post_seo_setting($post_type){
		$seo_post_types	= wpjam_basic_get_setting('seo_post_types') ?? ['post'];

		if($seo_post_types  && in_array($post_type, $seo_post_types)){
			include WPJAM_BASIC_PLUGIN_DIR.'extends/admin/posts/seo-post.php';
		}
	}

	function wpjam_term_seo_setting($taxonomy){
		$seo_taxonomies	= wpjam_basic_get_setting('seo_taxonomies') ?? ['category'];

		if(!$seo_taxonomies || !in_array($taxonomy, $seo_taxonomies)){
			return;
		}

		include WPJAM_BASIC_PLUGIN_DIR.'extends/admin/posts/seo-term.php';
	}

	add_action('wpjam_post_page_file', 		'wpjam_post_seo_setting');
	add_action('wpjam_post_list_page_file', 'wpjam_post_seo_setting');
	add_action('wpjam_term_list_page_file', 'wpjam_term_seo_setting');
}

add_action('blog_privacy_selector', function(){
	?>
	<style type="text/css">tr.option-site-visibility{display: none;}</style>
	<?php
});

