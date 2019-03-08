<?php
add_filter('wpjam_basic_setting', function(){
	$smtp_fields = [
		'smtp_mail_from_name'	=> ['title'=>'发送者姓名',	'type'=>'text'],

		'smtp'					=> ['title'=>'SMTP 设置',	'type'=>'fieldset','fields'=>[
			'smtp_host'	=> ['title'=>'地址',		'type'=>'text',		'value'=>'smtp.gmail.com'],
			'smtp_ssl'	=> ['title'=>'发送协议',	'type'=>'text',		'value'=>'ssl'],
			'smtp_port'	=> ['title'=>'SSL端口',	'type'=>'number',	'value'=>'465'],
			'smtp_user'	=> ['title'=>'邮箱账号',	'type'=>'email'],
			'smtp_pass'	=> ['title'=>'邮箱密码',	'type'=>'password'],
		]],

		'smtp_reply'			=> ['title'=>'默认回复',	'type'=>'fieldset','fields'=>[
			'smtp_reply_to_mail'	=> ['title'=>'邮箱地址',	'type'=>'email'],
			'smtp_reply_to_name'	=> ['title'=>'邮箱姓名',	'type'=>'text'],
		]],
	];

	$sections	= [
		'wpjam-smtp'	=> [
			'title'		=>'', 
			'fields'	=>$smtp_fields, 
			'summary'	=>'<p>点击这里查看：<a target="_blank" href="http://blog.wpjam.com/m/gmail-qmail-163mail-imap-smtp-pop3/">常用邮箱的 SMTP 设置</a>。</p>'
		]
	];

	return compact('sections');
});

function wpjam_smtp_ajax_response(){
	$action	= $_POST['page_action'];
	$data	= wp_parse_args($_POST['data']);

	if($action == 'submit'){
		$to			= $data['to']?:'';
		$subject	= $data['subject']?:'';
		$message	= $data['message']?:'';

		if(wp_mail($to, $subject, $message)){
			wpjam_send_json(['errcode'=>0]);
		}
	}
}

function wpjam_smtp_send_page(){
	echo '<h2>发送测试</h2>';

	$fields = array(
		'to'		=> array('title'=>'收件人',	'type'=>'email',	'required'),
		'subject'	=> array('title'=>'主题',	'type'=>'text',		'required'),
		'message'	=> array('title'=>'内容',	'type'=>'textarea',	'style'=>'max-width:640px;',	'rows'=>8,	'required'),
	);

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'action'		=> 'submit', 
		'submit_text'	=> '发送'
	]);
}

add_action('wp_mail_failed', function ($mail_failed){
	wpjam_send_json($mail_failed);
});