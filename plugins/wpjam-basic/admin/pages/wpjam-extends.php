<?php
add_filter('wpjam_extends_setting', function(){
	$fields		= [];
	$extend_dir = WPJAM_BASIC_PLUGIN_DIR.'extends';

	if(is_dir($extend_dir)) { 
		$wpjam_extends 	= wpjam_get_option('wpjam-extends');

		if($wpjam_extends){	// 已激活的优先
			foreach ($wpjam_extends as $extend_file => $value) {
				if(!$value || !is_file($extend_dir.'/'.$extend_file)){
					continue;
				}

				$data = get_plugin_data($extend_dir.'/'.$extend_file);
				if($data['Name']){
					$fields[$extend_file] = ['title'=>$data['Name'], 'type'=>'checkbox', 'description'=>$data['Description']];
				}
			}
		}

		if($extend_handle = opendir($extend_dir)) {   
			while (($extend_file = readdir($extend_handle)) !== false) {
				if ($extend_file == '.' || $extend_file == '..' || !is_file($extend_dir.'/'.$extend_file) || !empty($wpjam_extends[$extend_file])){
					continue;
				}
					
				if(pathinfo($extend_file, PATHINFO_EXTENSION) != 'php') {
					continue;
				}

				$data = get_plugin_data($extend_dir.'/'.$extend_file);

				if($data['Name']){
					$fields[$extend_file] = ['title'=>$data['Name'], 'type'=>'checkbox', 'description'=>$data['Description']];
				}		
				
			}   
			closedir($extend_handle);   
		}
	} 

	if(is_multisite() && !is_network_admin()){
		$sitewide_extends = get_site_option('wpjam-extends');

		unset($sitewide_extends['plugin_page']);
		
		if($sitewide_extends){
			foreach ($sitewide_extends as $extend_file => $value) {
				if($value){
					unset($fields[$extend_file]);	
				}
			}
		}
	}

	$field_validate	= function($value) use($fields){
		$new_value	= [];
		foreach ($value as $k => $v) {
			if($v && isset($fields[$k])){
				$new_value[$k]	= 1;
			}
		}

		return $new_value;
	};

	$summary	= is_network_admin() ? '在管理网络激活将整个站点都会激活！' : '';
	$ajax		= false;

	return compact('summary', 'fields', 'ajax', 'field_validate');
});


