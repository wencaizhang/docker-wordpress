<?php
add_filter('wpjam_content_template_setting', function(){
	$tip	= '扫码关注公众号，回复「[keyword]」获取文章密码。';
	$reply	= '密码是： [password]';
	$style	= '
div.post-password-content-template{margin-bottom: 20px; padding:10px; background: #EDF3DE;}

form.content-template-post-password-form:after{display: block; content: " "; clear: both;}

form.content-template-post-password-form img{float: left; margin-right:10px;}

form.content-template-post-password-form input[type="password"]{ border: 1px solid #EDE8E2; padding: 6px;}

form.content-template-post-password-form input[type="submit"]{padding: 8px; background: #1BA6B2; color: #fff; border: 0; text-shadow: none; line-height: 1;}';

	$card_style	= '
.card-content-template { border: 1px solid #ddd; padding: 10px; border-radius:4px; box-sizing: border-box; min-height:122px; margin-bottom: 20px; box-shadow:0 0 6px 0 #999;}

.card-content-template:after{ content:" "; clear:both; }

.card-content-template .card-thumbnail{float:left; margin: 0 10px 10px 0;}

.card-content-template .card-title{font-size:16px; margin: 0; line-height:1.5;}

.card-content-template .card-except{font-size:14px; margin: 10px 0; overflow: hidden; white-space: nowrap;  text-overflow:ellipsis}

.card-content-template .card-price{float:left; font-weight:bold;}

.card-content-template .card-button{ background: #8d4fdb; color: #fff; float: right; margin-right:4px; padding: 2px 4px; border-radius: 4px;}
';

	$post_password_fields	= [
		'weixin_qrcode'	=> ['title'=>'公众号二维码图片',	'type'=>'img',		'item_type'=>'url',	'size'=>'160x160'],
		'weixin_tip'	=> ['title'=>'扫码提示文本',		'type'=>'textarea',	'rows'=>3,	'value'=>$tip,		'class'=>'',	'description'=>'<br />使用[keyword]代替回复关键字'],
		'weixin_reply'	=> ['title'=>'自定义回复文本',	'type'=>'textarea',	'rows'=>3,	'value'=>$reply,	'class'=>'',	'description'=>'<br />使用[password]代替文章密码'],
		'weixin_style'	=> ['title'=>'前端样式',			'type'=>'textarea',	'rows'=>6,	'value'=>$style,	'class'=>'',	'description'=>'<br />也可留空，然后修改主题的样式文件']
	];

	$card_template_fields	= [
		'weapps'		=> ['title'=>'跳转小程序',	'type'=>'mu-fields',	'description'=>'请输入跳转小程序 APPID，最多10个，同时也要加到小程序中。',	'fields'=>[
			'name'	=> ['title'=>'名称',		'type'=>'text',	'class'=>'all-options'],
			'appid'	=> ['title'=>'APPID',	'type'=>'text',	'class'=>'all-options'],
		]],
		'card_style'	=> ['title'=>'卡片样式',		'type'=>'textarea',	'rows'=>6,	'value'=>$card_style,	'class'=>'',	'description'=>'<br />也可留空，然后修改主题的样式文件']
	];

	$sections	= [ 
		'post_password'	=>[
			'title'		=>'密码保护', 
			'summary'	=>'<p>设置了密码保护的内容模板，可以通过自定义回复获取密码。</p>',
			'fields'	=>$post_password_fields,	
		],
		'card'	=>[
			'title'		=>'卡片模板', 
			'fields'	=>$card_template_fields,	
		]
	];

	if(!defined('WEIXIN_ROBOT_PLUGIN_DIR')){
		unset($sections['post_password']);
	}

	$template_types	= wpjam_get_content_template_types();

	if(!isset($template_types['card'])){
		unset($sections['card']);
	}else{
		if(!defined('WEAPP_PLUGIN_DIR')){
			unset($sections['card']['fields']['weapps']);
		}
	}	

	$capability		= 'manage_options';

	return compact('sections', 'capability');
});

add_action('update_option', function(){
	delete_transient('weixin_builtin_replies');
});