<?php
include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-list-table.php';

function wpjam_admin_list_page_load(){
	global $current_list_table, $wpjam_list_table;

	$wpjam_list_table	= wpjam_get_list_table($current_list_table);

	if(is_wp_error($wpjam_list_table)){
		wp_die($wpjam_list_table->get_error_message());
	}
	
	return true;
}

function wpjam_admin_list_page($page_setting=[]){
	global $wpjam_list_table;

	if($wpjam_list_table){
		$result = $wpjam_list_table->prepare_items();

		if(is_wp_error($result)){
			wp_admin_add_error($result->get_error_message());
		}else{
			echo '<div class="list-table">';
			$wpjam_list_table->list_page();
			echo '</div>';
		}
	}
}

function wpjam_get_list_table($current_list_table){

	$wpjam_list_table_args	= apply_filters(wpjam_get_filter_name($current_list_table, 'list_table'), []);

	if(empty($wpjam_list_table_args)){
		return new WP_Error('invalid_list_table_args', '非法 List Table 参数');
	}

	$wpjam_list_table_args	= wp_parse_args($wpjam_list_table_args, ['primary_key'=>'id','name'=>$current_list_table]);
	$wpjam_list_table	= new WPJAM_List_Table($wpjam_list_table_args);

	if(empty($wpjam_list_table->get_model())){
		return new WP_Error('invalid_model', 'List Table 的 Model 未定义');
	}

	return $wpjam_list_table;
}

function wpjam_list_table_ajax_response(){
	global $current_list_table, $wpjam_list_table;

	$wpjam_list_table	= wpjam_get_list_table($current_list_table);

	if(is_wp_error($wpjam_list_table)){
		wpjam_send_json($wpjam_list_table);
	}

	$wpjam_list_table->ajax_response();
}

add_filter('set-screen-option', function ($status, $option, $value) {
	if ( isset($_GET['page']) ) {	// 如果插件页面就返回呗
		return $value;
	}else{
		return $status;
	}
}, 10, 3);


