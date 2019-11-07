<?php
add_filter('wpjam_basic_setting', function(){
	$disabled_fields	= [
		'diable_revision'		=> [
			'title'			=>'屏蔽文章修订',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-post-revision/">屏蔽文章修订功能，精简 Posts 表数据。</a>'
		],
		'disable_trackbacks'	=> [
			'title'			=>'屏蔽Trackbacks',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/bye-bye-trackbacks/">彻底关闭Trackbacks，防止垃圾留言。</a>'
		],
		'disable_emoji'			=> [
			'title'			=>'屏蔽Emoji图片',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/diable-emoji/">屏蔽 Emoji 功能，直接使用支持Emoji文字。</a>'
		],
		'disable_texturize'		=> [
			'title'			=>'屏蔽字符转码',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-wptexturize/">屏蔽字符换成格式化的 HTML 实体功能。</a>'
		],
		'disable_feed'		=> [
			'title'			=>'屏蔽站点Feed',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-feed/">屏蔽站点Feed，防止文章快速被采集。</a>'
		],
		'disable_privacy'		=> [
			'title'			=>'屏蔽后台隐私',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/wordpress-remove-gdpr-pages/">移除后台适应欧洲通用数据保护条例而生成相关的页面</a>。'
		],	
		'disable_auto_update'	=> [
			'title'			=>'屏蔽自动更新',	
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-wordpress-auto-update/">关闭自动更新功能</a>，通过手动或者<a target="_blank" href="https://blog.wpjam.com/article/ssh-wordpress/">SSH方式更新WordPress</a>。'
		],
		'disable_autoembed'		=> [
			'title'			=>'屏蔽Auto Embeds',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-auto-embeds-in-wordpress/">禁用 Auto Embeds 功能，加快页面解析速度。</a>'
		],
		'disable_post_embed'	=> [
			'title'			=>'屏蔽文章Embed',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/diable-wordpress-post-embed/">屏蔽可嵌入其他 WordPress 文章的Embed功能</a>。'
		],
		'diable_block_editor'	=> [
			'title'			=>'屏蔽Gutenberg',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-gutenberg/">屏蔽Gutenberg编辑器，换回经典编辑器</a>。'
		],
		'disable_xml_rpc'		=> [
			'title'			=>'屏蔽XML-RPC',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-xml-rpc/">关闭XML-RPC功能，只在后台发布文章</a>。'
		],
		'disable_rest_api'		=> [
			'title'			=>'屏蔽REST API',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/disable-wordpress-rest-api/">屏蔽REST API功能</a>。WPJAM 出品的小程序没有使用该功能。'
		],
	];

	$speed_fields		= [
		'google_fonts'			=> [
			'title'			=>'Google字体加速',
			'type'			=>'select',
			'options'		=>[''=>'默认Google提供的服务', 'ustc'=>'中科大Google字体加速服务']
		],
		'gravatar'				=> [
			'title'			=>'Gravatar加速',
			'type'			=>'select',
			'options'		=>[''=>'使用Gravatar默认服务器', 'v2ex'=>'使用v2ex镜像加速服务']
		],
		'excerpt_fieldset'		=> [
			'title'			=>'文章摘要优化',
			'type'			=>'fieldset',
			'fields'		=>[
				'excerpt_optimization'	=> [
					'title'			=>'未设摘要：',
					'type'			=>'select',
					'options'		=>[0=>'WordPress 默认方式截取',1=>'按照中文最优方式截取',2=>'直接不显示摘要']
				],
				'excerpt_length'		=> [
					'title'			=>'摘要长度：',
					'type'			=>'number',
					'value'			=>200,
					'description'	=>'<br />中文最优方式是指：<a target="_blank" href="https://blog.wpjam.com/m/get_post_excerpt/">按照中文2个字节，英文1个字节的算法从内容中截取</a>。',
					'class'			=>''
				]
			]
		],
		'frontend_optimization'	=> [
			'title'			=>'前端页面优化',
			'type'			=>'fieldset',
			'fields'		=>[
				'locale'				=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/setup-different-admin-and-frontend-language-on-wordpress/">前台不加载语言包，节省加载语言包所需的0.1-0.5秒。</a>'
				],
				'search_optimization'	=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/redirect-to-post-if-search-results-only-returns-one-post/">当搜索结果只有一篇时直接重定向到文章</a>。'
				],
				'404_optimization'	=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/wpjam_redirect_guess_404_permalink/">改进404页面跳转到正确的页面的效率</a>。'
				],
				'remove_head_links'		=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/remove-unnecessary-code-from-wp_head/">移除页面头部中无关紧要的代码</a>。'
				],
				'remove_admin_bar'		=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/remove-wp-3-1-admin-bar/">移除工具栏和后台个人设置页面工具栏有关的选项。</a>'
				],
				'remove_capital_P_dangit'	=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/remove-capital_p_dangit/">移除WordPress大小写修正，让用户自己决定怎么写。</a>'
				],
			]
		],
			
		'backend_optimization'	=> [
			'title'			=>'后台界面优化',
			'type'			=>'fieldset',
			'fields'		=>[
				'remove_help_tabs'	=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/wordpress-remove-help-tabs/">移除后台界面右上角的帮助。</a>'
				],
				'remove_screen_options'	=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/wordpress-remove-screen-options/">移除后台界面右上角的选项</a>。'
				],
				'no_admin'				=> [
					'title'			=>'',
					'type'			=>'checkbox',
					'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/no-admin-try/">禁止使用 admin 用户名尝试登录 WordPress</a>。'
				]
			]
		]
		
	];

	$enhance_fields		= [
		'no_category_base'	=> [
			'title'			=>'去掉URL中category',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/wordpress-no-category-base/">去掉分类目录 URL 中的 category，简化分类目录固定链接。</a>'
		],
		'timestamp_file_name'	=> [
			'title'			=>'上传图片加上时间戳',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/add-timestamp-2-image-filename/">给上传的图片加上时间戳</a>，防止<a target="_blank" href="https://blog.wpjam.com/m/not-name-1-for-attachment/">大量的SQL查询</a>。'
		],
		'order_by_registered'	=> [
			'title'			=>'用户按注册时间排序',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/order-by-user-registered-time/">后台用户列表按照用户注册时间排序并且显示注册时间</a>。'
		],
		'optimized_by_wpjam'	=>[
			'title'			=>'由WPJAM优化',	
			'type'			=>'checkbox',	
			'description'	=>'在网站底部显示：Optimized by WPJAM Basic。'
		],
		'x-frame-options'	=>[
			'title'			=>'Frame 嵌入支持',	
			'type'			=>'select',	
			'options'		=>[''=>'所有网页', 'DENY'=>'不允许其他网页嵌入本网页', 'SAMEORIGIN'=>'只能是同源域名下的网页']
		],
		'image_default_link_type'	=>[
			'title'			=>'媒体文件默认链接到：',	
			'type'			=>'select',	
			'options'		=>['none'=>'无','file'=>'媒体文件','post'=>'附件页面']
		]
	];

	global $wp_rewrite;

	if($wp_rewrite->use_verbose_page_rules){
		$enhance_fields['no_category_base']['type']		= 'view';
		$enhance_fields['no_category_base']['value']	= '你的固定链接设置使得无法去掉分类目录 URL 中的 category，请先修改固定链接。';
	}

	$sections	= [ 
		'disabled'	=>[
			'title'		=>'功能屏蔽', 
			'summary'	=>'<p>通过下面选项可以关闭 WordPress 中一些不常用的功能来提速。<br />但是注意关闭一些功能会引起一些操作无法执行，详情请点击<a href="https://blog.wpjam.com/m/wpjam-basic-optimization-setting/" target="_blank">优化设置</a>介绍。</p>',
			'fields'	=>$disabled_fields,	
		],
		'speed'	=>[
			'title'		=>'加速优化', 
			'fields'	=>$speed_fields,	
		],
		'enhance'	=>[
			'title'		=>'功能增强', 
			'fields'	=>$enhance_fields,	
		],
	];

	$field_validate	= function($value){
		update_option('image_default_link_type', $value['image_default_link_type']);

		if(!empty($value['disable_auto_update'])){
			wp_clear_scheduled_hook('wp_version_check');
			wp_clear_scheduled_hook('wp_update_plugins');
			wp_clear_scheduled_hook('wp_update_themes');
			wp_clear_scheduled_hook('wp_maybe_auto_update');
		}

		return $value;
	};

	return compact('sections','field_validate');
});

add_action('update_option', function($option){
	if($option == 'wpjam-basic'){
		flush_rewrite_rules();
	}
});

add_action('add_option', function($option){
	if($option == 'wpjam-basic'){
		flush_rewrite_rules();
	}
});

add_action('admin_head', function(){
	?>
	<style type="text/css">
	table.form-table td a{text-decoration: none;}
	</style>
	<script type="text/javascript">
	jQuery(function ($){
		function wpjam_basic_init(){
			if($('#diable_block_editor').is(':checked') && $('#disable_post_embed').is(':checked')){
				$("#disable_rest_api").attr('disabled', false);
			}else{
				$("#disable_rest_api").attr('disabled', true).attr('checked',false);
			}

			if($('#diable_block_editor').is(':checked')){
				$("#disable_xml_rpc").attr('disabled', false);
			}else{
				$("#disable_xml_rpc").attr('disabled', true).attr('checked',false);
			}
		}

		wpjam_basic_init();

		$('#diable_block_editor').on('change', wpjam_basic_init);
		$('#disable_post_embed').on('change', wpjam_basic_init);

		$('#excerpt_optimization').on('change', function(){
			if($(this).val() == 1){
				$('#div_excerpt_length').show();
			}else{
				$('#div_excerpt_length').hide();
			}
		});

		$('#excerpt_optimization').change();
	});
	</script>
	<?php
});
