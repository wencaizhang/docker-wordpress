<?php
include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR .'admin/posts/template-type.php';

add_filter('disable_months_dropdown', '__return_true');

add_filter('views_edit-template', function($views){
	unset($views['publish']);

	$current	= $_GET['template_type'] ?? null;
	$template_types	= wpjam_get_content_template_types();

	foreach ($template_types as $type=>$tt) {
		$query_args	= ['no_found_rows'=>false, 'post_type'=>'template',	'meta_key'=>'_template_type'];

		if($type == 'content'){
			$query_args['meta_compare']	= 'NOT EXISTS';
		}else{
			$query_args['meta_value']	= $type;	
		}

		$query	= wpjam_query($query_args);

		if($count	= $query->found_posts){
			$class	= ($current && $current == $type) ?' class="current"' : '';
			$views[$type.'-content']	='<a href="edit.php?post_type=template&template_type='.$type.'"'.$class.'><span class="dashicons dashicons-'.$tt['dashicon'].'"></span> '.$tt['title'].'<span class="count">（'.$count.'）</span></a>';
		}
	}

	return $views;
}, 1, 2);

add_filter('post_row_actions', function($row_actions, $post){
	$template_type	= get_post_meta($post->ID, '_template_type', true) ?: '';
	if($template_type){
		$row_actions['edit']	= preg_replace('/href="(.*?)"/i', 'href="'.admin_url('edit.php?post_type=template&amp;page=wpjam-'.$template_type.'&post_id='.$post->ID).'"', $row_actions['edit']);
	}

	return $row_actions;
}, 10, 2);

add_action('pre_get_posts', function($query){
	if($query->is_main_query()){
		$template_type	= $_GET['template_type'] ?? null;

		if($template_type){
			$query->set('meta_key', '_template_type');

			if($template_type == 'content'){
				$query->set('meta_compare', 'NOT EXISTS');
			}else{
				$query->set('meta_value', $template_type);
			}
		}
	}
});

add_filter('manage_template_posts_columns', function($columns){
	$columns['template_type']	= '类型';
	$columns['shortcode']		= '短代码';

	unset($columns['date']);
	// wpjam_array_push($columns, ['template_type'=>'模板类型', 'shortcode'=>'短代码'], 'date'); 
	return $columns;
});

add_action('manage_template_posts_custom_column', function($column_name, $post_id){
	if($column_name == 'shortcode'){
		echo '[template id="'.$post_id.'"]';
	}elseif($column_name == 'template_type'){
		$template_types	= wpjam_get_content_template_types();
		$template_type	= get_post_meta($post_id, '_template_type', true) ?: 'content';
		if($template_type && isset($template_types[$template_type])){
			$tt = $template_types[$template_type];
			echo '<span class="dashicons dashicons-'.$tt['dashicon'].'"></span>  '.$tt['title'];
		}else{
			echo '';
		}
	}
}, 10, 2);

add_filter('wpjam_html_replace', function($html){
	return preg_replace('/<a href=".*?" class="page-title-action">.*?<\/a>/i', '<a href="javascript:;" class="page-title-action wpjam-new-template">新建模板</a>', $html);
});