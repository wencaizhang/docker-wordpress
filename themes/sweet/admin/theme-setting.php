<?php
if(!WPJAM_Verify::verify()){
	wp_redirect(admin_url('admin.php?page=wpjam-basic'));
	exit;		
}

add_action('admin_head',function(){ ?>
	<style type="text/css">
		#navbar_type_options input,#index_type_options input {margin-top: 0px;}
		#navbar_type_options label,#index_type_options label {margin-right: 10px;}
		#tr_none-setting .description{color: #f00}
		<?php
		$theme_setting = wpjam_theme_get_setting('none-setting');
		if( $theme_setting ){?>
			#toplevel_page_wpjam-sweet{display: none}
		<?php }?>
	</style>

	<script type="text/javascript">
	jQuery(function($){
		
		$('body').on('change', '#index_type_options input', function(){
			$('tr#tr_featured_title').show();
			$('tr#tr_featured_post_ids').show();

			if ($(this).is(':checked')) {
				if($(this).val() != 'list'){
					$('tr#tr_featured_title').hide();
					$('tr#tr_featured_post_ids').hide();
				}
			}			
		});

		$('body #index_type_options input').change();
	});
</script>

<?php });

function xintheme_version_update() {
	$theme_info		= wpjam_remote_request('http://www.xintheme.com/api?id=31944');
	$version		= $theme_info['new_version'];
	$current_theme	= wp_get_theme();
	
	if( $version > $current_theme->get( 'Version' ) ){
		$version_update = '<p>主题有更新，请前往 <a href="/wp-admin/themes.php">主题</a> 页面进行更新，或 <a href="'.$theme_info['url'].'" target="_blank">查看主题介绍页面</a> 。</p>
		<p>最新版本：V '.$version.'，当前使用版本：V '.$current_theme->get( 'Version' ).'</p>';
	}else{
		$version_update = '<p>你的主题目前已经是最新版了！</p>';
	}
	return $version_update;
}

add_filter('wpjam_theme_setting', function(){
	$sections	= [ 
		'icon'	=>[
			'title'		=>'网站图标', 
			'fields'	=>[
				'logo'		=> ['title'=>'网站 LOGO',		'type'=>'img',	'item_type'=>'url',	'size'=>'175*40',	'description'=>'建议尺寸：175x40'],
				'favicon'	=> ['title'=>'Favicon',			'type'=>'img',	'item_type'=>'url',	'size'=>'32*32',	'description'=>'建议尺寸：32x32'],
				'apple-icon'=> ['title'=>'Apple-touch-icon','type'=>'img',	'item_type'=>'url',	'size'=>'256*256',	'description'=>'建议尺寸：256x256'],
				'thumbnails'=> ['title'=>'默认缩略图',	'type'=>'mu-img',	'item_type'=>'url', 'description'=>'文章没有设置缩略图也没有图片时候，使用该图中的随机一张。<br />建议尺寸：420*260 px，会覆盖CDN加速设置中的默认缩略图设置。'],
				'bg_img'	=>['title'=>'背景图像',		'type'=>'img','item_type'=>'url','description'=>'网站默认调用第一篇文章的缩略图为背景图像，此处上传背景图像，将优先显示。'],
			]
		],
		'layout'	=>[
			'title'		=>'布局设置', 
			'fields'	=>[
				'navbar_type'		=> ['title'=>'导航栏样式','type'=>'radio','options'=>['center'=>'居中','left'=>'靠左','right'=>'靠右']],
				'index_type'		=> ['title'=>'首页样式',	'type'=>'radio','options'=>['list'=>'列表','grid'=>'网格'],'description'=>'选择【列表】请到 “后台 - 设置 - 阅读 - 博客页面至多显示” 设置为：3<br>选择【网格】请到 “后台 - 设置 - 阅读 - 博客页面至多显示” 设置为：10'],
				'featured_title'	=> ['title'=>'推荐阅读标题','type'=>'text'],
				'featured_post_ids'	=> ['title'=>'推荐阅读文章','type'=>'mu-text',	'data_type'=>'post_type',	'post_type'=>'post',	'total'=>3,	'placeholder'=>'请输入文章ID或者关键字进行筛选',	'description'=>'显示在首页和分类页底部'],
			]
		],
		'foot_setting'	=>[
			'title'		=>'页脚设置', 
			'fields'	=>[
				'foot_copyright'	=>['title'=>'自定义页脚版权信息',	'type'=>'textarea'],
				//'foot_link'			=> ['title'=>'友情链接',			'type'=>'checkbox',	'description'=>'激活“友情链接”，显示在首页底部，在【后台 - 连接】中添加友情链接'],
				'footer_icp'		=> ['title'=>'网站备案号',			'type'=>'text',		'rows'=>4],
				'foot_timer'		=> ['title'=>'页面加载时间',		'type'=>'checkbox',	'description'=>'页脚显示当前页面加载时间'],
			],	
		],
		'extend'	=>[
			'title'		=>'扩展选项', 
			'summary'	=>'<p>下面的选项，可以让你选择性显示或关闭一些功能。</p>',
			'fields'	=>[
				'xintheme_v2ex'		=> ['title'=>'Gravatar镜像服务',		'type'=>'checkbox',	'description'=>'使用国内的Gravatar镜像服务，提高网站加载速度，https://cdn.v2ex.com/gravatar'],
				'xintheme_feed'		=> ['title'=>'关闭Feed',				'type'=>'checkbox',	'description'=>'Feed易被利用采集，造成不必要的资源消耗，建议关闭'],
				'none-setting'	=> ['title'=>'隐藏主题设置菜单','type'=>'checkbox',	'description'=>'【高危选项】此选项极度危险，小白请勿操作！<br><br>通过css隐藏主题设置选项，此选项专为强迫症患者设计，隐藏菜单后可直接通过访问：<br>'.admin_url('admin.php?page=wpjam-sweet').' 打开主题设置面板'],
			],	
		],

		'support'	=>[
			'title'		=>'主题支持',
			'fields'	=>[],
			'summary'	=>'
			<p>在使用过程中遇到任何问题都可以在网站后台的「<a href="/wp-admin/admin.php?page=wpjam-basic-topics">讨论组</a>」中发布问题，看到了就会回复你。</p>
			<p>如果你是个小白，什么都不会的那种，我们提供「<a href="https://wpjam.com/speed/">服务器优化以及主题安装调试服务</a>」。</p>
			<h2>小程序</h2>
			<p>Sweet主题对应的小程序已经开始在开发，定价为<strong>300</strong>元一个域名授权。<br />预计月底上线，现预售<strong>150</strong>一个域名授权。</p>
			<p>Sweet小程序详细介绍和参与预售，请点击：<br /><a href="https://mp.weixin.qq.com/s/1njmO9I13fNnaMq3hKOklA">https://mp.weixin.qq.com/s/1njmO9I13fNnaMq3hKOklA</a>。</p>
			<h2>常见问题</h2>
			<p><b>问：</b>评论框以及后台登陆界面显示英文？</p>
			<p><b style="color: #e12020;">答：</b>如果你勾选了插件里面的【前台不加载语言包】评论和后台登陆界面就会显示英文。</p>
			<p><b>问：</b>小工具怎么显示不出来了？</p>
			<p><b style="color: #e12020;">答：</b>插件的优化设置里面有个【主题 Widget】的选项，不要勾选就好了。</p>
			<p><b>问：</b>这个主题不能自定义SEO设置？</p>
			<p><b style="color: #e12020;">答：</b>【WPJAM插件 - 扩展管理 - SEO】勾选即可。</p>
			<h2>版权说明</h2>
			<p>禁止删除网站页脚的链接（Powered by XinTheme + WordPress 果酱）。<br />这是我们持续更新免费主题的动力，请支持我们！</p>
			<h2>检查更新</h2>'
			.xintheme_version_update()

		],

	];

	$field_validate	= function($value){
		flush_rewrite_rules();

		return $value;
	};

	return compact('sections', 'field_validate');
});