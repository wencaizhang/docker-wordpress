<?php
/*
Plugin Name: 表情回复
Plugin URI: http://blog.wpjam.com/project/weixin-robot-emotion/
Description: 表情回复，用户发送表情之后，可以回复同样的表情。
Version: 2.1
Author URI: http://blog.wpjam.com/
*/

add_filter('weixin_builtin_reply', 'wpjam_weixin_emotions_builtin_reply');
function wpjam_weixin_emotions_builtin_reply($weixin_builtin_replies){

	$emotions = wpjam_weixin_get_emotions();

	$emotions_low = array_map('strtolower', $emotions);

	$emotions_text = array('微笑','伤心','美女','发呆','墨镜','哭','羞','哑','睡','哭','囧','怒','调皮','笑','惊讶','难过','酷','汗','抓狂','吐','笑','快乐','奇','傲','饿','累','吓','汗','高兴','闲','努力','骂','疑问','秘密','乱','疯','哀','鬼','打击','bye','汗','抠','鼓掌','糟糕','恶搞','什么','什么','累','看','难过','难过','坏','亲','吓','可怜','刀','水果','酒','篮球','乒乓','咖啡','美食','动物','鲜花','枯','唇','爱','分手','生日','电','炸弹','刀','足球','虫','臭','月亮','太阳','礼物','伙伴','赞','差','握手','优','恭','勾','顶','坏','爱','不','好的','爱','吻','跳','怕','尖叫','圈','拜','回头','跳','天使','激动','舞','吻','瑜伽','太极');

	foreach ($emotions_low as $emotion) {
		if($emotion){
			$weixin_builtin_replies[$emotion] =  array('type'=>'full',   'reply'=>'表情回复',  'function'=>'wpjam_weixin_emotions_reply');
		}
	}

    return $weixin_builtin_replies;
}

add_filter('weixin_response_types','wpjam_emotions_response_types');
function wpjam_emotions_response_types($response_types){
	$response_types['emotions'] = '表情回复';
	return $response_types;
}

function wpjam_weixin_emotions_reply($keyword){
	global $weixin_reply;
	$emotions = wpjam_weixin_get_emotions();
	$emotions_low = array_flip(array_map('strtolower', $emotions));

	$keyword = $emotions[$emotions_low[$keyword]];

	$weixin_reply->textReply('我也会发表情哦，而且一次三个：'.$keyword.$keyword.$keyword);
	$weixin_reply->set_response('emotions');
}

function wpjam_weixin_get_emotions(){
	return array('/::)','/::~','/::B','/::|','/:8-)','/::<','/::$','/::X','/::Z','/::\'(','/::-|','/::@','/::P','/::D','/::O','/::(','/::+','/:--b','/::Q','/::T','/:,@P','/:,@-D','/::d','/:,@o','/::g','/:|-)','/::!','/::L','/::>','/::,@','/:,@f','/::-S','/:?','/:,@x','/:,@@','/::8','/:,@!','/:!!!','/:xx','/:bye','/:wipe','/:dig','/:handclap','/:&-(','/:B-)','/:<@','/:@>','/::-O','/:>-|','/:P-(','/::\'|','/:X-)','/::*','/:@x','/:8*','/:pd','/:<W>','/:beer','/:basketb','/:oo','/:coffee','/:eat','/:pig','/:rose','/:fade','/:showlove','/:heart','/:break','/:cake','/:li','/:bome','/:kn','/:footb','/:ladybug','/:shit','/:moon','/:sun','/:gift','/:hug','/:strong','/:weak','/:share','/:v','/:@)','/:jj','/:@@','/:bad','/:lvu','/:no','/:ok','/:love','/:<L>','/:jump','/:shake','/:<O>','/:circle','/:kotow','/:turn','/:skip','/:oY','/:#-0','/:hiphot','/:kiss','/:<&','/:&>');
}