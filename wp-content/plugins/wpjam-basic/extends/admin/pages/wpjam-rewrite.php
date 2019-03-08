<?php
add_filter('wpjam_basic_setting', function(){
	$rewrite_fields		= [
		'remove_type/_rewrite'			=> '文章格式Rewrite规则',
		'remove_comment_rewrite'		=> '留言Rewrite规则',
		'remove_comment-page_rewrite'	=> '留言分页Rewrite规则',
		'remove_author_rewrite'			=> '作者Rewrite规则',
		'remove_feed=_rewrite'			=> '分类Feed Rewrite规则',
		'remove_attachment_rewrite'		=> '附件Rewrite规则'
	];
	
	$sections	= [ 
		'remove-rewrite'	=>[
			'title'		=>'优化Rewrite', 
			'summary'	=>'<p>如果你的网站没有使用以下页面，可以移除相关功能的的Rewrite规则以提高网站效率！</p>',
			'fields'	=> array_map(function($title){return ['title'=>$title,'type'=>'checkbox','description'=>'移除']; }, $rewrite_fields)
		],
		'custom-rewrite'	=> [
			'title'		=>'自定义Rewrite', 
			'fields'	=> [
				'rewrites'=>['title'=>'',	'type'=>'mu-fields',	'fields'=>[
					'regex'	=> ['title'=>'规则',	'type'=>'text'],
					'query'	=> ['title'=>'查询',	'type'=>'text'],
				]]
			]
		]
	];
	
	$field_validate = function($value){
		flush_rewrite_rules();
		return $value;
	};

	return compact('sections','field_validate');
});