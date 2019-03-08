<?php
add_filter('wpjam_basic_setting', function($sections){
	$toc_fields = [
		'toc_depth'		=> ['title'=>'显示到第几级',	'type'=>'select',	'value'=>'6',	'options'=>['1'=>'h1','2'=>'h2','3'=>'h3','4'=>'h4','5'=>'h5','6'=>'h6']],
    	'toc_individual'=> ['title'=>'目录单独设置',	'type'=>'checkbox',	'value'=>'1',	'description'=>'在每篇文章编辑页面单独设置是否显示文章目录以及显示到第几级。'],
		'toc_auto'		=> ['title'=>'脚本自动插入',	'type'=>'checkbox', 'value'=>'1',	'description'=>'自动插入文章目录的 JavaScript 和 CSS 代码，请点击这里获取<a href="https://blog.wpjam.com/m/toc-js-css-code/" target="_blank">文章目录的默认 JS 和 CSS</a>。'],
		'toc_script'	=> ['title'=>'JS代码',		'type'=>'textarea',	'value'=>'',	'description'=>'如果你没有选择自动插入脚本，可以将下面的 JavaScript 代码复制你主题的 JavaScript 文件中。'],
		'toc_css'		=> ['title'=>'CSS代码',		'type'=>'textarea',	'value'=>'',	'description'=>'根据你的主题对下面的 CSS 代码做适当的修改。<br />如果你没有选择自动插入脚本，可以将下面的 CSS 代码复制你主题的 CSS 文件中。'],
    	'toc_copyright'	=> ['title'=>'版权信息',		'type'=>'checkbox', 'value'=>'1',	'description'=>'在文章目录下面显示版权信息。']
	];

	$sections	= [
		'wpjam-toc'	=> [
			'title'		=>'', 
			'fields'	=>$toc_fields, 
		]
	];

	return compact('sections');
});

add_action('admin_head',function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('input#toc_auto').change(function(){
			$('tr#tr_toc_script').hide();
			$('tr#tr_toc_css').hide();

			if($(this).is(':checked')){
				$('tr#tr_toc_script').show();
				$('tr#tr_toc_css').show();
			}
		});

		$('input#toc_auto').change();
	});
	</script>
	<?php
});