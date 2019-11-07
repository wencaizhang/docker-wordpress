<?php

// 定义高级回复在自定义回复中的 tab
add_filter('wpjam_weixin_replies_tabs', function($tabs){
	$tabs['advanced']	= array('title'=>'高级回复', 'function'=>'option', 'option_name'=>'weixin-robot'); // 高级回复和默认回复处理方式一样
	return $tabs;
},11);


add_filter('weixin_reply_setting',function($sections, $current_tab){
	if($current_tab == 'advanced'){
    
	    $advanced_reply_section_fields = array(
			'new'		=> array('title'=>'最新日志',			'type'=>'text',	'class'=>'small-text',	'value'=>'n'),
			'rand'		=> array('title'=>'随机日志',			'type'=>'text',	'class'=>'small-text',	'value'=>'r'),
			'comment'	=> array('title'=>'留言最高日志',		'type'=>'text',	'class'=>'small-text',	'value'=>'c'),
			'comment-7'	=> array('title'=>'7天留言最高日志',	'type'=>'text',	'class'=>'small-text',	'value'=>'c7'),
			'hot'		=> array('title'=>'浏览最高日志',		'type'=>'text',	'class'=>'small-text',	'value'=>'t'),
			'hot-7'		=> array('title'=>'7天浏览最高日志',	'type'=>'text',	'class'=>'small-text',	'value'=>'t7'),
		);

		$advanced_reply_section_fields = apply_filters('weixin_advanced_reply',$advanced_reply_section_fields);

		$sections = array(
	    	'advanced_reply'	=> array(
	    		'title'		=>'',
	    		'fields'	=>$advanced_reply_section_fields,	
	    		'summary'	=>'<p>设置返回下面各种类型日志的关键字。</p>'
	    	)
	    );
	}
    
    return $sections;
},11, 2);
