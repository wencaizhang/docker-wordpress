<?php
$response	= array();
$openid		= isset($_GET['weixin_openid'])?$_GET['weixin_openid']:'';

if($openid){
	$response = WEIXIN_User::get($openid);

	if(!$response){
		$response = array('err'=>'无此微信用户');
	}
}else{
	if(is_weixin()){
		$response	= WEIXIN_User::get();
		if(empty($response['nickname'])){
			wp_redirect(home_url('/api/get_weixin_user.json?get_userinfo'));
		}
	}else{
		$response = array('err'=>'weixin_openid 不能为空');
	}
}

wpjam_send_json($response);