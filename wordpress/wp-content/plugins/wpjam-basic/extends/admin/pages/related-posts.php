<?php
add_filter('wpjam_basic_setting', function(){
	$post_type_options	= wp_list_pluck(get_post_types(['show_ui'=>true,'public'=>true], 'objects'), 'label', 'name');

	unset($post_type_options['attachment']);

	$sections	= [
		'related-posts'	=> [
			'title'		=>'', 
			'fields'	=>[
				'related_posts_title'		=> ['title'=>'标题',		'type'=>'text',		'value'=>'相关文章',	'class'=>'all-options',	'description'=>'相关文章列表标题。'],
				'related_posts_number'		=> ['title'=>'数量',		'type'=>'number',	'value'=>5,			'class'=>'all-options',	'description'=>'默认为5。'],
				'related_posts_post_types'	=> ['title'=>'文章类型',	'type'=>'checkbox',	'options'=>$post_type_options,	'description'=>'相关文章列表包含哪些文章类型的文章，默认则为当前文章的类型。'],
				'related_posts_excerpt'		=> ['title'=>'摘要',		'type'=>'checkbox',	'description'=>'显示文章摘要。'],
				'related_posts_thumbnail'	=> ['title'=>'缩略图',	'type'=>'fieldset',	'fields'=>[
						'related_posts_thumbnail'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,		'description'=>'显示缩略图。'],
						'related_posts_width'		=> ['title'=>'',	'type'=>'number',	'value'=>100,	'class'=>'small-text',	'description'=>'宽度'],
						'related_posts_height'		=> ['title'=>'',	'type'=>'number',	'value'=>100,'class'=>'small-text',	'description'=>'高度']
					]
				],
				'related_posts_style'		=> ['title'=>'样式',		'type'=>'fieldset',	'fields'=>[
					'related_posts_div_id'	=> ['title'=>'',	'type'=>'text',	'value'=>'related_posts',	'class'=>'all-options',	'description'=>'外层 div id，不填则外层不添加 div。'],
					'related_posts_class'	=> ['title'=>'',	'type'=>'text',	'value'=>'',				'class'=>'all-options',	'description'=>'相关文章列表 ul 的 class。'],
				]],
				'related_posts_auto'		=> ['title'=>'自动',		'type'=>'checkbox',	'value'=>1,	'description'=>'自动附加到文章末尾。'],
			]
		]
	];

	return compact('sections');
});

add_action('admin_head',function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('input#related_posts_thumbnail').change(function(){
			$('div#div_related_posts_width').hide();
			$('div#div_related_posts_height').hide();

			if($(this).is(':checked')){
				$('div#div_related_posts_width').show();
				$('div#div_related_posts_height').show();
			}
		});

		$('input#related_posts_thumbnail').change();
	});
	</script>
	<?php
});