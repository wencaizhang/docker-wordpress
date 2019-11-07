<?php
add_filter('wpjam_basic_setting', function(){

	$post_type_options	= wp_list_pluck(get_post_types(['show_ui'=>true,'public'=>true], 'objects'), 'label', 'name');

	unset($post_type_options['attachment']);

	$fields	= [
		'series_post_types'	=>['title'=>'支持的文章类型',	'type'=>'checkbox',	'options'=>$post_type_options,	'value'=>['post']],
	];

	return compact('fields');	
});

add_action('updated_option', function($option){
	if($option == 'wpjam-basic'){
		flush_rewrite_rules();
	}
});

add_action('added_option', function($option){
	if($option == 'wpjam-basic'){
		flush_rewrite_rules();
	}
});
