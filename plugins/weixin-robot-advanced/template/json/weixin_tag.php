<?php
$response = array();

$weixin_openid	= isset($_REQUEST['weixin_openid'])?$_REQUEST['weixin_openid']:'';
$tag 			= isset($_REQUEST['tag'])?$_REQUEST['tag']:'';

if($weixin_openid && $tag){
	weixin_robot_insert_user_tag($weixin_openid, $tag);
	$response = array('success'=>'添加成功');

	// weixin_robot_delete_user_tag($weixin_openid, $tag);
	// $response = array('success'=>'删除成功');
	
}else{
	$response = array('errcode'=>'50001','errmsg'=>'weixin_openid 或者 tag 为空');
}

wpjam_send_json($response);