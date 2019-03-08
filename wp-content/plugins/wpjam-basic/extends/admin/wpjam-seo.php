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

add_filter('wpjam_post_options',function($post_options){
	global $pagenow, $post_type;

	if(wpjam_basic_get_setting('seo_individual') ){
		$post_type_object = get_post_type_object($post_type);
		if(!empty($post_type_object->seo_meta_box) || $post_type == 'post'){

			$post_options['wpjam-seo'] = array(
				'title'			=> 'SEO设置',
				'fields'		=> array(
					'seo_title'			=> array('title'=>'标题', 	'type'=>'text'),
					'seo_description'	=> array('title'=>'描述', 	'type'=>'textarea'),
					'seo_keywords'		=> array('title'=>'关键字',	'type'=>'text')
				)
			);
		}
	}
	return $post_options;
});

add_filter('wpjam_term_options', function($term_options){
	if(wpjam_basic_get_setting('seo_individual') ){
		$seo_taxonomies	= array();
		$taxonomies		= get_taxonomies(array('public' => true)); 
		foreach ($taxonomies as $taxonomy) {
			$taxonomy_object = get_taxonomy( $taxonomy );
			if(!empty($taxonomy_object->seo_meta_box) || $taxonomy == 'tag' || $taxonomy == 'category'){
				$seo_taxonomies[] = $taxonomy;
			}
		}

		if($seo_taxonomies){
			$term_options['seo_title'] 			= array('title'=>'SEO 标题',		'taxonomies'=>$seo_taxonomies,	'type'=>'text');
			$term_options['seo_description']	= array('title'=>'SEO 描述',		'taxonomies'=>$seo_taxonomies, 	'type'=>'textarea');
			$term_options['seo_keywords']		= array('title'=>'SEO 关键字',	'taxonomies'=>$seo_taxonomies, 	'type'=>'text');
		}
	}
	return $term_options;
});





