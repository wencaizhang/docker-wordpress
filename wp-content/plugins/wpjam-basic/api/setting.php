<?php
if(!empty($args['option_name'])){
	$option_name	= $args['option_name']??'';
	$setting		= $args['setting']??'';
	$output			= $args['output']??'';
	$option			= get_option($option_name);
	$option_setting	= wpjam_get_option_setting($option_name);
	if(empty($option_setting)){
		wpjam_send_json(new WP_Error('option_not_exists', $option_name.'设置不存在'));
	}

	$option_fields	= current($option_setting['sections'])['fields'];

	if($setting){
		if(!isset($option_fields[$setting])){
			wpjam_send_json(new WP_Error('setting_not_exists', $setting.'设置不存在'));
		}

		$setting_field	= $option_fields[$setting];
		$setting_value	= ($option[$setting])??'';

		if($setting_field['type'] == 'fieldset'){
			$fieldset_type	= ($setting_field['fieldset_type'])??'single';
			if($setting_field['fieldset_type'] = 'single'){
				$setting_value		= array();
				foreach ($setting_field['fields'] as $sub_key => $sub_field) {
					$setting_value[$sub_key]	= ($option[$sub_key])??'';
				}
			}
		}
		
		$output	= $output ?: $setting; 
		$response[$output]		= wpjam_parse_field_value($setting_value, $setting_field);

		// wpjam_print_R(wpjam_parse_field_value($setting_value, $setting_field));
	}else{
		$option_value	= [];
		if($option){
			foreach ($option_fields as $setting => $setting_field) {
				$setting_value	= ($option[$setting])??'';

				if($setting_field['type'] == 'fieldset'){
					$fieldset_type	= ($setting_field['fieldset_type'])??'single';
					if($setting_field['fieldset_type'] = 'single'){
						$setting_value		= array();
						foreach ($setting_field['fields'] as $sub_key => $sub_field) {
							$setting_value[$sub_key]	= ($option[$sub_key])??'';
						}
					}
				}

				$option_value[$setting]	= wpjam_parse_field_value($setting_value, $setting_field);
			}
		}

		$output	= $output ?: $option_name; 
		$response[$output]	= $option_value;
	}
}