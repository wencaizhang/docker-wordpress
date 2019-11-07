<?php
/*
Plugin Name: 装逼字符工具集
Plugin URI: http://blog.wpjam.com/
Description: 各种装逼字符工具。
Version: 1.4
Author URI: http://blog.wpjam.com/
*/

add_filter('weixin_builtin_reply', function ($builtin_replies){
	$builtin_replies['大写数字'] = ['type'=>'full',	'reply'=>'大写人民币汉字',	'function'=>'weixin_upper_rmb_reply'];
	$builtin_replies['上标'] 	= ['type'=>'full',	'reply'=>'上标电话号码',		'function'=>'weixin_sup_tel_reply'];
	$builtin_replies['下标'] 	= ['type'=>'full',	'reply'=>'上标电话号码',		'function'=>'weixin_sub_tel_reply'];
	$builtin_replies['下划'] 	= ['type'=>'full',	'reply'=>'下划线昵称',		'function'=>'weixin_underline_nickname_reply'];
	$builtin_replies['翅膀'] 	= ['type'=>'full',	'reply'=>'翅膀昵称',			'function'=>'weixin_wing_char_reply'];
	$builtin_replies['彩字'] 	= ['type'=>'full',	'reply'=>'彩色字母',			'function'=>'weixin_color_char_reply'];
	$builtin_replies['模糊'] 	= ['type'=>'full',	'reply'=>'模糊昵称',			'function'=>'weixin_blur_nickname_reply'];
	$builtin_replies['冒烟'] 	= ['type'=>'full',	'reply'=>'冒烟昵称',			'function'=>'weixin_smoke_nickname_reply'];
	$builtin_replies['丑拒'] 	= ['type'=>'full',	'reply'=>'加群丑拒',			'function'=>'weixin_chouju_qun_reply'];
	$builtin_replies['变脏'] 	= ['type'=>'full',	'reply'=>'昵称变脏',			'function'=>'weixin_dirty_nickname_reply'];
	$builtin_replies['666'] 	= ['type'=>'full',	'reply'=>'666卧槽牛逼',		'function'=>'weixin_wow_666_reply'];
	$builtin_replies['unicode'] = ['type'=>'full',	'reply'=>'中文转Unioncode',	'function'=>'weixin_unicode_reply'];
	return $builtin_replies;
});

function weixin_upper_rmb_reply($keyword){
	global $weixin_reply;

	if($keyword == '大写数字'){
		$weixin_reply->set_context_reply('weixin_upper_rmb_reply');
		$weixin_reply->textReply("请输入人民币数字金额：");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$number		= str_replace(',', '', $keyword);
	$number		= number_format($number*100, 0, '', '');
	$length		= strlen($number);

	// $weixin_reply->textReply($number);
	// 	$weixin_reply->set_response('char');
	// 	return;

	if($length>15){
		$weixin_reply->textReply('超出计算范围');
		$weixin_reply->set_response('char');
		return;
	}elseif($number == 0){
		$weixin_reply->textReply('零元整');
		$weixin_reply->set_response('char');
		return;
	}

	
	$upper_rmb	= '';
	$string1	= '零壹贰叁肆伍陆柒捌玖'; //汉字数字
	$string2	= '万仟佰拾亿仟佰拾万仟佰拾元角分'; // 对应单位  

	$string2	= mb_substr($string2, mb_strlen($string2)-$length, $length);	 // 取出对应位数的数字单位

	$ch1		= '';	// 数字的汉语读法
	$ch2		= '';	// 数字位的汉字读法
	$n_zero		= 0;	// 用来计算连续的零值的个数
	
	$string3	= '';	// 指定位置的数值  
	for ($i=0; $i < $length; $i++) { 
		$string3	= substr($number, $i, 1);	// 取出需转换的某一位的值  
		if($i != ($length - 3) && $i != ($length - 7) && $i != ($length - 11) && $i != ($length - 15) ){
			if($string3 == 0){
				$ch1 	= '';
				$ch2 	= '';
				$n_zero++;
			}elseif($string3!=0 && $n_zero != 0){
				$ch1	= '零'.mb_substr($string1, $string3, 1);
				$ch2	= mb_substr($string2, $i, 1);
				$n_zero	= 0;
			}else{
				$ch1	= mb_substr($string1, $string3, 1);
				$ch2	= mb_substr($string2, $i, 1);
				$n_zero	= 0;
			}
		}else{	// 该位是万亿，亿，万，元位等关键位 
			if($string3 != 0 && $n_zero != 0){
				$ch1	= '零'.mb_substr($string1, $string3, 1);
				$ch2	= mb_substr($string2, $i, 1);
				$n_zero	= 0;
			}elseif($string3 != 0 && $n_zero == 0){
				$ch1	= mb_substr($string1, $string3, 1);
				$ch2	= mb_substr($string2, $i, 1);
				$n_zero	= 0;
			}elseif($string3 == 0 && $n_zero >= 3){
				$ch1 	= '';
				$ch2 	= '';
				$n_zero++;
			}else{
				$ch1 	= '';
				$ch2	= mb_substr($string2, $i, 1);
				$n_zero++;
			}

			if ($i == ($length - 11) || $i == ($length - 3)) { // 如果该位是亿位或元位，则必须写上  
				$ch2	= mb_substr($string2, $i, 1);
			}
		}

		$upper_rmb	.=  $ch1 . $ch2;
	}

	if ($string3 == 0) { // 最后一位（分）为0时，加上“整”  
		$upper_rmb	.= "整";
	}

	$weixin_reply->textReply($upper_rmb);
	$weixin_reply->set_response('char');
}

function weixin_mb_combining_character($string, $combining_char){
	$result = '';

	$start = 0;
	$strlen = mb_strlen($string);
	while ($strlen) {
		$result	.= mb_substr($string, $start, 1,"utf8"). $combining_char;
		$string	= mb_substr($string, 1, $strlen, "utf8");
		$strlen	= mb_strlen($string);
	}
	return $result;
}

function weixin_chouju_qun_reply($keyword){
	global $weixin_reply;

	if($keyword == '丑拒'){
		$weixin_reply->set_context_reply('weixin_chouju_qun_reply');
		$weixin_reply->textReply("请输入要恶搞的群名称：");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$message	= $weixin_reply->get_message();
	$qun_name 	= str_replace(['+','/'],['-','_'],base64_encode($message['Content']));
	$qun_url	= 'https://tool.wpjam.com/chouju/?qun='.$qun_name;
	
	$weixin_reply->textReply('「'.$message['Content'].'」群的丑拒加群恶搞图片已经生成。'."\n\n".'<a href="'.$qun_url.'">请点击这里获取！</a>');
	$weixin_reply->set_response('char'); 
}

function weixin_underline_nickname_reply($keyword){
	global $weixin_reply;

	if($keyword == '下划'){
		$weixin_reply->set_context_reply('weixin_underline_nickname_reply');
		$weixin_reply->textReply("请输入要转换的昵称或者其他文字！");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$message	= $weixin_reply->get_message();
	
	$weixin_reply->textReply('꯭'.weixin_mb_combining_character($message['Content'], '꯭'));
	$weixin_reply->set_response('char'); 
}

function weixin_dirty_nickname_reply($keyword){
	global $weixin_reply;

	if($keyword == '变脏'){
		$weixin_reply->set_context_reply('weixin_dirty_nickname_reply');
		$weixin_reply->textReply("请输入要转换的昵称或者其他文字！");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$message	= $weixin_reply->get_message();

	$dirtys	= [' ༽','༼'];
	$key	= array_rand($dirtys);
	$dirty	= $dirtys[$key];
	
	$weixin_reply->textReply($message['Content'].' '.$dirty);
	$weixin_reply->set_response('char'); 
}

function weixin_wow_666_reply($keyword){
	global $weixin_reply;

	if($keyword == '666'){
		$weixin_reply->set_context_reply('weixin_wow_666_reply');
		$weixin_reply->textReply("请输入第一段文字：");
		$weixin_reply->set_response('char'); 
		return true;
	}

	$openid		= $weixin_reply->get_openid();

	$wow_666	= wp_cache_get($openid, 'wow_666');

	if($wow_666 === false){
		$message	= $weixin_reply->get_message();
		wp_cache_set($openid, $message['Content'], 'wow_666', HOUR_IN_SECONDS);

		$weixin_reply->textReply("请输入第二段文字：");
		$weixin_reply->set_response('char'); 
		return true;

	}else{
		$wow_template	= '⁶⁶⁶⁶⁶     ⁶⁶⁶⁶⁶⁶    666卧槽 ⁶⁶⁶⁶⁶⁶    ⁶⁶⁶⁶⁶⁶     ⁶⁶66⁶⁶⁶⁶     ⁶⁶⁶⁶⁶⁶卧槽    ⁶⁶666 ⁶⁶⁶⁶⁶     ⁶⁶⁶⁶⁶⁶   [message1]⁶⁶⁶⁶⁶ ⁶⁶⁶⁶⁶     ⁶⁶⁶⁶⁶⁶   ⁶⁶⁶⁶卧槽   ⁶⁶⁶⁶⁶⁶[message2]    ⁶⁶66⁶⁶⁶⁶     卧槽⁶⁶⁶⁶⁶⁶     ⁶6⁶⁶66';

		$message		= $weixin_reply->get_message();
		$wow_666_2		= $message['Content'];

		wp_cache_delete($openid, 'wow_666');
		$weixin_reply->delete_context_reply();

		$weixin_reply->textReply(str_replace(['[message1]','[message2]'], [$wow_666, $wow_666_2], $wow_template));
		$weixin_reply->set_response('char');
	} 
}

function weixin_smoke_nickname_reply($keyword){
	global $weixin_reply;

	if($keyword == '冒烟'){
		$weixin_reply->set_context_reply('weixin_smoke_nickname_reply');
		$weixin_reply->textReply("请输入要转换的昵称或者其他文字！");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$message	= $weixin_reply->get_message();
	
	$weixin_reply->textReply(weixin_mb_combining_character($message['Content'], 'ྂ'));
	$weixin_reply->set_response('char'); 
}

function weixin_blur_nickname_reply($keyword){
	global $weixin_reply;

	if($keyword == '模糊'){
		$weixin_reply->set_context_reply('weixin_blur_nickname_reply');
		$weixin_reply->textReply("请输入要转换的昵称或者其他文字！");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$message	= $weixin_reply->get_message();
	
	$weixin_reply->textReply('҈'.weixin_mb_combining_character($message['Content'], '҈҈'));
	$weixin_reply->set_response('char'); 
}

function weixin_sup_tel_reply($keyword){
	global $weixin_reply;

	if($keyword == '上标'){
		$weixin_reply->set_context_reply('weixin_sup_tel_reply');
		$weixin_reply->textReply("请输入要转换的电话号码！");  
		$weixin_reply->set_response('char'); 
		return true;
	}
	
	$weixin_reply->textReply('℡'.str_replace(['0','1','2','3','4','5','6','7','8','9'], ['⁰','¹','²','³','⁴','⁵','⁶','⁷','⁸','⁹'], $keyword));
	$weixin_reply->set_response('char'); 
}

function weixin_sub_tel_reply($keyword){
	global $weixin_reply;

	if($keyword == '下标'){
		$weixin_reply->set_context_reply('weixin_sub_tel_reply');
		$weixin_reply->textReply("请输入要转换的电话号码！");  
		$weixin_reply->set_response('char'); 
		return true;
	}
	
	$weixin_reply->textReply('℡'.str_replace(['0','1','2','3','4','5','6','7','8','9'], ['₀','₁','₂','₃','₄','₅','₆','₇','₈','₉'], $keyword));
	$weixin_reply->set_response('char'); 
}

function weixin_wing_char_reply($keyword){
	global $weixin_reply;

	if($keyword == '翅膀'){
		$weixin_reply->set_context_reply('weixin_wing_char_reply');
		$weixin_reply->textReply("请输入昵称！");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$message	= $weixin_reply->get_message();

	// $wing_fixs = [
	// 	['╰︶﹉๑','๑﹉︶╯'],
	// 	['╰⋛⋋⊱⋋','⋌⊰⋌⋚╯'],
	// 	['╰⋛⋋⊱๑','๑⊰⋌⋚╯'],
	// 	['╰⊱⋛⋋','⋌⋚⊰╯'],
	// 	['⊹⊱⋛⋋','⋌⋚⊰⊹'],
	// 	['☜☞','☜☞'],
	// 	['⋛⊱','⊰⋚'],
	// 	['☜','☞'],
	// 	['ε','з'],
	// 	['༺','༻'],
	// 	['ʚ','ɞ']
	// ];

	// $wing_fix	= $wing_fixs[mt_rand(0, 10)];

	$weixin_reply->textReply('꧁꫞'.$message['Content'].'꫞꧂');

	$weixin_reply->set_response('char'); 
}

function weixin_color_char_reply($keyword){
	global $weixin_reply;

	if($keyword == '彩字'){
		$weixin_reply->set_context_reply('weixin_color_char_reply');
		$weixin_reply->textReply("请输入要转换的英文昵称！\n\n该功能仅在安卓系统有效！");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$weixin_reply->textReply(str_replace(
		['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'], 
		['🇦 ','🇧 ','🇨 ','🇩 ','🇪 ','🇫 ','🇬 ','🇭 ','🇮 ','🇯 ','🇰 ','🇱 ','🇲 ','🇳 ','🇴 ','🇵 ','🇶 ','🇷 ','🇸 ','🇹 ','🇺 ','🇻 ','🇼 ','🇽 ','🇾 ','🇿'], 
		$keyword
	));
	
	$weixin_reply->set_response('char'); 
}


function weixin_unicode_reply($keyword){
	global $weixin_reply;

	if($keyword == 'unicode'){
		$weixin_reply->set_context_reply('weixin_unicode_reply');
		$weixin_reply->textReply("请输入要转换的文字！！");  
		$weixin_reply->set_response('char'); 
		return true;
	}

	$message	= $weixin_reply->get_message();
	$encode 	= json_encode($message['Content']);
	$unicode	= str_replace('\\', '\\\\', substr($encode,1,strlen($encode)-2));

	$weixin_reply->textReply($unicode);
	
	$weixin_reply->set_response('char'); 
}



