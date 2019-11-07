<?php
add_filter('wpjam_page_ajax_response', function(){
	return 'wpjam_verify_ajax_response';
});

function wpjam_verify_ajax_response(){
	$action	= $_POST['page_action'];
	$data	= wp_parse_args($_POST['data']);

	if($action == 'submit'){
		$response = WPJAM_Verify::bind_user($data);

		if(is_wp_error($response)){
			wpjam_send_json($response);
		}else{
			wpjam_send_json();
		}
	}
}

function wpjam_verify_page(){
	?>
	<h2>验证 WPJAM</h2>
	
	<?php 

	$fields	= [
		'qrcode'	=> ['title'=>'二维码',	'type'=>'view'],
		'code'		=> ['title'=>'验证码',	'type'=>'number',	'class'=>'all-options',	'description'=>'验证码10分钟内有效！'],
		'scene'		=> ['title'=>'scene',	'type'=>'hidden']
	];

	$response	= WPJAM_Verify::get_qrcode();

	if(is_wp_error($response)){

		echo '<div class="notice notice-error"><p>'.$response->get_error_message().'</p></div>';

		return;
	}else{
		echo '
		<p><strong>通过验证才能使用 WPJAM Basic 的扩展功能。 </strong></p>
		<p>1. 使用微信扫描下面的二维码获取验证码。<br />
		2. 将获取验证码输入提交即可！<br />
		3. 如果验证不通过，请使用 Chrome 浏览器验证，并在验证之前清理浏览器缓存。</p>
		';
	}

	$qrcode = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$response['ticket'];
	$fields['qrcode']['value']	= '<img srcset="'.$qrcode.' 2x" src="'.$qrcode.'" style="max-width:350px;" />';
	$fields['scene']['value']	= $response['scene'];

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'action'		=> 'submit',
		'submit_text'	=> '验证'
	]);
}

add_action('admin_head', function(){
	if(current_user_can('manage_sites')){
		$redirect_url	= admin_url('admin.php?page=wpjam-extends');
	}else{
		$redirect_url	= admin_url('admin.php?page=wpjam-basic-topics');
	}
	?>

	<style type="text/css">
	.form-table th{width: 100px;}
	</style>

	<script type="text/javascript">
	jQuery(function($){
		$('body').on('page_action_success', function(e, response){
			if(response.page_action == 'submit'){
				window.location.replace('<?php echo $redirect_url; ?>');
			}
		});
	});
	</script>
	<?php
});