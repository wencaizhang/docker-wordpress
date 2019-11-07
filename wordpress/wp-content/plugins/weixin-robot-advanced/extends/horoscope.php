<?php
/*
Plugin Name: 星座运势
Plugin URI: http://wpjam.net/item/wpjam-weixin-horoscope/
Description: 发送你的星座就可以查看今日的运势了，比如发送：天秤座。
Version: 1.1
Author URI: http://blog.wpjam.com/
*/

function wpjam_weixin_horoscope_reply($keyword){
	global $weixin_reply;

	$horoscope = wpjam_get_juhe_constellation($keyword);

	if(is_wp_error($horoscope)){
		$weixin_reply->textReply($horoscope->get_error_message()); 
	}else{
		$text	= "==".$keyword." 今日运势==\n\n";
		$text	.='综合指数：'.$horoscope['all']."\n";
		$text	.='爱情指数：'.$horoscope['love']."\n";
		$text	.='工作指数：'.$horoscope['work']."\n";
		$text	.='财运指数：'.$horoscope['money']."\n";
		$text	.='健康指数：'.$horoscope['health']."\n\n";
		$text	.='幸运颜色：'.$horoscope['color']."\n";
		$text	.='幸运数字：'.$horoscope['number']."\n";
		$text	.='速配星座：'.$horoscope['QFriend']."\n\n";
		$text	.=$horoscope['summary'];
		$weixin_reply->textReply($text);
	}

    $weixin_reply->set_response('horoscope'); 
}







