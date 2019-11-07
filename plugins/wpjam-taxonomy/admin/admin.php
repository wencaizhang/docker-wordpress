<?php
add_filter('wpjam_basic_sub_pages', function($subs){
	if(!is_multisite() || !is_network_admin()){
		$subs['wpjam-taxonomy']	=[
			'menu_title'	=> '分类设置', 
			'function'		=> 'option',
			'option_name'	=> 'wpjam_taxonomy_args',
			'page_file'		=> WPJAM_TAXONOMY_PLUGIN_DIR.'admin/taxonomy-args.php'
		];
	}

	return $subs;
});


add_action('wpjam_term_list_page_file', function($taxonomy){
	if(is_taxonomy_hierarchical($taxonomy)){
		require WPJAM_TAXONOMY_PLUGIN_DIR .'admin/term-list.php';	
	}
});

// add_filter('rest_prepare_taxonomy', function($response, $taxonomy, $request){

// 	// wpjam_print_R($taxonomy);
// 	// wpjam_print_R($request);
// 	// wpjam_print_R($response);

// 	trigger_error(var_export($request, true));

// 	return $response;
// },10,3);

// add_filter('rest_pre_dispatch', function($result, $aa, $request){

// 	// wpjam_print_R($taxonomy);
// 	// wpjam_print_R($request);
// 	// wpjam_print_R($response);

// 	// trigger_error('123');
// 	trigger_error(var_export($request, true));

// 	return $result;
// },10,3);

// add_filter('block_editor_preload_paths', function($preload_paths, $post){

// 	wpjam_print_R($preload_paths);
// 	// wpjam_print_R($post);

// 	return $preload_paths;
// },10,2);


