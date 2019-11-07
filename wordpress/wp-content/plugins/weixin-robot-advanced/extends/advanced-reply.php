<?php
/*
Plugin Name: 高级回复
Plugin URI: 
Description: 发送 n r 等关键字获取博客最新或者随机文章。
Version: 1.0
Author URI: http://blog.wpjam.com/
*/

// 定义高级回复的关键字
add_filter('weixin_builtin_reply', function($builtin_replies){
	$weixin_setting	= weixin_get_setting();

    $builtin_replies[$weixin_setting['new']] 		= array('type'=>'full',	'reply'=>'最新日志',			'function'=>'weixin_robot_new_posts_reply');
	$builtin_replies[$weixin_setting['rand']] 		= array('type'=>'full',	'reply'=>'随机日志',			'function'=>'weixin_robot_rand_posts_reply');
	$builtin_replies[$weixin_setting['hot']] 		= array('type'=>'full',	'reply'=>'最热日志',			'function'=>'weixin_robot_hot_posts_reply');
	$builtin_replies[$weixin_setting['comment']] 	= array('type'=>'full',	'reply'=>'留言最多日志',		'function'=>'weixin_robot_comment_posts_reply');
	$builtin_replies[$weixin_setting['hot-7']]		= array('type'=>'full',	'reply'=>'一周最热日志',		'function'=>'weixin_robot_hot_7_posts_reply');
	$builtin_replies[$weixin_setting['comment-7']]	= array('type'=>'full',	'reply'=>'一周留言最多日志',	'function'=>'weixin_robot_comment_7_posts_reply');
	
    return $builtin_replies;
});


//设置时间为最近7天
function weixin_robot_posts_where_7( $where = '' ) {
	return $where . " AND post_date > '" . date('Y-m-d', strtotime('-7 days')) . "'";
}

//设置时间为最近30天
function weixin_robot_posts_where_30( $where = '' ) {
	return $where . " AND post_date > '" . date('Y-m-d', strtotime('-60 days')) . "'";
}

//按照时间排序
function weixin_robot_new_posts_reply($keyword){
	global $weixin_reply;
	$weixin_reply->wp_query_reply();
}

//随机排序
function weixin_robot_rand_posts_reply($keyword){
	global $weixin_reply;
	$weixin_reply->wp_query_reply(array(
		'orderby'	=>'rand'
	));
}

//按照浏览排序
function weixin_robot_hot_posts_reply($keyword){
	global $weixin_reply;
	$weixin_reply->wp_query_reply(array(
		'meta_key'	=>'views',
		'orderby'	=>'meta_value_num',
	));
}

//按照留言数排序
function weixin_robot_comment_posts_reply($keyword){
	global $weixin_reply;
	$weixin_reply->wp_query_reply(array(
		'orderby'	=>'comment_count',
	));
}

//7天内最热
function weixin_robot_hot_7_posts_reply($keyword){
	add_filter('posts_where', 'weixin_robot_posts_where_7' );
	weixin_robot_hot_posts_reply($keyword);
}

//7天内留言最多 
function weixin_robot_comment_7_posts_reply($keyword){
	add_filter('posts_where', 'weixin_robot_posts_where_7' );
	weixin_robot_comment_posts_reply($keyword);
}

//30天内最热
function weixin_robot_hot_30_posts_reply($keyword){
	add_filter('posts_where', 'weixin_robot_posts_where_30' );
	weixin_robot_hot_posts_reply($keyword);
}

//30天内留言最多
function weixin_robot_comment_30_posts_reply($keyword){
	add_filter('posts_where', 'weixin_robot_posts_where_30' );
	weixin_robot_comment_posts_reply($keyword);
}