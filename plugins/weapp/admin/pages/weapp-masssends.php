<?php

include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-masssend.php');

function weapp_masssends_list_table(){

	$template_keys	= [];
	// $templates[weapp_get_template_id('service_status')]	= '商家服务状态提醒';
	$template_keys['service_status']	= '商家服务状态提醒';

	$actions	= [
		'add'		=> ['title'=>'新增'],
		'edit'		=> ['title'=>'编辑'],
		'duplicate'	=> ['title'=>'复制'],
		'send'		=> ['title'=>'群发',	'direct'=>true,	'confirm'=>true,	'page_title'=>'群发作业'],
		'cancel'	=> ['title'=>'取消',	'direct'=>true,	'confirm'=>true],
		'delete'	=> ['title'=>'删除',	'direct'=>true,	'confirm'=>true],
	];

	return array(
		'title'				=> '群发作业',
		'singular'			=> 'weapp-masssends',
		'plural'			=> 'weapp-masssend',
		'primary_column'	=> 'title',
		'primary_key'		=> 'id',
		'model'				=> 'WEAPP_AdminMasssendJob',
		'ajax'				=> true,
		'actions'			=> $actions,
		'capability'		=> 'manage_weapp_'.weapp_get_appid(),
		'fields'			=> array(
			'title'			=>['title'=>'标题',		'type'=>'text',		'show_admin_column'=>true],
			// 'user_tag'		=>['title'=>'用户标签',	'type'=>'select',	'show_admin_column'=>true,	'options'=>[''=>'全部','tuhao'=>'土豪']],
			'path'			=>['title'=>'路径',		'type'=>'text',		'description'=>'点击模板卡片后的跳转页面'],
			'template_key'	=>['title'=>'模板',		'type'=>'select',	'show_admin_column'=>true,	'options'=>$template_keys],
			'service_status'=>['title'=>'模板内容',	'type'=>'fieldset',	'fields'=>[
				'keyword1'	=>['title'=>'服务类型',	'type'=>'text'],
				'keyword2'	=>['title'=>'服务状态',	'type'=>'textarea'],
				'keyword3'	=>['title'=>'时间',		'type'=>'text'],
			]],
			'count'			=>['title'=>'预计群发',	'type'=>'view',		'show_admin_column'=>'only'],
			'success'		=>['title'=>'发送成功',	'type'=>'text',		'show_admin_column'=>'only'],
			'failed'		=>['title'=>'发送失败',	'type'=>'text',		'show_admin_column'=>'only'],
			'status'		=>['title'=>'状态',		'type'=>'view',		'show_admin_column'=>'only','options'=>WEAPP_AdminMasssendJob::get_status_list()],
			'start_time'	=>['title'=>'开始时间',	'type'=>'view',		'show_admin_column'=>'only'],
			'end_time'		=>['title'=>'结束时间',	'type'=>'view',		'show_admin_column'=>'only'],
		),
	);						
}
add_filter('wpjam_weapp_'.weapp_get_appid().'_masssends_list_table', 'weapp_masssends_list_table');
add_filter('wpjam_weapp_masssends_list_table', 'weapp_masssends_list_table');

