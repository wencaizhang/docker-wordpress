<?php
add_filter('wpjam_post_options','weixin_robot_share_post_options');
function weixin_robot_share_post_options($wpjam_post_options){

	$wpjam_post_options['weixin-share'] = array(
		'title'			=> '微信分享设置',
		'post_types'	=> 'all',
		'fields'		=> array(
			'weixin_hide_option_menu'	=> array('title'=>'隐藏网页右上角按钮',	'type'=>'radio',	'options'=>array('0'=>'否','1'=>'是')	),
			// 'weixin_hide_toolbar'		=> array('title'=>'隐藏网页底部导航栏',	'type'=>'radio',	'options'=>array('0'=>'否','1'=>'是')	),
			'weixin_share_title'		=> array('title'=>'分享标题',			'type'=>'text'	),
			'weixin_share_desc'			=> array('title'=>'分享描述',			'type'=>'textarea'	),
			'weixin_share_img'			=> array('title'=>'分享图片',			'type'=>'image'	),
			'weixin_share_url'			=> array('title'=>'分享链接',			'type'=>'url'	),
			'weixin_url'				=> array('title'=>'群发原文链接',		'type'=>'url'	),
		)
	);

	return $wpjam_post_options;
}