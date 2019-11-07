<?php
add_filter('wpjam_basic_sub_pages','wpjam_basic_add_toc_sub_page');
function wpjam_basic_add_toc_sub_page($subs){
	$subs['wpjam-toc']	= [
		'menu_title'	=> '文章目录',	
		'function'		=> 'option',
		'option_name'	=> 'wpjam-basic',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'extends/admin/pages/wpjam-toc.php'
	];
	
	return $subs;
}


add_filter('wpjam_post_options',function ($post_options){
	if(wpjam_basic_get_setting('toc_individual')){
		$post_options['wpjam-toc'] = [
			'title'		=> '文章目录',
			'context'	=> 'side',
			'fields'	=> [
				'toc_hidden'	=> ['title'=>'',		'type'=>'checkbox',	'description'=>'隐藏文章目录'],
				'toc_depth'		=> ['title'=>'显示到：',	'type'=>'select',	'options'=>['1'=>'h1','2'=>'h2','3'=>'h3','4'=>'h4','5'=>'h5','6'=>'h6']]
			]
		];
	}

	return $post_options;
});

add_action('save_post', function($post_id){
	wp_cache_delete($post_id,'wpjam-toc');
});