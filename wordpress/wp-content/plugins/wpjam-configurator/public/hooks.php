<?php
add_filter('alloptions', function($alloptions){
	foreach(wpjam_get_configurator_options() as $option_name){
		if(!isset($alloptions[$option_name])){
			$alloptions[$option_name]	= [];
		}
	}
	
	return $alloptions;
});

foreach(wpjam_get_configurator_options() as $option_name){
	add_filter('default_option_'.$option_name, '__return_empty_array');
}

add_filter('wpjam_post_types', function($wpjam_post_types){
	if($post_type_settings	= get_option('wpjam_post_types')){
		if(isset($post_type_settings['post'])){
			unset($post_type_settings['post']);
		}

		if(isset($post_type_settings['page'])){
			unset($post_type_settings['page']);
		}

		return array_merge($post_type_settings, $wpjam_post_types);
	}

	return $wpjam_post_types;
});

add_filter('wpjam_post_options', function($wpjam_post_options, $post_type){
	if($post_options_settings = get_option('wpjam_post_options')){
		foreach ($post_options_settings as $meta_box => $args) {
			if($meta_box && empty($wpjam_post_options[$meta_box]) && $args['post_types'] && in_array($post_type, $args['post_types'])) {
				$args['fields']		= wpjam_parse_fields_setting($args['fields']);
				$wpjam_post_options[$meta_box]	= $args;
			}			
		}
	}

	return $wpjam_post_options;
},10,2);

add_filter('wpjam_taxonomies', function($wpjam_taxonomies){

	if($taxonomy_settings = get_option('wpjam_taxonomies')){
		$taxonomy_settings	= array_map(function($args){ 
			return ['object_type'=>$args['object_type'], 'args'=>$args]; 
		}, $taxonomy_settings);

		return array_merge($taxonomy_settings, $wpjam_taxonomies);
	}

	return $wpjam_taxonomies;
});

add_filter('wpjam_term_options', function($wpjam_term_options, $taxonomy){
	if($term_options_settings = get_option('wpjam_term_options')){
		foreach($term_options_settings as $field_key => $args){
			if($field_key && empty($wpjam_term_options[$field_key]) && $args['taxonomies'] && in_array($taxonomy, array_values($args['taxonomies']))){
				$field	= wpjam_parse_fields_setting($args['field']);
				$field['taxonomies']	= array_values($args['taxonomies']);

				$wpjam_term_options[$field_key]	= $field;
			}	
		}
	}

	return $wpjam_term_options;
}, 10, 2);

add_filter('wpjam_settings', function($wpjam_settings, $option_name){
	if(empty($wpjam_settings[$option_name])){
		$option_settings	= get_option('wpjam_settings');

		if($option_settings && !empty($option_settings[$option_name])){
			$option_setting	= $option_settings[$option_name];

			$option_setting['fields']		= wpjam_parse_fields_setting($option_setting['fields']);

			$wpjam_settings[$option_name]	= $option_setting;
		}
	}

	return $wpjam_settings;
}, 10, 2);

add_filter('wpjam_apis', function($wpjam_apis, $json){
	if(empty($wpjam_apis[$json])){

		$api_settings	= get_option('wpjam_apis');

		if($api_settings && !empty($api_settings[$json])){
			$wpjam_apis[$json]	= $api_settings[$json];
		}
	}

	return $wpjam_apis;
}, 10, 2);

add_filter('wpjam_post_json', function($post_json, $post_id){
	$post_type	= $post_json['post_type'];
	
	if($post_fields = wpjam_get_post_fields($post_type)){
		foreach ($post_fields  as $field_key => $post_field) {
			$post_field['key']	= $field_key;

			$field_type		= $post_field['type']??'';
			
			if($field_type == 'fieldset'){
				if(empty($post_field['fields'])){
					continue;
				}
					
				$fieldset_type	= $post_field['fieldset_type'] ?? 'single';

				if($fieldset_type == 'single'){
					foreach ($post_field['fields'] as $sub_key => $sub_field) {
						$sub_field['key']	= $sub_key;
						$value	= wpjam_get_field_value($sub_field, ['data_type'=>'post_meta', 'id'=>$post_id]);
						$post_json[$sub_key]	= wpjam_parse_field_value($value, $sub_field);	
					}

					continue;
				}
			}

			$value	= wpjam_get_field_value($post_field, ['data_type'=>'post_meta', 'id'=>$post_id]);
			$post_json[$field_key]	= wpjam_parse_field_value($value, $post_field);	

			if(!empty($post_field['data_type'])){
				if($post_field['data_type'] == 'vote'){
					$post_json['__vote_field']	= $field_key;
				}
			}
		}
	}

	return $post_json;
}, 10, 2);

add_filter('wpjam_term_json', function($term_json, $term_id){
	$taxonomy	= $term_json['taxonomy'];

	if($term_fields = wpjam_get_term_options($taxonomy)){
		foreach ($term_fields as $field_key => $term_field) {
			$term_field['key']	= $field_key;
			$field_type			= $term_field['type']??'';
			
			if($field_type == 'fieldset'){
				if(empty($post_field['fields'])){
					continue;
				}
				
				$fieldset_type	= $term_field['fieldset_type'] ?? 'single';

				if($fieldset_type == 'single'){
					foreach ($term_field['fields'] as $sub_key => $sub_field) {
						$sub_field['key']	= $sub_key;
						$value	= wpjam_get_field_value($sub_field, ['data_type'=>'term_meta', 'id'=>$post_id]);
						$term_json[$sub_key]	= wpjam_parse_field_value($value, $sub_field);
					}
				
					continue;
				}
			}

			$value	= wpjam_get_field_value($term_field, ['data_type'=>'term_meta', 'id'=>$term_id]);
				
			$term_json[$field_key]	= wpjam_parse_field_value($value, $term_field);
		}
	}

	return $term_json;
},10,2);

add_filter('wpjam_setting_value', function($setting_value, $setting_name, $option_name){
	$option_setting	= wpjam_get_option_setting($option_name);

	if(empty($option_setting)){
		return new WP_Error('option_not_exists', $option_name.'设置不存在');
	}

	$option_fields	= current($option_setting['sections'])['fields'];

	if(!isset($option_fields[$setting_name])){
		return new WP_Error('setting_not_exists', $setting_name.'设置不存在');
	}

	$setting_field	= $option_fields[$setting_name];

	return wpjam_parse_field_value($setting_value, $setting_field);
}, 10, 3);

add_filter('wpjam_option_value', function($option_value, $option_name){
	$option_setting	= wpjam_get_option_setting($option_name);

	if(empty($option_setting)){
		return new WP_Error('option_not_exists', $option_name.'设置不存在');
	}

	$option_fields	= current($option_setting['sections'])['fields'];

	if($option_fields){
		foreach ($option_fields as $setting_name => $setting_field) {
			$setting_value	= ($option_value && isset($option_value[$setting_name])) ? $option_value[$setting_name] : null;

			$option_value[$setting_name]	= wpjam_parse_field_value($setting_value, $setting_field);
		}
	}

	return $option_value;

}, 10, 2);