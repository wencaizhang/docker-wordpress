<?php
add_action('wpjam_post_page_file', function($post_type){
	if(is_post_type_viewable($post_type) && $post_type != 'attachment'){
		add_filter('wpjam_post_options', function ($wpjam_options){
			
			$wpjam_options['wpjam_custom_footer_box'] = [
				'title'		=> '文章底部代码',	
				'fields'	=> [
					'custom_footer'	=>['title'=>'',	'type'=>'textarea', 'description'=>'自定义文章 Footer 代码可以让你在当前文章插入独有的 JS，CSS，iFrame 等类型的代码，让你可以对具体一篇文章设置不同样式和功能，展示不同的内容。']
				]
			];

			return $wpjam_options;
		});
	}
});
