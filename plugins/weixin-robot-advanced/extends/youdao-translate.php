<?php
/*
Plugin Name: 有道中英翻译
Plugin URI: http://wpjam.net/item/wpjam-weixin-youdao-translate/
Description: 发送【翻译 xxx】来就可以进行翻译了，比如翻译中文为英语。
Version: 1.4
Author URI: http://blog.wpjam.com/
*/

// add_filter('weixin_builtin_reply', 'wpjam_weixin_youdao_translate_builtin_reply');
// function wpjam_weixin_youdao_translate_builtin_reply($weixin_builtin_replies){
// 	$weixin_builtin_replies['fy']	= 
// 	$weixin_builtin_replies['翻译']	= array('type'=>'prefix',	'reply'=>'中英翻译',	'function'=>'wpjam_weixin_youdao_translate_reply');
// 	return $weixin_builtin_replies;
// }

function wpjam_weixin_youdao_translate_reply($keyword){
	global $weixin_reply;

	if($keyword == '翻译' || $keyword == '中英翻译'){
		$weixin_reply->set_context_reply('wpjam_weixin_youdao_translate_reply');
		$weixin_reply->textReply("你已经进入翻译模式，请输入要翻译单词或者句子。\n\n退出翻译请输入：Q，或者1分钟后自动退出");  
		$weixin_reply->set_response('translate'); 
		return true;
	}

	if($keyword == 'q'){
		$weixin_reply->delete_context_reply('wpjam_weixin_youdao_translate_reply');
		$weixin_reply->textReply("你已经退出了翻译模式，下次要进行翻译，请再次输入：翻译");  
		$weixin_reply->set_response('translate'); 
		return true;
	}

	$keyword = str_replace(array('翻译','fy'), '', $keyword);

	$results = wpjam_weixin_get_youdao_translate_results($keyword);
	if($results){
		$weixin_reply->textReply($results);
	}else{
		$weixin_reply->textReply('翻译失败');   
	}
	
	$weixin_reply->set_response('translate'); 
}

function wpjam_weixin_get_youdao_translate_results($keyword){
	$weixin_setting	= weixin_get_setting();
	$url = 'http://fanyi.youdao.com/openapi.do?keyfrom='.$weixin_setting['youdao_translate_key_from']."&key=".$weixin_setting['youdao_translate_api_key'].'&type=data&doctype=json&version=1.1&q='.urlencode($keyword);
	//test
	//$url = "http://fanyi.youdao.com/openapi.do?keyfrom=doucube&key=1845007487&type=data&doctype=json&version=1.1&q=$keyword";
	
	$responese = wpjam_remote_request($url);

	if(is_wp_error($responese)){
		return false;
	}
	$youdao = $responese;
	
	$result = "";
	if (isset($youdao['errorCode'])){
		switch ($youdao['errorCode']){
			case 0:
				$translation = isset($youdao['translation'])?$youdao['translation']:'';
				if($translation){
					$result .= $translation[0]."\n";
					if (isset($youdao['basic'])){
						$result .= isset($youdao['basic']['phonetic'])?($youdao['basic']['phonetic'])."\n":"";
						foreach ($youdao['basic']['explains'] as $value) {
							$result .= $value."\n";
						}
					}
				}else{
					$result = "错误：请重试";
				}
				break;
			case 20:
				$result = "错误：要翻译的文本过长";
				break;
			case 30:
				$result = "错误：无法进行有效的翻译";
				break;
			case 40:
				$result = "错误：不支持的语言类型";
				break;
			case 50:
				$result = "错误：无效的密钥";
				break;
			default:
				$result = "错误：原因未知，错误码：".$youdao['errorCode'];
				break;
		}
		return trim($result);
	}else{
		return false;
	}
}

add_filter('weixin_default_option','wpjan_weixin_translate_default_option');
function wpjan_weixin_translate_default_option($defaults_options){
	$youdao_translate_default_options = array(
		'youdao_translate_api_key'	=> '',
		'youdao_translate_key_from'	=> '',
	);
	return array_merge($defaults_options, $youdao_translate_default_options);
}

