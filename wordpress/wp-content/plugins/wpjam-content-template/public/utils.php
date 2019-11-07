<?php
wpjam_register_post_type('template',	[
	'label'			=> '内容模板',
	'public'		=> false,
	'show_ui'		=> true,
	'has_archive'	=> false,
	'rewrite'		=> false,
	'query_var'		=> false,
	'menu_icon'		=> 'dashicons-edit',
	'menu_position'	=> 50,
	'supports'		=> ['title','editor'],
]);

function wpjam_get_content_template_types(){
	return [
		'content'	=>['title'=>'内容模板',	'dashicon'=>'edit'],
		'table'		=>['title'=>'表格模板',	'dashicon'=>'editor-table',		'function'=>'tab'],
		'card'		=>['title'=>'卡片模板',	'dashicon'=>'index-card',		'function'=>'tab'],
		// 'code'		=>['title'=>'代码模板',	'dashicon'=>'code-standards',	'function'=>'tab'],
	];
}

function wpjam_get_content_template($id, $class, $text){
	$post	= get_post($id);

	if(empty($post) || $post->post_type != 'template'){
		return '';
	}

	$template_type	= get_post_meta($id, '_template_type', true);

	$class	= $class ? $class .' ' : '';
	$class	.= 'content-template';
	
	if(post_password_required($post)){
		$text	= "\n".get_the_password_form($post)."\n".wpautop(do_shortcode($text));
		$class	.= ' post-password-content-template';
	}else{
		if($template_type == 'table'){
			$text	= $post->post_excerpt . $text;
		}elseif($template_type == 'card'){
			// 不需要
		}else{
			$text	= $post->post_content . $text;
		}
		
		$text	= $text ? "\n".wpautop(do_shortcode($text)) : '';

		if($template_type == 'table'){
			$text	.= wpjam_get_content_template_table($post); 
			$class	.= ' table-content-template';
		}elseif($template_type == 'card'){
			$text	= wpjam_get_content_template_card($post);
			$class	.= ' card-content-template';
		}elseif($post->post_password){
			$class	.= ' post-password-content-template';
		}
	}

	return '<div class="'.$class.'">'."\n".$text."\n</div>";
}

function wpjam_get_content_template_card($post){
	$post_content	= $post->post_content;
	$content		= maybe_unserialize($post_content);

	$card_type		= $content['card_type'] ?? 1;
	$thumbnail		= $content['thumbnail'] ?? '';
	$price			= $content['price'] ?? '';
	$link			= $content['link'] ?? '';
	$weapp			= $content['weapp'] ?? [];

	$card	= '';

	if($card_type == 1){
		$card		.= '<img class="card-thumbnail" src="'.wpjam_get_thumbnail($thumbnail, '200x200').'" width="100" height="100" alt="'.esc_attr($post->post_title).'" />';

		if($post->post_title){
			$card	.= '<h3 class="card-title">'.$post->post_title.'</h3>';
		}
			
		if($post->post_excerpt){
			$card	.= '<p class="card-except">'.$post->post_excerpt.'</p>';
		}
		
		if($price){
			$card	.= '
			<div class="card-meta">
				<div class="card-price">￥'.$price.'</div>
				<div class="card-button">去选购</div>
			</div>';
		}
	}else{
		$card	.= '<img class="card-banner" src="'.wpjam_get_thumbnail($thumbnail, '1200').'" alt="'.esc_attr($post->post_title).'" />';
	}

	if(is_weapp()){
		if($weapp['appid'] == 'weapp'){
			$card	= '<a href_type="weapp" href="'.$weapp['path'].'">'.$card.'</a>';
		}elseif($weapp['appid'] == 'webview'){
			$card	= '<a href_type="webview" href="'.$link.'">'.$card.'</a>';;
		}else{
			$card	= '<a href_type="miniprograme" href="'.$weapp['path'].'" appid="'.$weapp['appid'].'">'.$card.'</a>';
		}
	}else{
		if($link){
			$card	= '<a href="'.$link.'">'.$card.'</a>';
		}
	}

	return $card;
}

function wpjam_get_content_template_table($post){

	$post_content	= $post->post_content;
	$table_content	= $post_content ? maybe_unserialize($post_content) : [];

	$table_fields	= get_post_meta($post->ID, '_table_fields', true);
	
	if($table_content && $table_fields){
		$table_fields	= wpjam_parse_content_template_table_fields($table_fields);

		$thead = $tbody = '';

		foreach ($table_fields as $table_field) {
			$thead .= "\t\t\t".'<th>'.$table_field['title'].'</th>'."\n";
		}

		$thead = "\t\t".'<tr>'."\n".$thead."\t\t".'</tr>'."\n";

		foreach ($table_content as $table_row) {
			$tbody .= "\t\t".'<tr>'."\n";
			foreach($table_fields as $table_field){
				$field_type		= $table_field['type'];
				$field_index	= 'i'.$table_field['index'];

				$value	= $table_row[$field_index] ?? '';

				if($field_type == 'img'){
					if(!empty($table_row[$field_index])){
						$thumb	= wpjam_get_thumbnail($value, '200x200');
						$value	= '<a href="'.$value.'"><img src="'.$thumb.'" width="100" height="100" /></a>';
					}
				}elseif($field_type == 'textarea'){
					$value	= wpautop(do_shortcode($value));
				}

				if(isset($table_field['url'])){
					$url_index	= 'i'.$table_field['url']['index'];

					$url	= $table_row[$url_index] ?? '';
					$value	= '<a href="'.$url.'">'.$value.'</a>';
				}

				$tbody .= "\t\t\t".'<td>'.$value.'</td>'."\n";
			}
			$tbody .= "\t\t".'</tr>'."\n";
		}

		$table		= "\t".'<thead>'."\n".$thead."\t".'</thead>'."\n"."\t".'<tbody>'."\n".$tbody."\t".'</tbody>'."\n";
		// $table .= "\t".'<tfoot>'."\n".$thead."\t".'</tfoot>'."\n";

		$summary	= $post->post_title ? 'summary="'.esc_attr($post->post_title).'"' : '';

		return "\n".'<table '.$summary.'>'."\n".$table.'</table>'."\n";
	}

	return '';
}

function wpjam_parse_content_template_table_fields($table_fields){
	$url_for_fields	= [];

	foreach($table_fields as $key => $table_field) {
		if($table_field['type'] == 'url'){
			if($table_field['url_for']){
				$url_for_fields[$table_field['url_for']]	= $key;
			}
		}
	}

	$fields	= [];

	foreach($table_fields as $key => $table_field) {
		if($table_field['type'] != 'url'){
			if(isset($url_for_fields[$table_field['title']])){
				$url_key	= $url_for_fields[$table_field['title']];

				$table_field['url']	= $table_fields[$url_key];

				$fields[]	= $table_field;
			}else{
				if(empty($table_field['url_for'])){
					$fields[]	= $table_field;
				}
			}
		}
	}

	return $fields;
}