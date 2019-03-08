<?php
add_filter('wpjam_basic_setting', function (){
	$site_url = parse_url( site_url() );
	$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
	$seo_robots	= '';
	$seo_robots	.= "User-agent: *\n";
	$seo_robots	.= "Disallow: /wp-admin/\n";
	$seo_robots	.= "Disallow: /wp-includes/\n";
	$seo_robots	.= "Disallow: /cgi-bin/\n";
	$seo_robots	.= "Disallow: $path/wp-content/plugins/\n";
	$seo_robots	.= "Disallow: $path/wp-content/themes/\n";
	$seo_robots	.= "Disallow: $path/wp-content/cache/\n";
	$seo_robots	.= "Disallow: $path/author/\n";
	$seo_robots	.= "Disallow: $path/trackback/\n";
	$seo_robots	.= "Disallow: $path/feed/\n";
	$seo_robots	.= "Disallow: $path/comments/\n";
	$seo_robots	.= "Disallow: $path/search/\n";

	$summary	= '
	<ol>
		<li>可以设置首页的TDK。</li>
		<li>如果没有单独设置，自动获取文章摘要作为文章页面的 Meta Description，可以将文章页面的 Tag 作为 Meta Keywords。</li>
		<li>如果没有单独设置，自动获取分类和 Tag 的描述作为分类和 Tag 页面的 Meta Description。</li>
		<li>如果博客支持并开启固定链接，自动生成 <a href="'.home_url('/robots.txt').'" target="_blank">robots.txt</a> 和 <a href="'.home_url('/sitemap.xml').'" target="_blank">sitemap.xml</a>。</li>
	</ol>';

	$sections = [ 
		'setting'	=> [
			'title'		=>'SEO设置',	
			'summary'	=>$summary,
			'fields'	=>[
				'seo_individual'	=> ['title'=>'独立设置',		'type'=>'checkbox', 'description'=>'文章页面和分类页面独立的 SEO TDK 设置。'],
				'seo_robots'		=> ['title'=>'robots.txt',	'type'=>'textarea',	'class'=>'regular-text',	'rows'=>10,	'value'=>$seo_robots,	'description'=>'如果博客的根目录下已经有 robots.txt 文件，请先删除，否则这里设置的无法生效。'],
			]
		],
		'home'		=> [
			'title'		=>'首页设置',	
			'fields'	=>[
				'seo_home_title'		=> ['title'=>'首页 SEO 标题',	'type'=>'text'],
				'seo_home_description'	=> ['title'=>'首页 SEO 描述',	'type'=>'textarea', 'rows'=>4],
				'seo_home_keywords'		=> ['title'=>'首页 SEO 关键字',	'type'=>'text' ],
			]
		],
	];

	if(!is_multisite() || (is_multisite() && !is_network_admin())){
		if($post_types = get_post_types(['public'=> true, 'has_archive'=>true],'objects')){
			foreach ($post_types as $post_type) {
				$post_type_object = get_post_type_object($post_type);
				if(!empty($post_type_object->seo_meta_box) || $post_type == 'post'){
					$post_type_fields = [
						'seo_'.$post_type->name.'_title'		=> ['title'=>$post_type->label.' SEO 标题',		'type'=>'text'],
						'seo_'.$post_type->name.'_description'	=> ['title'=>$post_type->label.' SEO 描述',		'type'=>'textarea', 'rows'=>4],
						'seo_'.$post_type->name.'_keywords'		=> ['title'=>$post_type->label.' SEO Keywords',	'type'=>'text'],
					];

					$sections[$post_type->name.'-seo']	= ['title'=>$post_type->label, 'fields'=>$post_type_fields];
				}
			}
		}
	}

	$sections['sitemap']	= [
		'title'		=>'Sitemap',	
		'fields'	=>[
			'sitemap'	=> [
				'title'	=>'',
				'type'	=>'view',	
				'value'	=>'
				<table>
					<tr><td style="padding:0 10px 8px 0;">首页/分类/标签：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap.xml').'" target="_blank">'.home_url('/sitemap.xml').'</a></td></tr>
					<tr><td style="padding:0 10px 8px 0;">前1000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-1.xml').'" target="_blank">'.home_url('/sitemap-1.xml').'</a></td></tr>
					<tr><td style="padding:0 10px 8px 0;">1000-2000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-2.xml').'" target="_blank">'.home_url('/sitemap-2.xml').'</a></td></tr>
					<tr><td style="padding:0 10px 8px 0;" colspan=2>以此类推...</a></td></tr>
				</table>
				'
			]
		]
	];
	
	return compact('sections');
});
