<?php
/*
Plugin Name: Rewrite 优化
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 清理无用的 Rewrite 代码，和添加自定义 rewrite 代码。 
Version: 1.0
*/

add_action('generate_rewrite_rules', function ($wp_rewrite){
	$wp_rewrite->rules				= wpjam_remove_rewrite_rules($wp_rewrite->rules); 
	$wp_rewrite->extra_rules_top	= wpjam_remove_rewrite_rules($wp_rewrite->extra_rules_top);

	// wpjam_var_dump(wpjam_basic_get_setting('rewrites'));

	if($wpjam_rewrites	= wpjam_basic_get_setting('rewrites')){
		$wpjam_rewrites		= wp_list_pluck($wpjam_rewrites, 'query', 'regex');
		$wp_rewrite->rules	= array_merge($wpjam_rewrites, $wp_rewrite->rules);
	}

	// wpjam_print_r($wp_rewrite);

});

add_action('init',function(){
	if($wpjam_rewrites	= wpjam_basic_get_setting('rewrites')){
		foreach ($wpjam_rewrites as $wpjam_rewrite) {
			if($wpjam_rewrite['regex'] && $wpjam_rewrite['query']){
				add_rewrite_rule($wpjam_rewrite['regex'], $wpjam_rewrite['query'], 'top');
			}
		}
	}
});


function wpjam_remove_rewrite_rules($rules){

	$unuse_rewrite_keys = ['comment-page','comment','author','type/','feed=','attachment'];

	foreach ($unuse_rewrite_keys as $i=>$unuse_rewrite_key) {
		if(wpjam_basic_get_setting('remove_'.$unuse_rewrite_key.'_rewrite') == false){
			unset($unuse_rewrite_keys[$i]);
		}
	}
	
	foreach ($rules as $key => $rule) {
		if($unuse_rewrite_keys){
			foreach ($unuse_rewrite_keys as $unuse_rewrite_key) {
				if( strpos($key, $unuse_rewrite_key) !== false || strpos($rule, $unuse_rewrite_key) !== false){
					if($rule != 'index.php?&feed=$matches[1]'){
						unset($rules[$key]);
					}
				}
			}
		}

		if(wpjam_basic_get_setting('disable_post_embed')){
			if( strpos($rule, 'embed=true') !== false){
				unset($rules[$key]);
			}
		}

		if(wpjam_basic_get_setting('disable_trackbacks')){
			if( strpos($rule, 'tb=1') !== false){
				unset($rules[$key]);
			}
		}
	}

	return $rules;
}