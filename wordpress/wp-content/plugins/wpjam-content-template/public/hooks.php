<?php
add_filter('the_content', function($content) {
	$post_id	= get_the_ID();

	if(doing_filter('get_the_excerpt') || !is_singular() || $post_id != get_queried_object_id()){ 
		return $content;
	}

	$post_type	= get_post_type();

	foreach (['top', 'bottom'] as $position) {
		$template	= wpjam_get_setting('wpjam-content-template', $post_type.'_'.$position);

		if($template){
			if($position == 'top'){
				$template	= ($template && is_numeric($template)) ? $template : 0;
				$content	= '[template id="'.$template.'"]'."\n\n".$content;
			}else{
				foreach ($template as $t) {
					$t	= ($t && is_numeric($t)) ? $t : 0;
					if($t){
						$content	= $content."\n\n".'[template id="'.$t.'"]';
					}
				}
			}
		}
	}

	return $content;
}, 1);

add_shortcode('template',  function($atts, $text=''){
	extract(shortcode_atts([
		'id'	=> 0,
		'class'	=> '',
	], $atts));

	if(empty($id) || !is_singular() || get_the_ID() != get_queried_object_id()){
		return '';
	}

	return wpjam_get_content_template($id, $class, $text);
});

add_action('wpjam_api_template_redirect', function ($json){
	wpjam_register_api('template.get', [
		'json'		=> 'template.get',
		'auth'		=> false,
		'title'		=> '内容模板详情',
		'modules'	=> [
			[
				'type'	=> 'post_type',
				'args'	=> '[module post_type="template" action="get" output="template"]'
			]
		]
	]);
});

add_filter('wpjam_post_json', function($post_json, $post_id){
	if($post_json['post_type']	== 'template'){
		$post_json['template_type']	= $template_type = get_post_meta($post_id, '_template_type', true);

		if($template_type == 'table'){
			$post_content	= get_post($post_id)->post_content;
			$table_content	= $post_content ? maybe_unserialize($post_content) : [];
			$table_fields	= get_post_meta($post_id, '_table_fields', true);

			if($table_fields && $table_content){
				$table	= ['fields'=>array_values($table_fields), 'content'=>array_values($table_content)];
			}else{
				$table	= null;
			}

			$post_json['table']	= $table;
		}
	}

	return $post_json;
}, 10, 2);


add_action('wp_head', function(){
	if($card_style	= wpjam_get_setting('wpjam-content-template', 'card_style')){
		echo '<style type="text/css">'."\n".
		$card_style.	
		'</style>';
	}
});