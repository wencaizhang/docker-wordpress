<?php
include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-list-table.php';

if(wp_doing_ajax()){
	add_action('wp_ajax_wpjam-list-table-action', function(){
		global $current_list_table, $wpjam_list_table;

		$wpjam_list_table	= wpjam_get_list_table($current_list_table);
		if(is_wp_error($wpjam_list_table)){
			wpjam_send_json($wpjam_list_table);
		}else{
			$wpjam_list_table->ajax_response();
		}
	});	
}else{
	global $plugin_page_setting;

	if(isset($plugin_page_setting['page_hook'])){
		add_action('load-'.$plugin_page_setting['page_hook'], function(){
			global $current_list_table, $wpjam_list_table;
			$wpjam_list_table	= wpjam_get_list_table($current_list_table);
			if(is_wp_error($wpjam_list_table)){
				wp_die($wpjam_list_table->get_error_message());
			}
		});
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

function wpjam_get_list_table_filter_link($filters, $title, $class=''){
	global $wpjam_list_table;
	return $wpjam_list_table->get_filter_link($filters, $title, $class);
}

function wpjam_get_list_table_row_action($action, $args=[]){
	global $wpjam_list_table;
	return $wpjam_list_table->get_row_action($action, $args);
}

add_action('current_screen', function($current_screen){
	global $plugin_page;
	// 如果是通过 wpjam_pages filter 定义的后台菜单
	// 需要设置 $current_screen->id=$plugin_page
	// 否则隐藏列功能就会出问题。
	$current_screen->id	= $current_screen->base = $plugin_page;
});

add_filter('set-screen-option', function ($status, $option, $value) {
	if ( isset($_GET['page']) ) {	// 如果插件页面就返回呗
		return $value;
	}else{
		return $status;
	}
}, 10, 3);


