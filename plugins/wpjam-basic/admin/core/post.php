<?php
do_action('wpjam_post_page_file', $post_type);

add_filter('post_updated_messages', function($messages){
	global $post_type;

	if($post_type == 'page' || $post_type == 'post'){
		return $messages;
	}

	function _replace_post_updated_messages($messages, $post_type){
		$labels		= get_post_type_labels(get_post_type_object($post_type));
		$label_name	= $labels->name;

		return array_map(function($message) use ($label_name){
			if($message == $label_name) return $message;

			return str_replace(
				['文章', '页面', 'post', 'Post'], 
				[$label_name, $label_name, $label_name, ucfirst($label_name)], 
				$message
			);
		}, $messages);
	}

	if(is_post_type_hierarchical($post_type)){
		$messages['page']	=  _replace_post_updated_messages($messages['page'], $post_type);
	}else{
		$messages['post']	=  _replace_post_updated_messages($messages['post'], $post_type);
	}

	return $messages;
});

add_filter('admin_post_thumbnail_html', function($content, $post_id){
	if($post_id){
		global $wp_post_type;

		$post		= get_post($post_id);
		$post_type	= $post->post_type;

		if(!empty($wp_post_types[$post_type]->thumbnail_size)){
			return $content.'<p>尺寸：'.$thumbnail_size.'</p>';
		}
	}

	return $content;
}, 10, 2);

add_filter('post_edit_category_parent_dropdown_args', function($args){
	$tax_obj	= get_taxonomy($args['taxonomy']);
	$levels		= $tax_obj->levels ?? 0;

	if($levels == 1){
		$args['parent']	= 0;
	}elseif($levels > 1){
		$args['depth']	= $levels - 1;
	}

	return $args;
});

// if(wpjam_basic_get_setting('diable_revision')){
// 	add_action('wp_print_scripts',function() {
// 		wp_deregister_script('autosave');
// 	});
// }

function wpjam_post_options_callback($post, $meta_box){
	global $pagenow;

	$fields			= $meta_box['args']['fields'];
	$fields_type	= $meta_box['args']['context']=='side' ? 'list' : 'table';

	wpjam_fields($fields, array(
		'data_type'		=> 'post_meta',
		'id'			=> $post->ID,
		'fields_type'	=> $fields_type,
		'is_add'		=> $pagenow == 'post-new.php'
	));
}

if(wpjam_basic_get_setting('diable_block_editor')){
	add_filter('use_block_editor_for_post_type', '__return_false');
}

add_action('add_meta_boxes', function($post_type){
	$post_options	= wpjam_get_post_options($post_type);

	if($post_options){
		$context	= 'normal';
		if(!function_exists('use_block_editor_for_post_type') || !use_block_editor_for_post_type($post_type)){
			$context	= 'wpjam';
		}

		// 输出日志自定义字段表单
		foreach($post_options as $meta_key => $post_option){
			$post_option = wp_parse_args($post_option, [
				'priority'		=> 'default',
				'context'		=> $context,
				'title'			=> '',
				'callback'		=> 'wpjam_post_options_callback',
				'fields'		=> []
			]);
			
			if($post_option['title']){
				add_meta_box($meta_key, $post_option['title'], $post_option['callback'], $post_type, $post_option['context'], $post_option['priority'], [
					'context'	=> $post_option['context'],
					'fields'	=> $post_option['fields']
				]);
			}
		}
	}
});

if(!function_exists('use_block_editor_for_post_type') || !use_block_editor_for_post_type($post_type)){
	add_action('edit_form_advanced', function ($post){
		global $pagenow;

		// 下面代码 copy 自 do_meta_boxes
		global $wp_meta_boxes;
		
		$page		= get_current_screen()->id;
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
				$nav_tab_title	.= '<a class="nav-tab" href="javascript:;" id="tab_title_'.$meta_box['id'].'" data-tab_id="'.$meta_box['id'].'">'.$meta_box['title'].'</a>';
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
	}, 99);
}

add_action('post_comment_status_meta_box-options', function($post){
	if(wpjam_basic_get_setting('disable_trackbacks')){
	?>
	<style type="text/css">
		label[for='ping_status']{display:none}
	</style>
	<?php
	}
});

// 保存日志自定义字段
add_action('save_post', function ($post_id, $post){
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;	
	}

	if($_SERVER['REQUEST_METHOD'] != 'POST'){
		return;	// 提交才可以
	}

	if(!empty($_POST['wp-preview']) && $_POST['wp-preview'] == 'dopreview'){
		return; // 预览不保存
	}

	static $did_save_post_option;
	if(!empty($did_save_post_option)){	// 防止多次重复调用
		return;
	}

	$did_save_post_option = true;

	$post_type		= get_current_screen()->post_type;
	$post_fields	= wpjam_get_post_fields($post_type);
	$post_fields	= apply_filters('wpjam_save_post_fields', $post_fields, $post_id);

	if(empty($post_fields)) {
		return;
	}

	// check_admin_referer('update-post_' .$post_id);
	
	if($value = wpjam_validate_fields_value($post_fields)){
		$custom	= get_post_custom($post_id);

		foreach ($value as $key => $field_value) {
			if($field_value === ''){
				if(isset($custom[$key])){
					delete_post_meta($post_id, $key);
				}
			}else{
				if(empty($custom[$key]) || maybe_unserialize($custom[$key][0]) != $field_value){
					update_post_meta($post_id, $key, $field_value);
				}
			}
		}
	}

}, 999, 2);