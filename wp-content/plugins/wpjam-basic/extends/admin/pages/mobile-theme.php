<?php
add_filter('wpjam_basic_setting', function(){
	$themes		= wp_get_themes();
	$current	= wp_get_theme();

	$theme_options		= [];
	$theme_options[$current->get_stylesheet()]	= $current->get('Name');

	foreach($themes as $theme){
		$theme_options[$theme->get_stylesheet()]	= $theme->get('Name');
	}

	$sections	= [
		'mobile-theme'	=> [
			'title'		=> '', 
			'summary'	=> '使用手机和平板访问网站的用户将看到以下选择的主题界面，而桌面用户依然看到 '.$current->get('Name').' 主题界面。',
			'fields'	=> [
				'mobile_stylesheet'	=> ['title'=>'移动主题',	'type'=>'select',	'options'=>$theme_options],
			]
		]
	];

	$field_validate	= function($value){
		$mobile_stylesheet = $value['mobile_stylesheet'] ?? '';

		if($mobile_stylesheet){
			$mobile_theme	= wp_get_theme($mobile_stylesheet);
			$value['mobile_template']	= $mobile_theme->get_template();
		}

		return $value;
	};

	return compact('sections', 'field_validate');
});