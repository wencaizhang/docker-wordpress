<?php
do_action('wpjam_term_list_page_file', $taxonomy);

// 显示 标签，分类，tax ID
add_filter($taxonomy.'_row_actions',function ($actions, $term){
	$actions['term_id'] = 'ID：'.$term->term_id;
	return $actions;
},10,2);

$taxonomy_fields = wpjam_get_term_options($taxonomy);
if(empty($taxonomy_fields)) {
	return;
}

// 添加 Term Meta 添加表单
add_action($taxonomy.'_add_form_fields', function($taxonomy){

	$taxonomy_fields = wpjam_get_term_options($taxonomy);

	wpjam_fields($taxonomy_fields, array(
		'data_type'		=> 'term_meta',
		'fields_type'	=> 'div',
		'item_class'	=> 'form-field',
		'is_add'		=> true
	));
});

function wpjam_save_term_fields($term_id, $tt_id, $taxonomy){
	if(wp_doing_ajax()){
		if($_POST['action'] == 'inline-save-tax'){
			return;
		}
	}

	$taxonomy_fields = wpjam_get_term_options($taxonomy);
	if(empty($taxonomy_fields)) return;

	if($value = wpjam_validate_fields_value($taxonomy_fields)){
		foreach ($value as $key => $field_value) {
			// if($field_value){
				update_term_meta($term_id, $key, $field_value);
			// }else{
			// 	if(isset($fields[$key]['value'])){	// 如果设置了默认值，也是会存储的
			// 		$field_value	= ($fields[$key]['type'] == 'number')?0:'';
			// 		update_term_meta($term_id, $key, $field_value);
			// 	}elseif(get_term_meta($term_id, $key, true)) {
			// 		delete_term_meta($term_id, $key);
			// 	}
			// }
		}
	}
}
add_action('created_term', 'wpjam_save_term_fields',10,3);
add_action('edited_term', 'wpjam_save_term_fields',10,3);

$taxonomy_fields	= array_filter($taxonomy_fields, function($field){ return !empty($field['show_admin_column']); });
if(empty($taxonomy_fields)) return;

// 数据格式 key => title
$taxonomy_columns = array_combine(array_keys($taxonomy_fields), array_column($taxonomy_fields, 'title'));

// Term 列表显示字段的名
add_action('manage_edit-'.$taxonomy.'_columns',	function ($columns) use($taxonomy_columns){
	return array_merge($columns, $taxonomy_columns);
});

// Term 列表显示字段的值
add_filter('manage_'.$taxonomy.'_custom_column', function ($value, $column_name, $term_id) use($taxonomy_fields) {
	if(isset($taxonomy_fields[$column_name])){
		return wpjam_column_callback($column_name, array(
			'id'		=> $term_id,
			'field'		=> $taxonomy_fields[$column_name],
			'data_type'	=> 'term_meta'
		));
	}

	return $value;
}, 10, 3);

if(wp_doing_ajax()) return;

// 获取要在列表页面拍排序展示的字段
$taxonomy_fields	= array_filter($taxonomy_fields, function($field){ return !empty($field['sortable_column']); });
if(empty($taxonomy_fields)) return;

// 设置 Term 列表页排序的字段
$taxonomy_columns = array_combine(array_keys($taxonomy_fields), array_keys($taxonomy_fields));
add_action('manage_edit-'.$taxonomy.'_sortable_columns',function ($columns) use($taxonomy_columns){
	return array_merge($columns, $taxonomy_columns);
});

// 使后台的排序生效
add_action('parse_term_query', function($term_query) use($taxonomy_fields){
	$orderby	= $term_query->query_vars['orderby'];
	if($orderby && isset($taxonomy_fields[$orderby])){
		$term_query->query_vars['orderby']	= ($taxonomy_fields[$orderby]['sortable_column'] == 'meta_value_num')?'meta_value_num':'meta_value';
		$term_query->query_vars['meta_key']	= $orderby;
	}
});