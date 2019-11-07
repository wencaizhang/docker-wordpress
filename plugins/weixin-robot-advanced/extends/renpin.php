<?php
/*
Plugin Name: 人品计算器
Plugin URI: http://wpjam.net/item/wpjam-weixin-renpin/
Description: 发送【人品XXX】（xxx为你的名字）就可以查看你或者朋友的当日人品了。
Version: 1.1
Author URI: http://blog.wpjam.com/
*/

add_filter('weixin_builtin_reply', 'weixin_robot_renpin_builtin_reply');
function weixin_robot_renpin_builtin_reply($weixin_builtin_replies){
	$weixin_builtin_replies['rp']	= 
	$weixin_builtin_replies['人品']	= array('type'=>'prefix', 'reply'=>'人品计算', 'function'=>'weixin_robot_renpin_reply');
	return $weixin_builtin_replies;
}

function weixin_robot_renpin_reply($keyword){
	global $weixin_reply;

	$name = str_replace(array('人品','rp'), '', $keyword);
	
	if(!$name){
		$weixin_reply->textReply('人品后面加上姓名哦，比如：人品张三。');
	}else{
		$results = weixin_robot_get_renpin_results($name);
		$weixin_reply->textReply($results);
	}
	$weixin_reply->set_response('renpin');
}

function weixin_robot_get_renpin_results($name){
	
	$name		= htmlspecialchars($name);	
	$results	= '你的大名：'.$name."\n";
	
	$a=0;
	for($i = 0;$i < strlen($name); $i++){
		$a=$a+ord($name[$i]);
	}
	
	$value=($a+round(time()/86400))%102;

	$results .= '你的得分：'.$value."\n得分评价：";

	if($value == 0){	// 分成 0-22 共 23 级
		$level = 0;
	}elseif($value == 100){
		$level = 21;
	}elseif ($value > 100) {
		$level = 22;
	}else{
		$level = (int)($value/5)+1;
	}

	$renpin_array = array(
		'你一定不是人吧？怎么一点人品都没有？！',
		'算了，跟你没什么人品好谈的...',
		'是我不好...不应该跟你谈人品问题的...',
		'杀过人没有？放过火没有？你应该无恶不做吧？',
		'你貌似应该三岁就偷看隔壁大妈洗澡的吧...',
		'你的人品之低下实在让人惊讶啊...',
		'你的人品太差了。你应该有干坏事的嗜好吧？',
		'你的人品真差!肯定经常做偷鸡摸狗的事...',
		'你拥有如此差的人品请经常祈求佛祖保佑你吧...',
		'老实交待..那些论坛上面经常出现的偷拍照是不是你的杰作？',
		'你随地大小便之类的事没少干吧？',
		'你的人品太差了..稍不小心就会去干坏事了吧？',
		'你的人品很差了..要时刻克制住做坏事的冲动哦..',
		'你的人品比较差了..要好好的约束自己啊..',
		'你的人品勉勉强强..要自己好自为之..',
		'有你这样的人品算是不错了..',
		'你有较好的人品..继续保持..',
		'你的人品不错..应该一表人才吧？',
		'你的人品真好..做好事应该是你的爱好吧..',
		'你的人品太好了..你就是当代活雷锋啊...',
		'你是世人的榜样！',
		'天啦！你不是人！你是神！！！',
		'你的人品竟然溢出了...我对你无语..',
	);

	$results .= $renpin_array[$level];

	$results .= "\n\n温馨提示：本次测试仅供娱乐参考！";

	return $results;
}