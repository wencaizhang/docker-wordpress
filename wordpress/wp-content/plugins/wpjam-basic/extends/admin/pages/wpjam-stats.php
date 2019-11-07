<?php
add_filter('wpjam_basic_setting', function(){
	$sections	= [
		'wpjam-stats'	=> [
			'title'		=>'', 	
			'fields'	=>[
				'baidu_tongji'		=>['title'=>'百度统计',		'type'=>'fieldset',	'fields'=>[
					'baidu_tongji_id'		=>['title'=>'跟踪 ID：',	'type'=>'text']
				]],
				'google_analytics'	=>['title'=>'Google 分析',	'type'=>'fieldset',	'fields'=>[
					'google_analytics_id'	=>['title'=>'跟踪 ID：',	'type'=>'text'],
					'google_universal'		=>['title'=>'',			'type'=>'checkbox',	'description'=>'使用 Universal Analytics 跟踪代码。'],
				]]	
			]
		]
	];

	return compact('sections');
});