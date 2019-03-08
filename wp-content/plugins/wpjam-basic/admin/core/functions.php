<?php
include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-form.php';
include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-ajax.php';

// 后台页面
function wpjam_admin_page(){
	global $plugin_page_setting;

	echo '<div class="wrap">';

	wpjam_admin_plugin_page($plugin_page_setting);

	echo '</div>';
}

// 后台页面
function wpjam_admin_plugin_page($page_setting){
	$function	= $page_setting['function'] ?? null;

	if($function == 'list'){
		wpjam_admin_list_page($page_setting);
	}elseif($function == 'option'){
		wpjam_option_page($page_setting);
	}elseif($function == 'tab'){
		wpjam_admin_tab_page($page_setting);
	}elseif($function == 'dashboard'){
		wpjam_admin_dashboard_page($page_setting);
	}else{
		global $plugin_page;

		$function	= $function ?: wpjam_get_filter_name($plugin_page, 'page');
		if(!function_exists($function)){
			wp_die($function.'不存在');
		}else{
			call_user_func($function);
		}
	}
}

// Tab 后台页面
function wpjam_admin_tab_page($page_setting){
	global $plugin_page, $current_tab, $current_admin_url;

	$function	= wpjam_get_filter_name($plugin_page, 'page');	// 所有 Tab 页面都执行的函数
	if(function_exists($function)){
		call_user_func($function);
	}

	$tabs	= $page_setting['tabs'];

	if(count($tabs) > 1){
		
		echo '<h1 class="nav-tab-wrapper wp-clearfix">';
		
		foreach ($tabs as $tab_key => $tab) {
			$class	= 'nav-tab';
		
			if($current_tab == $tab_key){
				$tab_url	= $current_admin_url;
				$class		.= ' nav-tab-active';
			}else{
				$tab_url	= $page_setting['tab_url'].'&tab='.$tab_key;
			}

			echo '<a class="'.$class.'" href="'. $tab_url.'">'.$tab['title'].'</a>';
		}

		echo '</h1>';
	}

	$current_tab_setting	= $tabs[$current_tab];

	wpjam_admin_plugin_page($current_tab_setting);
}

function wpjam_add_admin_notice($admin_notice){
	WPJAM_Notice::add($admin_notice);
}

function wpjam_admin_add_error($message='', $type='success'){
	WPJAM_Notice::$errors[]	= compact('message','type');
}

function wpjam_display_errors(){
	WPJAM_Notice::display_errors();
}

add_action('wp_ajax_delete_wpjam_notice', function(){
	// check_ajax_referer("wpjam_setting_nonce");
	WPJAM_Notice::delete($_POST['key']);
});

function wpjam_page_ajax_response($args){
	WPJAM_AJAX::ajax_response($args);
}

function wpjam_get_ajax_button($args){
	return WPJAM_AJAX::get_button($args);
}

function wpjam_ajax_button($args){
	echo wpjam_get_ajax_button($args);
}

function wpjam_ajax_form($args){
	WPJAM_AJAX::form($args);
}

// 获取页面来源
function wpjam_get_referer(){
	$referer	= wp_get_original_referer();
	$referer	= $referer?:wp_get_referer();

	$removable_query_args	= array_merge(wp_removable_query_args(), ['_wp_http_referer', 'action', 'action2', '_wpnonce']);

	return remove_query_arg($removable_query_args, $referer);	
}

// 显示字段
function wpjam_fields($fieds, $args=[]){
	WPJAM_Form::fields_callback($fieds, $args);
}

function wpjam_column_callback($column_name, $args=[]){
	return WPJAM_Form::column_callback($column_name, $args);
}

// 验证一组字段的值
function wpjam_validate_fields_value($fields, $value=''){
	return WPJAM_Form::validate_fields_value($fields, $value);
}

// 验证单个字段的值
function wpjam_validate_field_value($field, $value=''){
	return WPJAM_Form::validate_field_value($field, $value);
}

// 获取表单 HTML
function wpjam_get_field_html($field){
	return WPJAM_Form::get_field_html($field);
}

function wpjam_form_field_tmpls(){
	if(WPJAM_Form::$field_tmpls){ 
		foreach (WPJAM_Form::$field_tmpls as $tmpl_id => $field_tmpl) { ?>

		<script type="text/html" id="tmpl-wpjam-<?php echo $tmpl_id; ?>">
		<?php echo $field_tmpl."\n"; ?>
		</script>

		<?php }
	}
}

function wpjam_get_form_post($fields, $nonce_action='', $capability='manage_options'){
	check_admin_referer($nonce_action);

	if( !current_user_can( $capability )){
		ob_clean();
		wp_die('无权限');
	}

	return WPJAM_Form::validate_fields_value($fields);
}

// 自定义主题更新
/* 数据格式：
{
	theme: "Autumn",
	new_version: "2.0.1",
	url: "http://www.xintheme.com/theme/4893.html",
	package: "http://www.xintheme.com/download/Autumn.zip"
}
*/
function wpjam_register_theme_upgrader($upgrader_url){
	add_filter('site_transient_update_themes',  function($transient) use($upgrader_url){
		$theme	= get_template();

		if(empty($transient->checked[$theme])){
			return $transient;
		}
		
		$remote	= get_transient('wpjam_theme_upgrade_'.$theme);

		if(false == $remote){
			$remote = wpjam_remote_request($upgrader_url);
	 
			if(!is_wp_error($remote)){
				set_transient( 'wpjam_theme_upgrade_'.$theme, $remote, HOUR_IN_SECONDS*12 ); // 12 hours cache
			}
		}

		if($remote && !is_wp_error($remote)){
			if(version_compare( $transient->checked[$theme], $remote['new_version'], '<' )){
				$transient->response[$theme]	= $remote;
			}
		}

		return $transient;
	});
}

// 逐步放弃
function wpjam_list_table($args = []){
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-list-table.php';

	return new WPJAM_List_Table($args);
}

// 编辑表单 
// 逐步放弃
function wpjam_form($fields, $form_url, $nonce_action='', $submit_text=''){
	?>
	<?php wpjam_display_errors();?>
	<form method="post" action="<?php echo $form_url; ?>" enctype="multipart/form-data" id="form">
		<?php wpjam_fields($fields); ?>
		<?php wp_nonce_field($nonce_action);?>
		<?php wp_original_referer_field(true, 'previous');?>
		<?php if($submit_text!==false){ submit_button($submit_text); } ?>
	</form>
	<?php
}

// 逐步放弃
function wpjam_get_form_fields($admin_column = false){
	global $plugin_page;
	$form_fields = apply_filters($plugin_page.'_fields', []);

	if($form_fields){
		foreach($form_fields as $key => $field){
			if($field['type'] == 'fieldset'){
				foreach ($field['fields'] as $sub_key => $sub_field) {
					if($admin_column){
						if(empty($sub_field['show_admin_column'])){
							unset($form_fields[$key]['fields'][$sub_key]);
						}
					}else{
						if(isset($sub_field['show_admin_column']) && $sub_field['show_admin_column'] === 'only'){
							unset($form_fields[$key]['fields'][$sub_key]);
						}
					}
				}
				if(empty($form_fields[$key]['fields'])){
					unset($form_fields[$key]);
				}
			}else{
				if($admin_column){
					if(empty($field['show_admin_column'])){
						unset($form_fields[$key]);
					}
				}else{
					if(isset($field['show_admin_column']) && $field['show_admin_column'] === 'only'){
						unset($form_fields[$key]);
					}
				}
			}
		}
	}

	return $form_fields;
}