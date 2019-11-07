<?php
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

		echo '<hr class="wp-header-end">';
	}

	$current_tab_setting	= $tabs[$current_tab];

	wpjam_admin_plugin_page($current_tab_setting);
}

function wpjam_get_ajax_button($args){
	global $plugin_page;

	$args	= wp_parse_args($args, [
		'fields'		=> [],
		'data'			=> [],
		'action'		=> '',
		'direct'		=> '',
		'confirm'		=> '',
		'tb_width'		=> 0,
		'tb_height'		=> 0,
		'confirm'		=> '',
		'button_text'	=> '保存',
		'page_title'	=> '',
		'tag'			=> 'a',
		'class'			=> 'button-primary large',
		'style'			=> ''
	]);

	$action	= $args['action'];

	if(empty($action)){
		return '';
	}

	$datas	= [];
	$datas['action']	= $action;
	$datas['nonce']		= wp_create_nonce($plugin_page.'-'.$action);
	$datas['title']		= $page_title = $args['page_title'] ?: $args['button_text'];;
	$datas['data']		= $args['data'] ? http_build_query($args['data']) : '';
	$datas['direct']	= $args['direct'];
	$datas['confirm']	= $args['confirm'];
	$datas['tb_width']	= $args['tb_width'];
	$datas['tb_height']	= $args['tb_height'];

	$data_attr	= '';
	foreach ($datas as $data_key=>$data_value) {
		if($data_value){
			$data_attr	.= ' data-'.$data_key.'="'.$data_value.'"';
		}
	}
	
	$style	= $args['style'] ? ' style="'.$args['style'].'"' : '';
	$class	= $args['class'] ? $args['class'].' wpjam-button' : 'wpjam-button';
	$class	= ' class="'.$class.'"';

	if($args['tag'] == 'a'){
		return '<a href="javascript:;" title="'.esc_attr($page_title).'" id="wpjam_button_'.$action.'" '.$class.$style.$data_attr.'>'.$args['button_text'].'</a>';
	}else{
		return '<'.$args['tag'].' title="'.esc_attr($page_title).'" id="wpjam_button_'.$action.'" '.$class.$style.$data_attr.'>'.$args['button_text'].'</'.$args['tag'].'>';
	}
}

function wpjam_ajax_button($args){
	echo wpjam_get_ajax_button($args);
}

function wpjam_ajax_form($args){
	echo wpjam_get_ajax_form($args);
}

function wpjam_get_ajax_form($args){
	global $plugin_page;

	$args	= wp_parse_args($args, [
		'data_type'		=> 'form',
		'fields_type'	=> 'table',
		'fields'		=> [],
		'data'			=> [],
		'bulk'			=> false,
		'ids'			=> [],
		'id'			=> '',
		'action'		=> '',
		'page_title'	=> '',
		'submit_text'	=> '',
		'nonce'			=> '',
		'form_id'		=> 'wpjam_form',
		'notice_class'	=> '',
	]);

	$action	= $args['action'];

	if(empty($action)){
		return '';
	}

	$output	= '';

	$fields	= $args['fields'];
	if($fields){
		$datas	= [];
		$datas['action']	= $action;
		$datas['nonce']		= $args['nonce'] ?: wp_create_nonce($plugin_page.'-'.$action);
		$datas['title']		= $args['page_title'] ?: $args['submit_text'];

		if($args['bulk']){
			$datas['bulk']	= $args['bulk'];
			$datas['ids']	= $args['ids'] ? http_build_query($args['ids']) : '';
		}else{
			$datas['id']	= $args['id'];
		}

		$data_attr	= '';
		foreach ($datas as $data_key=>$data_value) {
			if($data_value){
				$data_attr	.= ' data-'.$data_key.'="'.$data_value.'"';
			}
		}

		$output	.= '<div class="'.$args['notice_class'].' notice inline is-dismissible hidden"></div>';

		if($args['bulk']){
			$output	.=  '<form method="post" action="#" id="'.$args['form_id'].'" '.$data_attr.'>';
		}else{
			$output	.=  '<form method="post" action="#" id="'.$args['form_id'].'" '.$data_attr.'>';
		}
		
		$args['echo']	= false;
		$output	.= WPJAM_Field::fields_callback($fields, $args);
	}

	if($args['submit_text']){
		$output	.= '<p class="submit"><input type="submit" class="button-primary large" value="'.$args['submit_text'].'"> <span class="spinner"></span></p>';
	}

	$output	.= '<div class="response" style="display:none;"></div>';

	if($fields){
		$output	.= '</form>';
	}

	return $output;
}

// 获取页面来源
function wpjam_get_referer(){
	$referer	= wp_get_original_referer();
	$referer	= $referer?:wp_get_referer();

	$removable_query_args	= array_merge(wp_removable_query_args(), ['_wp_http_referer', 'action', 'action2', '_wpnonce']);

	return remove_query_arg($removable_query_args, $referer);	
}

function wpjam_admin_add_error($message='', $type='success'){
	WPJAM_Notice::$errors[]	= compact('message','type');
}

function wpjam_display_errors(){
	global $plugin_page;

	if(!empty($plugin_page)){

		$did_auto_error	= false;

		if(empty($did_auto_error)){
			$did_auto_error	= true;

			$removable_query_args	= wp_removable_query_args();

			if($removable_query_args = array_intersect($removable_query_args, array_keys($_GET))){
				foreach ($removable_query_args as $key) {
					if($key != 'message' && $key != 'settings-updated'){
						if($_GET[$key] === 'true' || $_GET[$key] === '1'){
							WPJAM_Notice::$errors[]	= ['message'=>'操作成功','type'=>'success'];
						}else{
							WPJAM_Notice::$errors[]	= ['message'=>$_GET[$key],'type'=>'error'];
						}
					}
				}
			}
		}
	}

	if(WPJAM_Notice::$errors){
		foreach (WPJAM_Notice::$errors as $error){
			$error	= wp_parse_args($error, [
				'type'		=> 'error',
				'message'	=> '',
			]);

			if($error['message']){
				echo '<div class="notice notice-'.$error['type'].' is-dismissible"><p>'.$error['message'].'</p></div>';
			}
		}
	}

	WPJAM_Notice::$errors	= [];
}

function wpjam_get_form_post($fields, $nonce_action='', $capability='manage_options'){
	check_admin_referer($nonce_action);

	if( !current_user_can( $capability )){
		ob_clean();
		wp_die('无权限');
	}

	return WPJAM_Field::validate_fields_value($fields);
}

function wpjam_column_callback($column_name, $args=[]){
	return WPJAM_Field::column_callback($column_name, $args);
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