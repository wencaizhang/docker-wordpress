<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-reply-setting.php');

add_action('wpjam_weixin_replies_tabs', function($tabs){
	return [
		'custom'	=> ['title'=>'自定义回复',	'function'=>'list'],
		'default'	=> ['title'=>'默认回复',		'function'=>'list'],
		'builtin'	=> ['title'=>'内置回复',		'function'=>'list'],
		'third'		=> ['title'=>'第三方平台',	'function'=>'option',	'option_name'=>'weixin-robot'],
	];
});

add_filter('wpjam_weixin_replies_list_table', function (){
	global $current_tab;

	if($current_tab == 'custom' || $current_tab == 'default' ){
		$style = 'th.column-title{width:126px;}
		th.column-keyword{width:126px;}
		th.column-match{width:70px;}
		th.column-type{width:84px;}
		th.column-status{width:56px;}';

		if($current_tab == 'custom'){
			return [
				'title'				=> '自定义回复',
				'singular'			=> 'weixin-reply',
				'plural'			=> 'weixin-replies',
				'primary_column'	=> 'keyword',
				'primary_key'		=> 'id',
				'model'				=> 'WEIXIN_AdminReplySetting',
				'style'				=> $style,
				'ajax'				=> true,
				'fixed'				=> false,
			];

		}elseif($current_tab == 'default'){
			return [
				'title'				=> '默认回复',
				'singular'			=> 'weixin-reply',
				'plural'			=> 'weixin-replies',
				'primary_column'	=> 'keyword',
				'model'				=> 'WEIXIN_AdminReplySetting',
				'style'				=> $style,
				'search'			=> false,
				'ajax'				=> true,
				'fixed'				=> false,
				'actions'			=> [
					'set'	=> ['title'=>'设置']
				]
			];
		}
	}elseif ($current_tab == 'builtin') {
		$style = '
		th.column-keywords {width:40%;}
		th.column-function {width:24%;}
		.tablenav{display:none;}
		';

		return [
			'title'				=> '内置回复',
			'singular'			=> 'weixin-reply',
			'plural'			=> 'weixin-replies',
			'primary_column'	=> 'keywords',
			'model'				=> 'WEIXIN_AdminReplySetting',
			'style'				=> $style,
			'search'			=> false,
			'actions'			=> []
		];
	}
});

add_filter('wpjam_weixin_robot_setting', function(){
	global $current_tab;
	$sections = [];
	if(isset($current_tab) && $current_tab == 'third'){
		$third_party_section_fields = [
			'weixin_3rd_1_fieldset'	=> ['title'=>'第三方自定义回复平台1',	'type'=>'fieldset',	'fields'=>[
				'weixin_3rd_1'			=> ['title'=>'名称',		'type'=>'text',		'class'=>'all-options'],
				'weixin_3rd_cache_1'	=> ['title'=>'缓存时间',	'type'=>'number',	'class'=>'all-options',	'description'=>'秒，输入空或者0为不缓存！'],
				'weixin_3rd_url_1'		=> ['title'=>'链接',		'type'=>'url'],
				'weixin_3rd_search'		=> ['title'=>'',		'type'=>'checkbox',	'description'=>'所有在WordPress找不到内容的关键词都提交到第三方微信自定义回复平台1处理。']
			]],

			'weixin_3rd_2_fieldset'	=> ['title'=>'第三方自定义回复平台2',	'type'=>'fieldset',	'fields'=>[
				'weixin_3rd_2'			=> ['title'=>'名称',		'type'=>'text',		'class'=>'all-options'],
				'weixin_3rd_cache_2'	=> ['title'=>'缓存时间',	'type'=>'number',	'class'=>'all-options',	'description'=>'秒'],
				'weixin_3rd_url_2'		=> ['title'=>'链接',		'type'=>'url']
			]],

			'weixin_3rd_3_fieldset'	=> ['title'=>'第三方自定义回复平台3',	'type'=>'fieldset',	'fields'=>[
				'weixin_3rd_3'			=> ['title'=>'名称',		'type'=>'text',		'class'=>'all-options'],
				'weixin_3rd_cache_2'	=> ['title'=>'缓存时间',	'type'=>'number',	'class'=>'all-options',	'description'=>'秒'],
				'weixin_3rd_url_3'		=> ['title'=>'链接',		'type'=>'url']
			]]
		];

		$sections = [
			'third_reply'	=> [
				'title'		=>'',
				'summary'	=>'<p>如果第三方的回复的数据对所有用户都相同，建议缓存。</p>',	
				'fields'	=>$third_party_section_fields
			]
		];
	}

	return apply_filters('weixin_reply_setting', $sections, $current_tab);
});

