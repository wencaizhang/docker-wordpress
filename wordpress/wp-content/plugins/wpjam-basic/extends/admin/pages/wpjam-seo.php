<?php
add_filter('wpjam_basic_setting', function (){
	$site_url = parse_url( site_url() );
	$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';

	if(file_exists(ABSPATH.'robots.txt')){
		$robots_type	= 'view';
		$robots_value	= '博客的根目录下已经有 robots.txt 文件。<br />请直接编辑或者删除之后在后台自定义。';
	}else{
		$robots_type	= 'textarea';
		$robots_value	= "User-agent: *
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /cgi-bin/
Disallow: $path/wp-content/plugins/
Disallow: $path/wp-content/themes/
Disallow: $path/wp-content/cache/
Disallow: $path/author/
Disallow: $path/trackback/
Disallow: $path/feed/
Disallow: $path/comments/
Disallow: $path/search/";
	}
	
	if(file_exists(ABSPATH.'sitemap.xml')){
		$sitemap_value	= '博客的根目录下已经有 sitemap.xml 文件。<br />删除之后才能使用插件自动生成的 sitemap.xml。';
	}else{
		$sitemap_value	= '<table>
			<tr><td style="padding:0 10px 8px 0;">首页/分类/标签：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap.xml').'" target="_blank">'.home_url('/sitemap.xml').'</a></td></tr>
			<tr><td style="padding:0 10px 8px 0;">前1000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-1.xml').'" target="_blank">'.home_url('/sitemap-1.xml').'</a></td></tr>
			<tr><td style="padding:0 10px 8px 0;">1000-2000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-2.xml').'" target="_blank">'.home_url('/sitemap-2.xml').'</a></td></tr>
			<tr><td style="padding:0 10px 8px 0;" colspan=2>以此类推...</a></td></tr>
		</table>';
	}

	$post_type_options	= wp_list_pluck(get_post_types(['show_ui'=>true,'public'=>true], 'objects'), 'label', 'name');
	$taxonomy_options	= wp_list_pluck(get_taxonomies(['show_ui'=>true,'public'=>true], 'objects'), 'label', 'name');

	unset($post_type_options['attachment']);

	$individual_options	= [0=>'文章和分类页自动获取摘要和关键字',1=>'文章和分类页单独的 SEO TDK 设置。'];
	$auto_view			= '文章摘要作为页面的 Meta Description，文章的标签作为页面的 Meta Keywords。<br />
	分类和标签的描述作为页面的 Meta Description，页面没有 Meta Keywords。';

	$sections = [ 
		'setting'	=> [
			'title'		=>'SEO设置',	
			'fields'	=>[
				'seo_individual'	=> ['title'=>'SEO设置',		'type'=>'select', 	'options'=>$individual_options],
				'auto'				=> ['title'=>'自动获取规则',	'type'=>'view', 	'value'=>$auto_view],
				'individual'		=> ['title'=>'单独设置支持',	'type'=>'fieldset', 'fields'=>[
					'seo_post_types'	=> ['title'=>'文章类型','type'=>'checkbox',	'options'=>$post_type_options,	'value'=>['post']],
					'seo_taxonomies'	=> ['title'=>'分类模式','type'=>'checkbox',	'options'=>$taxonomy_options,	'value'=>['category']],
				]],	
				'seo_robots'		=> ['title'=>'robots.txt',	'type'=>$robots_type,	'class'=>'',	'rows'=>10,	'value'=>$robots_value],
				'seo_sitemap'		=> ['title'=>'Sitemap',		'type'	=>'view',	'value'=>$sitemap_value]
			]
		],
		'home'		=> [
			'title'		=>'首页设置',	
			'fields'	=>[
				'seo_home_title'		=> ['title'=>'SEO 标题',		'type'=>'text'],
				'seo_home_description'	=> ['title'=>'SEO 描述',		'type'=>'textarea', 'class'=>''],
				'seo_home_keywords'		=> ['title'=>'SEO 关键字',	'type'=>'text' ],
			]
		],
	];

	// if(!is_multisite() || (is_multisite() && !is_network_admin())){
	// 	if($post_types = get_post_types(['public'=> true, 'has_archive'=>true],'objects')){
	// 		foreach ($post_types as $post_type) {
	// 			$post_type_object = get_post_type_object($post_type);
	// 			// if(!empty($post_type_object->seo_meta_box) || $post_type == 'post'){
	// 				$post_type_fields = [
	// 					'seo_'.$post_type->name.'_title'		=> ['title'=>$post_type->label.' SEO 标题',		'type'=>'text'],
	// 					'seo_'.$post_type->name.'_description'	=> ['title'=>$post_type->label.' SEO 描述',		'type'=>'textarea', 'class'=>''],
	// 					'seo_'.$post_type->name.'_keywords'		=> ['title'=>$post_type->label.' SEO Keywords',	'type'=>'text'],
	// 				];

	// 				$sections[$post_type->name.'-seo']	= ['title'=>$post_type->label, 'fields'=>$post_type_fields];
	// 			// }
	// 		}
	// 	}
	// }
	
	return compact('sections');
});

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function ($){
		$('#seo_individual').on('change', function(){
			if($(this).val() == 1){
				$('#tr_auto').hide();
				$('#tr_individual').show();
			}else{
				$('#tr_auto').show();
				$('#tr_individual').hide();
			}
		});

		$('#seo_individual').change();
	});
	</script>
	<?php
});
