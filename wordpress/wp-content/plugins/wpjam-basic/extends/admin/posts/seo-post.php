<?php
add_filter('wpjam_post_options',function($post_options){
	$post_options['wpjam-seo'] = [
		'title'		=> 'SEO设置',
		'fields'	=> [
			'seo_title'			=> ['title'=>'标题', 	'type'=>'text',		'placeholder'=>'不填则使用文章标题'],
			'seo_description'	=> ['title'=>'描述', 	'type'=>'textarea'],
			'seo_keywords'		=> ['title'=>'关键字',	'type'=>'text']
		]
	];
	return $post_options;
});

add_action('wpjam_'.$post_type.'_posts_actions', function($actions){
	$actions['seo']	= ['title'=>'SEO设置', 'page_title'=>'SEO设置',	'submit_text'=>'设置'];
	return $actions;
});

add_filter('wpjam_'.$post_type.'_posts_fields', function($fields, $action_key, $post_id){

	if($action_key == 'seo'){
		return [
			'title'				=> ['title'=>'文章标题',		'type'=>'view',		'value'=>get_post($post_id)->post_title],
			'seo_title'			=> ['title'=>'SEO 标题',		'type'=>'text',		'value'=>get_post_meta($post_id, 'seo_title', true),	'placeholder'=>'不填则使用文章标题'],
			'seo_description'	=> ['title'=>'SEO 描述', 	'type'=>'textarea',	'value'=>get_post_meta($post_id, 'seo_description', true)],
			'seo_keywords'		=> ['title'=>'SEO 关键字',	'type'=>'text',		'value'=>get_post_meta($post_id, 'seo_keywords', true)]
		];
	}

	return $fields;
}, 10, 3);

add_filter('wpjam_'.$post_type.'_posts_list_action', function($result, $list_action, $post_id, $data){
	if($list_action == 'seo'){

		foreach(['seo_title', 'seo_description', 'seo_keywords'] as $meta_key){
			$meta_value	= $data[$meta_key] ?? '';
			if($meta_value){
				update_post_meta($post_id, $meta_key, $meta_value);
			}else{
				delete_post_meta($post_id, $meta_key);
			}
		}

		return true;
	}

	return $result;
}, 10, 4);