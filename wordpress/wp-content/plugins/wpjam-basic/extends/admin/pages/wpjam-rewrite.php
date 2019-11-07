<?php
add_filter('wpjam_basic_setting', function(){
	$rewrite_fields		= [
		// 'remove_type/_rewrite'			=> '文章格式Rewrite规则',
		'remove_comment_rewrite'		=> '留言Rewrite规则',
		'remove_comment-page_rewrite'	=> '留言分页Rewrite规则',
		// 'remove_author_rewrite'			=> '作者Rewrite规则',
		'remove_feed=_rewrite'			=> '分类Feed Rewrite规则',
		'remove_attachment_rewrite'		=> '附件Rewrite规则'
	];

	$all	= '';

	if($rewrite_rules = get_option('rewrite_rules')){
		$all	= '<table class="widefat striped">';
		$all	.= '<thead>';
		$all	.= '<tr><td>正则 | regex</td><td>查询 | query</td></tr>';
		$all	.= '</thead>';
		$all	.= '<tbody>';

		foreach ($rewrite_rules as $key => $rewrite_rule) {
			$all	.= '<tr><td>'.$key.'</td><td>'.$rewrite_rule.'</td></tr>';
		}

		$all	.= '</tbody>';
		$all	.= '</table>';
	}
	
	$sections	= [ 
		'remove-rewrite'	=>[
			'title'		=>'优化Rewrite', 
			'summary'	=>'<p>如果你的网站没有使用以下页面，可以移除相关功能的的Rewrite规则以提高网站效率！</p>',
			'fields'	=> array_map(function($title){return ['title'=>'','type'=>'checkbox','description'=>'移除'.$title]; }, $rewrite_fields)
		],
		
		'custom-rewrite'	=> [
			'title'		=>'自定义Rewrite', 
			'fields'	=> [
				'rewrites'=>['title'=>'',	'type'=>'mu-fields',	'fields'=>[
					'regex'	=> ['title'=>'规则',	'type'=>'text'],
					'query'	=> ['title'=>'查询',	'type'=>'text'],
				]]
			]
		],

		'all-rewrite'	=> [
			'title'		=>'所有Rewrite', 
			'fields'	=> [
				'all'	=> ['title'=>'','type'=>'view','value'=>$all]
			]
		]
	];

	return compact('sections');
});


flush_rewrite_rules();