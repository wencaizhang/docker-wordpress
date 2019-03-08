<?php
do_action('wpjam_post_page_file', $post_type);

function wpjam_edit_form_advanced($post){
	global $pagenow;

	$current_screen	= get_current_screen();

	$post_type		= $current_screen->post_type;
	$post_options	= wpjam_get_post_options($post_type);

	if($post_options){
		// 输出日志自定义字段表单
		foreach($post_options as $meta_key => $post_option){
			$post_option = wp_parse_args($post_option, [
				'priority'		=> 'high',
				'title'			=> '',
				'fields'		=> []
			]);
			
			if($post_option['title']){
				add_meta_box($meta_key, $post_option['title'], '', $post_type, 'wpjam', $post_option['priority'], ['fields'=>$post_option['fields']]);
			}
		}
	}

	// 下面代码 copy 自 do_meta_boxes
	global $wp_meta_boxes;
	
	$page		= $current_screen->id;
	$context	= 'wpjam';

	$wpjam_meta_boxes	= $wp_meta_boxes[$page][$context] ?? [];

	if(empty($wpjam_meta_boxes)) {
		return;
	}

	$nav_tab_title	= '';
	$meta_box_count	= 0;

	foreach(['high', 'core', 'default', 'low'] as $priority){
		if(empty($wpjam_meta_boxes[$priority])){
			continue;
		}

		foreach ((array)$wpjam_meta_boxes[$priority] as $meta_box) {
			if(empty($meta_box['id']) || empty($meta_box['title'])){
				continue;
			}

			$meta_box_count++;
			// $class	= ($meta_box_count == 1)?'nav-tab nav-tab-active':'nav-tab';
			$nav_tab_title	.= '<a class="nav-tab" href="javascript:;" id="tab_title_'.$meta_box['id'].'">'.$meta_box['title'].'</a>';
			$meta_box_title	= $meta_box['title'];
		}
	}

	if(empty($nav_tab_title)){
		return;
	}

	echo '<div id="'.htmlspecialchars($context).'-sortables" class="meta-box-sortables">';
	echo '<div id="'.$context.'" class="postbox">' . "\n";
	
	if($meta_box_count == 1){	
		echo '<h2 class="hndle">';
		echo $meta_box_title;
		echo '</h2>';
	}else{
		echo '<h2 class="nav-tab-wrapper">';
		echo $nav_tab_title;
		echo '</h2>';
	}

	echo '<div class="inside">' . "\n";
	foreach (['high', 'core', 'default', 'low'] as $priority) {
		if (!isset($wpjam_meta_boxes[$priority])){
			continue;
		}
		
		foreach ((array) $wpjam_meta_boxes[$priority] as $meta_box) {
			if(empty($meta_box['id']) || empty($meta_box['title'])){
				continue;
			}

			if($meta_box_count > 1){
				echo '<div id="tab_'.$meta_box['id'].'" class="div-tab hidden">';
			}
			
			if(isset($post_options[$meta_box['id']])){
				wpjam_fields($post_options[$meta_box['id']]['fields'], array(
					'data_type'		=> 'post_meta',
					'id'			=> $post->ID,
					'fields_type'	=> 'table',
					'is_add'		=> ($pagenow == 'post-new.php')?true:false
				));
			}else{
				call_user_func($meta_box['callback'], $post, $meta_box);
			}
			
			if($meta_box_count > 1){
				echo "</div>\n";
			}
		}
	}
	echo "</div>\n";

	echo "</div>\n";
	echo "</div>";
}

if(function_exists('use_block_editor_for_post_type') && use_block_editor_for_post_type($post_type)){
	$post_fields	= wpjam_get_post_fields($post_type);
	$post_fields	= array_filter($post_fields, function($post_field){
		return empty($post_field['show_admin_column']) || ($post_field['show_admin_column'] != 'only');
	});

	if(!empty($post_fields)){
		add_filter('use_block_editor_for_post_type', '__return_false');
		add_action('edit_form_advanced', 'wpjam_edit_form_advanced', 99);
	}
}else{
	add_action('edit_form_advanced', 'wpjam_edit_form_advanced', 99);
}

// 保存日志自定义字段
add_action('save_post', function ($post_id, $post){
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if($_SERVER['REQUEST_METHOD'] != 'POST') return;	// 提交才可以

	if(!empty($_POST['wp-preview']) && $_POST['wp-preview'] == 'dopreview') return; // 预览不保存

	static $did_save_post_option;
	if(!empty($did_save_post_option)){	// 防止多次重复调用
		return;
	}

	$did_save_post_option = true;

	$current_screen	= get_current_screen();
	$post_type		= $current_screen->post_type;

	$post_fields	= [];

	foreach (wpjam_get_post_fields($post_type) as $key => $post_field) {
		if($post_field['type'] == 'fieldset'){
			if(isset($post_field['fields'][$key.'_individual'])){
				if(empty($_POST[$key.'_individual'])){
					foreach ($post_field['fields'] as $sub_key => $sub_field){
						if(metadata_exists('post', $post_id, $sub_key)){
							delete_post_meta($post_id, $sub_key);
						}
					}
				}else{
					unset($post_field['fields'][$key.'_individual']);
					$post_fields[$key]	= $post_field;
				}
			}else{
				$post_fields[$key]	= $post_field;
			}
		}else{
			$post_fields[$key]	= $post_field;
		}
	}

	if(empty($post_fields)) return;

	$post_fields	= apply_filters('wpjam_save_post_fields', $post_fields, $post_id);

	// check_admin_referer('update-post_' .$post_id);
	
	if($value = wpjam_validate_fields_value($post_fields)){
		$custom	= get_post_custom($post_id);

		if(get_current_blog_id() == 339){
			// wpjam_print_R($value);
			// exit;
			// trigger_error(var_export($custom, true));
		}

		// trigger_error(var_export($value, true));

		foreach ($value as $key => $field_value) {
			if(empty($custom[$key]) || maybe_unserialize($custom[$key][0]) != $field_value){
				update_post_meta($post_id, $key, $field_value);
			}
		}
	}

	do_action('wpjam_save_post_options', $post_id, $value, $post_fields);
}, 999, 2);