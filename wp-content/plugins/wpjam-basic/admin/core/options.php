<?php
// 为了实现多个页面使用通过 option 存储。
// 注册设置选项，选用的是：'admin_action_' . $_REQUEST['action'] 的filter，
// 因为在这之前的 admin_init 检测 $plugin_page 的合法性
add_action('admin_action_update', function(){
	global $plugin_page, $current_tab, $current_option;

	$current_option		= $_POST['option_page']??'';

	if(empty($current_option)) {
		return;
	}

	$referer_origin	= parse_url(wpjam_get_referer());

	if(empty($referer_origin['query']))	{
		return;
	}

	$referer_args	= wp_parse_args($referer_origin['query']);

	$plugin_page	= $referer_args['page'] ?? '';	// 为了实现多个页面使用通过 option 存储。
	$current_tab	= $_POST['current_tab'] ?? '';	

	wpjam_admin_init();

	wpjam_register_settings();
});

function wpjam_register_settings(){
	global $plugin_page, $current_tab, $current_option;

	$wpjam_setting = wpjam_get_option_setting($current_option);

	if(!$wpjam_setting) {
		return;
	}

	extract($wpjam_setting);

	// 只需注册字段，add_settings_section 和 add_settings_field 可以在具体设置页面添加
	if($option_type == 'array'){

		if(!empty($capability)){
			add_filter('option_page_capability_'.$current_option, function($cap) use ($capability){	return $capability; });	
		}

		add_filter('sanitize_option_'.$current_option, function($value, $option_name) use ($wpjam_setting){
			$current_tab	= ($value['current_tab'])??'';

			$fields	= array_merge(...array_column($wpjam_setting['sections'], 'fields'));
			$value	= wpjam_validate_fields_value($fields, $value);
			$value	= wp_parse_args($value, wpjam_get_option($option_name));

			if($field_validate	= $wpjam_setting['field_validate'] ?? ''){
				$value	= call_user_func($field_validate, $value);
			}

			$value['current_tab']	= $current_tab;

			return $value;
		}, 10, 2);

		register_setting($option_group, $current_option);	
	}else{
		if(!$sections) return;

		foreach ($sections as $section_id => $section) {
			foreach ($section['fields'] as $key => $field) {
				if($field['type'] == 'fieldset'){
					$fieldset_type	= ($field['fieldset_type'])??'single';
					if($fieldset_type == 'array'){
						$field_validate = function($value) use ($field){
							return wpjam_validate_field_value($field, $value);
						};

						if(!empty($field['$capability'])){
							$capability	= $field['capability'];
							add_filter('option_page_capability_'.$key, function($cap) use ($capability){
								return $capability;
							});	
						}

						register_setting($option_group, $key, $field_validate);
					}else{
						foreach ($field['fields'] as $sub_key => $sub_field) {
							$field_validate = function($value) use ($sub_field){
								return wpjam_validate_field_value($sub_field, $value);
							};

							if(!empty($sub_field['$capability'])){
								$capability	= $sub_field['capability'];
								add_filter('option_page_capability_'.$sub_key, function($cap) use ($capability){
									return $capability;
								});	
							}

							register_setting($option_group, $sub_key, $field_validate);
						}
					}
				}else{
					$field_validate = function($value) use ($field){
						return wpjam_validate_field_value($field, $value);
					};

					if(!empty($field['$capability'])){
						$capability	= $field['capability'];
						add_filter('option_page_capability_'.$key, function($cap) use ($capability){
							return $capability;
						});	
					}

					register_setting($option_group, $key, $field_validate);
				}
			}
		}
		
	}
}

function wpjam_option_ajax_response(){
	global $current_option;

	$_POST	= wp_parse_args($_POST['data']);

	wpjam_register_settings();

	$whitelist_options = apply_filters('whitelist_options', []);

	$option_page	= $_POST['option_page'];

	if(!wp_verify_nonce($_POST['_wpnonce'], $option_page . '-options')){
		wpjam_send_json([
			'errcode'	=> 'invalid_nonce',
			'errmsg'	=> '非法操作'
		]);
	}

	$capability = apply_filters('option_page_capability_'.$option_page, 'manage_options');

	if(!current_user_can($capability)){
		wpjam_send_json([
			'errcode'	=> 'no_authority',
			'errmsg'	=> '无权限'
		]);
	}

	$options	= $whitelist_options[$option_page];

	if(empty($options)){
		wpjam_send_json([
			'errcode'	=> 'invalid_option',
			'errmsg'	=> '字段未注册'
		]);
	}
		
	foreach ( $options as $option ) {
		$option = trim( $option );
		$value = null;
		if ( isset( $_POST[ $option ] ) ) {
			$value = $_POST[ $option ];
			if ( ! is_array( $value ) ) {
				$value = trim( $value );
			}
			$value = wp_unslash( $value );
		}
		update_option( $option, $value );
	}

	wpjam_send_json(['errcode'=>0, 'data'=>get_option($option)]);
}

// 后台选项页面
// 部分代码拷贝自 do_settings_sections 和 do_settings_fields 函数
function wpjam_option_page($page_setting=[]){
	global $current_option, $current_tab, $plugin_page_setting;

	if(empty($current_option)){
		return;
	}

	$page_setting	= $page_setting ?: $plugin_page_setting;
	$wpjam_setting	= wpjam_get_option_setting($current_option);

	if(!$wpjam_setting)	{
		wp_die($current_option.' 的 wpjam_settings 未设置', '未设置');
	}
	
	extract($wpjam_setting);

	if(!$sections) return;

	do_action(str_replace('-', '_', $option_page).'_option_page');

	$page_type	= count($sections) > 1 ? 'tab' : '';

	if($page_type == 'tab'){
		echo '<h1 class="nav-tab-wrapper wp-clearfix">';
		foreach ( $sections as $section_id => $section ) {
			echo '<a class="nav-tab" href="javascript:;" id="tab_title_'.$section_id.'">'.$section['title'].'</a>';
		}
		echo '</h1>';
	}else{
		$page_title	= $page_setting['page_title'] ?? ($page_setting['title'] ?? '');

		if(!empty($page_title)){
			if(preg_match("/<[^<]+>/",$page_title) ){ 
				// 如 $page_title 里面有 <h1> <h2> 标签，就不再加入 <h2> <h3> 标签了。
				echo $page_title;
			}else{
				if(empty($current_tab)){
					echo '<h1 class="wp-heading-inline">'.$page_title.'</h1>';
				}else{
					echo '<h2>'.$page_title.'</h2>';
				}
			}
		}
	}

	if(is_multisite() && is_network_admin()){	
		if($_SERVER['REQUEST_METHOD'] == 'POST'){	// 如果是 network 就自己保存到数据库	
			$fields	= array_merge(...array_column($sections, 'fields'));
			$value	= wpjam_validate_fields_value($fields, $_POST[$current_option]);
			$value	= wp_parse_args($value, wpjam_get_option($current_option));

			update_site_option( $current_option,  $value);
			wpjam_admin_add_error('设置已保存。');
		}
		wpjam_display_errors();
		echo '<form action="'.add_query_arg(['settings-updated'=>'true'], wpjam_get_current_page_url()).'" method="POST">';
	}else{
		if($ajax){
			echo '<form action="options.php" method="POST" id="wpjam_option">';
		}else{
			echo '<form action="options.php" method="POST">';
		}
	}

	settings_errors();

	echo '<div class="option-notice notice inline" style="display:none;"></div>';

	if($current_tab){
		echo '<input type="hidden" name="current_tab" value="'.$current_tab.'" />';
	}
	
	settings_fields($option_group);
	foreach($sections as $section_id => $section) {
		$section_class	= ($page_type == 'tab')?' class="div-tab hidden"':'';

		echo '<div id="tab_'.$section_id.'"'.$section_class.'>';

		if(!empty($section['title'])){
			if(empty($current_tab)){
				echo '<h2>'.$section['title'].'</h2>';
			}else{
				echo '<h3>'.$section['title'].'</h3>';
			}
		}

		if(!empty($section['callback'])) {
			call_user_func($section['callback'], $section);
		}

		if(!empty($section['summary'])) {
			echo wpautop($section['summary']);
		}
		
		if(!$section['fields']) {
			echo '</div>';
			continue;
		}

		if($option_type == 'array'){
			wpjam_fields($section['fields'], array(
				'fields_type'	=> 'table',
				'data_type'		=> 'option',
				'field_name'	=> $current_option,
				'field_callback'=> $wpjam_setting['field_callback']??''
			));
		}else{
			wpjam_fields($section['fields'], array(
				'fields_type'	=> 'table',
				'data_type'		=> 'option',
				'option_type'	=> 'single'
			));
		}
		
		echo '</div>';
	}
	
	echo '<p class="submit">';
	submit_button('', 'primary', 'submit', false);
	echo '<span class="spinner"  style="float: none; height: 28px;"></span>';
	echo '</p>';

	echo '</form>'; 
}
