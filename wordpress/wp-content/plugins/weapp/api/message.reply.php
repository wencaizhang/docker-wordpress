<?php
include(WP_CONTENT_DIR.'/plugins/weapp/includes/class-weapp-message.php');
include(WP_CONTENT_DIR.'/plugins/weapp/includes/class-weapp-reply-setting.php');

$appid		= wpjam_get_parameter('appid',		array('method'=>'GET',	'required'=>true));
$signature	= wpjam_get_parameter('signature',	array('method'=>'GET',	'required'=>true));
$timestamp	= wpjam_get_parameter('timestamp',	array('method'=>'GET',	'required'=>true));
$nonce		= wpjam_get_parameter('nonce',		array('method'=>'GET',	'required'=>true));

$weapp_setting	= weapp_get_setting($appid);
$token			= $weapp_setting['token'];
$encodingAesKey	= $weapp_setting['encodingaeskey'];

// 验证签名
$msg_list	= array($token, $timestamp, $nonce);
sort($msg_list, SORT_STRING);
if(sha1(implode($msg_list)) != $signature){
	trigger_error("签名验证错误");
	exit;
}

// 微信小程序第一次验证token
if($echostr = wpjam_get_parameter('echostr')){
	echo $echostr;
	exit;	
}

// 获取已加密的实体
$encrypted_msg	= wpjam_json_decode(file_get_contents('php://input'));
$encrypted_msg	= $encrypted_msg['Encrypt'];

$msg_signature	= wpjam_get_parameter('msg_signature', array(
	'method'	=> 'GET',
	'required'	=> true
));

// 验证加密签名
$msg_list	= array($encrypted_msg, $token, $timestamp, $nonce);
sort($msg_list, SORT_STRING);

if(sha1(implode($msg_list)) != $msg_signature){
	trigger_error("加密签名验证错误");
	exit;
}

$aes_key 		= base64_decode($encodingAesKey . "=");
$iv				= substr($aes_key, 0, 16);

$openssl_crypt	= new WPJAM_OPENSSL_Crypt($aes_key, array('method'=>'aes-256-cbc', 'iv'=>$iv, 'options'=>OPENSSL_ZERO_PADDING));

try {
	$decrypted_msg	= $openssl_crypt->decrypt($encrypted_msg);
}catch(Exception $e) {
	trigger_error("AES 解密失败");
	exit;
}

//去除补位字符
$pad	= ord(substr($decrypted_msg, -1));
$pad	= ($pad >= 1 && $pad <= 32)?$pad:0;
$result	= substr($decrypted_msg, 0, (strlen($decrypted_msg) - $pad));

if (strlen($result) < 16)	{
	echo '';
	exit;
}

//去除16位随机字符串,网络字节序和AppId
$content		= substr($result, 16, strlen($result));
$len_list		= unpack("N", substr($content, 0, 4));
$json_len		= $len_list[1];
$decrypted_msg	= substr($content, 4, $json_len);
$from_appid		= substr($content, $json_len + 4);

if($appid != $from_appid){
	trigger_error("AppId 校验错误\nfrom_appid: $from_appid	appid: $appid ");
	exit;
}

$decrypted_msg	= wpjam_json_decode($decrypted_msg);


$require_reply	= true;
$keyword		= '';
$msg_type		= $decrypted_msg['MsgType'];
if($msg_type == 'text'){
	$keyword	= $decrypted_msg['Content'];
}elseif($msg_type == 'event'){
	$event		= $decrypted_msg['Event'];
	if($event == 'user_enter_tempsession'){
		$keyword	= $decrypted_msg['SessionFrom'];	
	}elseif($event == 'kf_create_session' || $event == 'kf_close_session'){
		$require_reply	= false; 	// 无需发送客服消息
	}
}

$ToUserName		= $decrypted_msg['ToUserName'];
$FromUserName	= $decrypted_msg['FromUserName'];

WEAPP_Message::set_appid($appid);
WEAPP_Message::insert($decrypted_msg);

echo "success";
if($require_reply){
	WEAPP_ReplySetting::set_appid($appid);;

	$weapp_replies	= WEAPP_ReplySetting::get_replies($msg_type, $keyword);
	$weapp_replies	= $weapp_replies ?: WEAPP_ReplySetting::get_replies('default');

	if($weapp_replies){

		$i = 0;

		foreach ($weapp_replies as $weapp_reply) {
			$i++;

			$weapp_reply['reply']	= maybe_unserialize($weapp_reply['reply']);
			$result	= WEAPP_ReplySetting::reply($FromUserName, $weapp_reply);

			if($result == 'transfer_customer_service'){
				echo wpjam_json_encode(array(
					'ToUserName'	=> $FromUserName,
					'FromUserName'	=> $ToUserName,
					'CreateTime'	=> time(),
					'MsgType'		=> 'transfer_customer_service',
				));
				
				exit;
			}elseif(is_wp_error($result)){
				trigger_error(var_export($weapp_reply, true).$result->get_error_message());
			}

			if($msg_type == 'event'){
				if($i == 1) break;
			}else{
				if($i == 5) break;
			}
		}
	}else{
		echo wpjam_json_encode(array(
			'ToUserName'	=> $FromUserName,
			'FromUserName'	=> $ToUserName,
			'CreateTime'	=> time(),
			'MsgType'		=> 'transfer_customer_service',
		));
		
		exit;
	}
}

exit;

