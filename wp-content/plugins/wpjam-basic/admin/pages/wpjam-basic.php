<?php
add_filter('default_option_wpjam-basic', 'wpjam_basic_get_default_settings');

add_filter('wpjam_basic_setting', function(){
	$disabled_fields	= [
		'diable_revision'		=> [
			'title'			=>'屏蔽文章修订',
			'type'			=>'checkbox',
			'description'	=>'文章修订会在 Posts 表中插入多条历史数据，造成 Posts 表冗余，建议<a target="_blank" href="http://blog.wpjam.com/m/disable-post-revision/">屏蔽文章修订功能</a>，提高数据库效率。'
		],
		'disable_trackbacks'	=> [
			'title'			=>'屏蔽Trackbacks',
			'type'			=>'checkbox',
			'description'	=>'Trackbacks协议被滥用，会给博客产生大量垃圾留言，建议<a target="_blank" href="http://blog.wpjam.com/m/bye-bye-trackbacks/">彻底关闭Trackbacks</a>。'
		],
		'disable_emoji'			=> [
			'title'			=>'屏蔽Emoji图片',
			'type'			=>'checkbox',
			'description'	=>'WordPress使用图片来渲染Emoji表情文字，但是WordPress使用的渲染图片在国外，经常打不开，并且现在主流浏览器都已经支持Emoji文字，建议<a target="_blank" href="http://blog.wpjam.com/m/diable-emoji/">屏蔽 Emoji 功能</a>。'
		],
		'disable_widgets'		=> [
			'title'			=>'屏蔽主题Widget',
			'type'			=>'checkbox',
			'description'	=>'如果你只是把WordPress当做后端服务，或者你的主题也没有使用widget，那么主题的widget功能是无用的，建议<a target="_blank" href="http://blog.wpjam.com/m/wpjam-disable-widget/">彻底取消Widget</a>，加快页面渲染。'
		],	
		'disable_privacy'		=> [
			'title'			=>'屏蔽后台隐私',
			'type'			=>'checkbox',
			'description'	=>'GDPR（General Data Protection Regulation）是欧洲的通用数据保护条例，WordPress为了适应该法律，在后台设置很多隐私功能，如果只是在国内运营博客，可以<a target="_blank" href="http://blog.wpjam.com/m/wordpress-remove-gdpr-pages/">移除后台隐私相关的页面</a>。'
		],	
		'disable_auto_update'	=> [
			'title'			=>'屏蔽自动更新',	
			'type'			=>'checkbox',
			'description'	=>'WordPress更新服务器在国外，经常无法打开，<a target="_blank" href="http://blog.wpjam.com/m/disable-wordpress-auto-update/">建议关闭 WordPress 后台和自动更新功能</a>，通过手动或者<a target="_blank" href="https://blog.wpjam.com/article/ssh-wordpress/">SSH方式更新WordPress</a>。'
		],
		'disable_autoembed'		=> [
			'title'			=>'屏蔽Auto Embeds',
			'type'			=>'checkbox',
			'description'	=>'Auto Embeds协议让你插入一个视频网站或者图片分享网站的链接，这个链接里面含有的视频或者图片就自动显示出来。但是该功能支持的网站都是国外的，建议<a target="_blank" href="http://blog.wpjam.com/m/disable-auto-embeds-in-wordpress/">禁用 Auto Embeds 功能</a>，加快页面解析速度。'
		],
		'disable_post_embed'	=> [
			'title'			=>'屏蔽文章Embed',
			'type'			=>'checkbox',
			'description'	=>'文章Embed功能让你可以在WordPress站点用嵌入的方式插入本站或者其他站点的WordPress文章。如果你不需要，可以<a target="_blank" href="http://blog.wpjam.com/m/diable-wordpress-post-embed/">屏蔽文章Embed功能</a>。'
		],
		'diable_block_editor'	=> [
			'title'			=>'屏蔽Gutenberg',
			'type'			=>'checkbox',
			'description'	=>'Gutenberg编辑器很酷，不过很多人不习惯，并且对自定义字段支持不够完善，WPJAM Basic 会在使用了自定义字段的文章类型恢复默认编辑器，当然也可以直接彻底<a target="_blank" href="http://blog.wpjam.com/m/disable-gutenberg/">屏蔽Gutenberg编辑器</a>。'
		],
		'disable_xml_rpc'		=> [
			'title'			=>'屏蔽XML-RPC',
			'type'			=>'checkbox',
			'description'	=>'XML-RPC协议用于客户端发布文章，如果你只是在后台发布，可以<a target="_blank" href="http://blog.wpjam.com/m/disable-xml-rpc/">关闭XML-RPC功能</a>。Gutenberg编辑器需要XML-RPC功能。'
		],
		'disable_rest_api'		=> [
			'title'			=>'屏蔽REST API',
			'type'			=>'checkbox',
			'description'	=>'REST API可以生成接口制作小程序或者APP，<strong>Gutenberg编辑器</strong>和<strong>文章Embed</strong>需要REST API，如果你没有使用这些功能，建议<a target="_blank" href="http://blog.wpjam.com/m/disable-wordpress-rest-api/">屏蔽REST API功能</a>。WPJAM 出品的小程序或者APP，没有使用该功能。'
		],
	];

	$remove_fields		= [
		'remove_head_links'		=> [
			'title'			=>'移除头部代码',
			'type'			=>'checkbox',
			'description'	=>'WordPress会在页面的头部输出了一些<code>link</code>和<code>meta</code>标签代码，这些代码没什么作用，并且存在安全隐患，建议<a target="_blank" href="http://blog.wpjam.com/m/emove-unnecessary-code-from-wp_head/">移除WordPress页面头部中无关紧要的代码</a>。'
		],
		'remove_admin_bar'		=> [
			'title'			=>'移除工具栏',
			'type'			=>'checkbox',
			'description'	=>'WordPress用户登陆的情况下会出现Admin Bar，让你可以进行一些快速操作，但是你的主题已经实现了相关功能或者你不需要，可以全局<a target="_blank" href="http://blog.wpjam.com/m/remove-wp-3-1-admin-bar/">移除工具栏</a>，所有人包括管理员都看不到，并且个人页面关于工具栏的选项也一起移除。'
		],
		'remove_capital_P_dangit'	=> [
			'title'			=>'移除WordPress大小写修正',
			'type'			=>'checkbox',
			'description'	=>'WordPress默认会把 Wordpress 这样的写法中的 P 从小写改成大写，如果你觉得没有必要，可以<a target="_blank" href="https://blog.wpjam.com/m/remove-capital_p_dangit/">移除WordPress大小写修正</a>，让用户自己决定怎么写。'
		],
		'no_admin'				=> [
			'title'			=>'禁止admin用户名',
			'type'			=>'checkbox',
			'description'	=>'使用admin作为用户名是最大的安全漏洞，建议<a target="_blank" href="http://blog.wpjam.com/m/no-admin-try/">禁止使用 admin 用户名尝试登录 WordPress</a>，提高网站的安全性。'
		],
		'locale'				=> [
			'title'			=>'前台不加载语言包',
			'type'			=>'checkbox',
			'description'	=>'WordPress加载语言包是需要花费 0.1-0.5 秒不等的时间，如果对性能要求极致，可以<a target="_blank" href="http://blog.wpjam.com/m/setup-different-admin-and-frontend-language-on-wordpress/">前台不加载语言包</a>，但是要把主题文件中的描述改成中文。'
		],
	];

	$enhance_fields		= [
		'no_category_base'	=> [
			'title'			=>'去掉URL中category',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/wordpress-no-category-base/">去掉分类目录 URL 中的 category</a>，简化分类目录固定链接。'
		],
		'timestamp_file_name'	=> [
			'title'			=>'上传图片加上时间戳',
			'type'			=>'checkbox',
			'description'	=>'<a target="_blank" href="https://blog.wpjam.com/m/add-timestamp-2-image-filename/">给上传的图片加上时间戳</a>，防止<a target="_blank" href="https://blog.wpjam.com/m/not-name-1-for-attachment/">大量的SQL查询</a>。'
		],
		'excerpt_optimization'	=> [
			'title'			=>'文章摘要优化',
			'type'			=>'checkbox',
			'description'	=>'在没有设置文章摘要的情况下，<a target="_blank" href="https://blog.wpjam.com/m/get_post_excerpt/">按照中文2个字节，英文1个字节的模式从文章内容中截取摘要</a>。'
		],
		'search_optimization'	=> [
			'title'			=>'搜索结果优化',
			'type'			=>'checkbox',
			'description'	=>'提高搜索效率，<a target="_blank" href="https://blog.wpjam.com/m/redirect-to-post-if-search-results-only-returns-one-post/">当搜索结果只有一篇时直接重定向到文章</a>。'
		],
		'404_optimization'	=> [
			'title'			=>'404跳转优化',
			'type'			=>'checkbox',
			'description'	=>'在多个文章类型的情况下，<a target="_blank" href="https://blog.wpjam.com/m/wpjam_redirect_guess_404_permalink/">改进404页面跳转到正确的页面的效率</a>。'
		],
		'order_by_registered'	=> [
			'title'			=>'用户按注册时间排序',
			'type'			=>'checkbox',
			'description'	=>'WordPress后台默认用户是按照用户名排序的，可以设置成后台用户列表<a target="_blank" href="http://blog.wpjam.com/m/order-by-user-registered-time/">按照用户注册时间排序并且显示注册时间</a>。'
		],
		'strict_user'		=> [
			'title'			=>'严格用户模式',
			'type'			=>'checkbox',
			'description'	=>'严格用户模式下，昵称和显示名称都是唯一的，并且用户名中不允许出现非法关键词。<br ><small>非法关键词是指在 <strong>设置</strong> &amp; <strong>讨论</strong> 中 <code>评论审核</code> 和 <code>评论黑名单</code> 中定义的</small>。'
		],
		'simplify_user'	=> [
			'title'			=>'简化后台用户界面',
			'type'			=>'checkbox',
			'description'	=>'移除后台个人资料和编辑用户页面姓氏，名字，显示名字，邮箱，描述等字段。'
		],
		'show_all_setting'		=> [
			'title'			=>'所有设置',
			'type'			=>'checkbox',
			'description'	=>'在设置菜单下面显示<strong>所有设置</strong>子菜单。'
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
			'summary'	=>'<p>通过下面选项可以关闭 WordPress 中一些不常用的功能来提速，但是注意关闭一些功能会引起一些操作无法执行，详情请点击<a href="https://blog.wpjam.com/m/wpjam-basic-optimization-setting/" target="_blank">优化设置</a>介绍。</p>',
			'fields'	=>$disabled_fields,	
		],
		'remove'	=>[
			'title'		=>'清理优化', 
			'fields'	=>$remove_fields,	
		],
		'enhance'	=>[
			'title'		=>'功能增强', 
			'fields'	=>$enhance_fields,	
		],
	];

	$field_validate	= function($value){
		update_option('image_default_link_type',$value['image_default_link_type']);

		if(!empty($value['disable_auto_update'])){
			wp_clear_scheduled_hook('wp_version_check');
			wp_clear_scheduled_hook('wp_update_plugins');
			wp_clear_scheduled_hook('wp_update_themes');
			wp_clear_scheduled_hook('wp_maybe_auto_update');
		}

		flush_rewrite_rules();

		return $value;
	};

	return compact('sections','field_validate');
});

add_action('admin_head', function(){
	?>
	<style type="text/css">
	table.form-table td label{max-width: 700px;display: inline-block;}
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
	});
	</script>
	<?php
});
