<?php
if(!WPJAM_Verify::verify()){
	wp_redirect(admin_url('admin.php?page=wpjam-basic'));
	exit;		
}

add_filter('wpjam_theme_setting', function(){
	
	$sections	= [ 
		'icon'	=>[
			'title'		=>'网站图标', 
			'fields'	=>[
				'logo'		=> ['title'=>'网站 LOGO',			'type'=>'img',	'item_type'=>'url',	'size'=>'120*40', 'description'=>'尺寸：120x40'],
				'favicon'	=> ['title'=>'网站 Favicon图标',	'type'=>'img',		'item_type'=>'url'],
			]
		],
		'social'	=>[
			'title'		=>'头部设置', 
			'fields'	=>[
				'header_search'			=> ['title'=>'显示搜索框',		'type'=>'checkbox',],
				'social'			=> ['title'=>'显示社交工具',		'type'=>'checkbox',],
				//'header_weixin_img'		=> ['title'=>'上传微信二维码',		'type'=>'img',		'item_type'=>'url'],
				'header_qq_url'			=> ['title'=>'输入QQ号码',			'type'=>'text',		'rows'=>4],
				'header_weibo_url'		=> ['title'=>'输入微博链接',		'type'=>'text',		'rows'=>4],
				'header_email_url'		=> ['title'=>'输入邮箱账号',		'type'=>'text',		'rows'=>4],
			],	
		],
		'layout'	=>[
			'title'		=>'布局设置', 
			'fields'	=>[
				'banner_area'		=> ['title'=>'首页幻灯片',			'type'=>'checkbox',	'description'=>'幻灯片显示的是置顶文章，【文章 - 快速编辑 - 勾选置顶】或【文章 - 编辑 - 公开度 - 勾选置顶】'],
				'cat_banner_area'		=> ['title'=>'分类页幻灯片',			'type'=>'checkbox',	'description'=>'在分类页面也显示置顶文章'],
				'index_no_cat'		=> ['title'=>'从最新文章中排除',			'type'=>'mu-text',	'description'=>'填入分类ID，从首页最新文章列表中，排除指定分类下的文章。'],
				'popular_area'		=> ['title'=>'热门文章',			'type'=>'checkbox',	'description'=>'显示四篇评论最多的文章，在首页幻灯片下方'],
			]
		],
		'foot_setting'	=>[
			'title'		=>'底部设置', 
			'fields'	=>[
				'footer_icp'		=> ['title'=>'网站备案号',			'type'=>'text',		'rows'=>4],
				'foot_link'			=> ['title'=>'友情链接',			'type'=>'checkbox',	'description'=>'激活“友情链接”，显示在首页底部，在【后台 - 连接】中添加友情链接'],
			],	
		]

	];

	$field_validate	= function($value){
		flush_rewrite_rules();

		return $value;
	};

	return compact('sections', 'field_validate');
});