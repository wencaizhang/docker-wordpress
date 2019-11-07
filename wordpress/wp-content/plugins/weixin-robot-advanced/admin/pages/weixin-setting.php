<?php
add_filter('wpjam_weixin_robot_setting', function(){
	$sections		= [];

	$weixin_fields	= [
		'weixin_type'			=> ['title'=>'公众号类型',		'type'=>'select',	'options'=>['-1'=>' ','1'=>'订阅号','2'=>'服务号','2.5'=>'认证订阅号（微博认证）','3'=>'认证订阅号','4'=>'认证服务号']],
		'weixin_app_id'			=> ['title'=>'AppID(应用ID)',	'type'=>'text',		'required',	'class'=>'all-options'],
		'weixin_app_secret'		=> ['title'=>'Secret(应用密钥)',	'type'=>'text',		'required'],
	];

	if(!current_user_can('manage_options')){
		unset($weixin_fields['weixin_app_id']);
		unset($weixin_fields['weixin_app_secret']);
	}

	$sections['weixin']	= ['title'=>'微信设置',	'fields'=>$weixin_fields];

	if(weixin_get_appid()){
		$features_fields	= [
			'weixin_reply'	=> ['title'=>'自定义回复',	'type'=>'checkbox',	'description'=>'开启自定义回复功能，在公众号后台自定义公众号关键字回复。'],
			'weixin_dkf'	=> ['title'=>'客服功能',		'type'=>'checkbox',	'description'=>'请首先在 <strong>微信公众号后台</strong> &gt; <strong>添加功能插件</strong> 中添加<strong>客服功能</strong>。']
		];

		$sections['features']	= ['title'=>'功能设置',	'fields'=>$features_fields];

		$reply_fields	= [
			'weixin_url'			=> ['title'=>'URL(服务器地址)',	'type'=>'view',		'value'=>home_url('/weixin/reply/')],
			// 'weixin_message_mode'=> ['title'=>'消息加解密方式',	'type'=>'select',	'options'=>['1'=>'明文模式','2'=>'兼容模式','3'=>'安全模式（推荐）']],
			'weixin_message_mode'	=> ['title'=>'消息加解密方式',	'type'=>'view',		'value'=>'请在微信公众号后台选用<strong>安全模式</strong>。'],
			'weixin_token'			=> ['title'=>'Token(令牌)',		'type'=>'text',		'class'=>'all-options'],
			'weixin_encodingAESKey'	=> ['title'=>'EncodingAESKey',	'type'=>'text',		'description'=>'请输入兼容或者安全模式下的消息加解密密钥'],
			'weixin_keyword_allow_length'	=> ['title'=>'搜索关键字最大字节',	'type'=>'number',	'class'=>'small-text',	'description'=>'一个汉字算两个字节，一个英文单词算两个字节，空格不算，搜索多个关键字可以用空格分开！',	'min'=>8,	'max'=>20,	'step'=>2,	'value'=>10],
			'weixin_search'			=> ['title'=>'博客文章搜索',		'type'=>'checkbox',	'description'=>'开启<strong>博客文章搜索</strong>，在自定义回复和内置回复没有相关的关键字，微信机器人会去搜索博客文章。'],
			'weixin_search_url'		=> ['title'=>'图文链接地址',		'type'=>'checkbox',	'description'=>'搜索结果多余一篇文章跳转搜索结果页面或者分类/标签列表页。'],
			
			// 'weixin_force_subscribe_url'	=> ['title'=>'未关注强制跳转',	'type'=>'url',		'description'=>'在博客任意链接后面加上<code>?weixin_force_subscribe</code>，就会跳转到该链接。']
		];

		if(!current_user_can('manage_options')){
			unset($reply_fields['weixin_token']);
			unset($reply_fields['weixin_encodingAESKey']);
		}

		$sections['reply']	= ['title'=>'回复设置',	'fields'=>$reply_fields];

		$sections	= apply_filters('weixin_setting', $sections);

		// $site_fields = [
		// 	// 'weixin_count'					=> ['title'=>'文章图文最大条数',		'type'=>'range',	'min'=>1,	'max'=>8,	'step'=>1], 
		// 	// 'weixin_content_wrap'			=> ['title'=>'开启文章图片预览',		'type'=>'text',		'class'=>'all-options',	'description'=>'输入文章内容所在DIV的class或者ID，留空则不启用该功能'],
		// 	// 'weixin_hide_option_menu'		=> ['title'=>'全局隐藏右上角菜单',	'type'=>'checkbox',	'description'=>'全局隐藏微网站右上角按钮']
		// ];
	}
	
	$ajax		= false;

	return compact('sections', 'ajax');	
});

add_action('add_option_weixin-robot', 'weixin_activation');
add_action('update_option_weixin-robot', 'weixin_activation');

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('#tr_weixin_dkf').hide();

		$('body').on('change', '#weixin_type', function(){
			if($(this).val() >= 3){
				$('#tr_weixin_dkf').show();
			}else{
				$('#tr_weixin_dkf').hide();
			}
		});

		$('body').on('change', '#weixin_reply', function(){
			if($('input#weixin_reply:checked').val()){
				$('#tab_title_reply').show();
			}else{
				$('#tab_title_reply').hide();
			}
		});

		$('body').on('change', '#weixin_search', function(){
			if($('input#weixin_search:checked').val()){
				$('#tr_weixin_search_url').show();
			}else{
				$('#tr_weixin_search_url').hide();
			}
		});

		$('body #weixin_type').change();
		$('body input#weixin_reply').change();
	});
	</script>
	<?php
});


