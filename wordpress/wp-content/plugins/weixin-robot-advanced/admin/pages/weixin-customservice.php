<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-customservice.php');
add_filter('wpjam_weixin_robot_customservice_list_table', function(){
	return [
		'title'				=> '客服',
		'singular'			=> 'weixin-customservice',
		'plural'			=> 'weixin-customservices',
		'primary_column'	=> 'kf_account',
		'primary_key'		=> 'kf_account',
		'model'				=> 'WEIXIN_CustomService',
		'search'			=> 'false',
		'actions'			=> [
			'add'		=> ['title' => '新增'],
			'edit'		=> ['title' => '编辑'],
			'delete'	=> ['title' => '删除',	'direct' => true,	'bulk'=>true]
		],
		'fields'			=> [
			'kf_id'			=> ['title'=>'客服ID',		'type'=>'text',	'show_admin_column'=>'only'],
			'kf_account'	=> ['title'=>'客服账号',		'type'=>'text',	'show_admin_column'=>'only','required',		'description'=>'完整客服账号，格式为：账号前缀@公众号微信号'],
			'kf_nick'		=> ['title'=>'客服昵称',		'type'=>'text',	'show_admin_column'=>true,	'required',		'description'=>'客服昵称，最长6个汉字或12个英文字符'],
			'kf_headimgurl'	=> ['title'=>'客服头像',		'type'=>'image','show_admin_column'=>true],
			'kf_wx'			=> ['title'=>'绑定微信号',	'type'=>'text',	'show_admin_column'=>true],
			'status'		=> ['title'=>'状态',		'type'=>'radio','show_admin_column'=>'only',		'options'=>WEIXIN_CustomService::$online_status],
			'accepted_case'	=> ['title'=>'正在接待会话',	'type'=>'text',	'show_admin_column'=>'only'],
		]
	];
});