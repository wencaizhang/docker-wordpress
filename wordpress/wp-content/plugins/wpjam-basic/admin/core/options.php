<?php
if(wp_doing_ajax()){
	add_action('wp_ajax_wpjam-option-action', 'wpjam_option_ajax_response');
}else{
	global $plugin_page_setting;
	if(isset($plugin_page_setting['page_hook'])){
		add_action('load-'.$plugin_page_setting['page_hook'], 'wpjam_option_register_settings');
	}

	add_action('admin_action_update', 'wpjam_option_register_settings');
}	

function wpjam_option_register_settings(){
	global $plugin_page, $current_option;

	$wpjam_setting = wpjam_get_option_setting($current_option);

	if(!$wpjam_setting) {
		return;
	}

	$option_blog_id	= $wpjam_setting['blog_id'] ?? '';
	$switched		= (is_multisite() && $option_blog_id) ? switch_to_blog($option_blog_id) : false;

	extract($wpjam_setting);

	if(!empty($capability)){
		add_filter('option_page_capability_'.$option_page, function() use ($capability){
			return $capability; 
		});	
	}

	// 只需注册字段，add_settings_section 和 add_settings_field 可以在具体设置页面添加
	if($option_type == 'array'){

		add_filter('sanitize_option_'.$current_option, function($value, $option_name){
			$wpjam_setting	= wpjam_get_option_setting($option_name);

			$fields	= array_merge(...array_column($wpjam_setting['sections'], 'fields'));
			$value	= wpjam_validate_fields_value($fields, $value);
			$value	= wp_parse_args($value, wpjam_get_option($option_name));

			$field_validate	= $wpjam_setting['field_validate'] ?? '';
			
			if($field_validate){
				$value	= call_user_func($field_validate, $value);
			}

			return $value;
		}, 10, 2);

		register_setting($option_group, $current_option);	
	}else{
		if(!$sections) {
			return;
		}

		foreach ($sections as $section_id => $section) {
			foreach ($section['fields'] as $key => $field) {
				if($field['type'] == 'fieldset'){
					$fieldset_type	= $field['fieldset_type'] ?? 'single';
					if($fieldset_type == 'single'){
						foreach ($field['fields'] as $sub_key => $sub_field) {
							$sanitize = function($value) use ($sub_field){
								return WPJAM_Field::sanitize_by_field($value, $sub_field);
							};

							register_setting($option_group, $sub_key, $sanitize);
						}
						continue;	
					}
				}

				$sanitize = function($value) use ($field){
					return WPJAM_Field::sanitize_by_field($value, $field);
				};

				register_setting($option_group, $key, $sanitize);
			}
		}
	}
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

	$option_blog_id	= $wpjam_setting['blog_id'] ?? '';
	$switched		= (is_multisite() && $option_blog_id) ? switch_to_blog($option_blog_id) : false;

	do_action(str_replace('-', '_', $option_page).'_option_page');

	$page_type	= count($sections) > 1 ? 'tab' : '';

	if($page_type == 'tab'){
		echo '<h1 class="nav-tab-wrapper wp-clearfix">';
		foreach ( $sections as $section_id => $section ) {
			echo '<a class="nav-tab" href="javascript:;" id="tab_title_'.$section_id.'" data-tab_id="'.$section_id.'">'.$section['title'].'</a>';
		}
		echo '</h1>';
		echo '<hr class="wp-header-end">';
	}else{
		$page_title	= $page_setting['page_title'] ?? ($page_setting['title'] ?? '');

		if(!empty($page_title)){
			if(preg_match("/<[^<]+>/",$page_title) ){ 
				// 如 $page_title 里面有 <h1> <h2> 标签，就不再加入 <h2> <h3> 标签了。
				echo $page_title;
			}else{
				if(empty($current_tab)){
					echo '<h1 class="wp-heading-inline">'.$page_title.'</h1>';
					echo '<hr class="wp-header-end">';
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

			if($field_validate	= $wpjam_setting['field_validate'] ?? ''){
				$value	= call_user_func($field_validate, $value);
			}

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
				'option_name'	=> $current_option
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

	if($switched){
		restore_current_blog();
	}
}

function wpjam_option_ajax_response(){
	global $current_option;

	wpjam_option_register_settings();

	$wpjam_setting	= wpjam_get_option_setting($current_option);

	$_POST	= wp_parse_args($_POST['data']);

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
		
		
		update_option($option, $value);
	}

	$data = get_option($option);

	wpjam_send_json(['data'=>$data]);
}