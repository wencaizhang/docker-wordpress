<?php
add_filter('wpjam_pages', function ($wpjam_pages){
	if(weixin_get_type() < 3) return $wpjam_pages;
	
	$base_menu	= 'weixin-mp';
	$subs		= [];

	$subs[$base_menu.'-stats']			= [
		'menu_title'	=> '用户数据', 
		'function'		=> 'tab',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'extends/admin/pages/mp-stats.php',
		'tabs'			=> [
			'daily'		=> ['title'=>'每日增长',	'function'=>'weixin_mp_daily_stats_page'],
			'summary'	=> ['title'=>'渠道汇总',	'function'=>'weixin_mp_user_summary_page'],
			'new'		=> ['title'=>'新增用户',	'function'=>'weixin_mp_summary_page'],
			'cancle'	=> ['title'=>'取消关注',	'function'=>'weixin_mp_summary_page'],
			'net'		=> ['title'=>'净增长',	'function'=>'weixin_mp_summary_page'],
		]
	];

	$subs[$base_menu.'-article-stats']	= [
		'menu_title'	=> '群发数据', 
		'function'		=> 'tab',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'extends/admin/pages/mp-stats.php',
		'tabs'			=> [
			'summary'	=> ['title'=>'每日阅读',	'function'=>'weixin_mp_article_daily_read_page'],
			'daily'		=> ['title'=>'每日群发',	'function'=>'weixin_mp_article_daily_stats_page'],
			'hot'		=> ['title'=>'最热图文',	'function'=>'weixin_mp_articles_hot_page'],
			'stats'		=> ['title'=>'群发统计',	'function'=>'weixin_mp_article_sub_stats_page'],
			'subscribe'	=> ['title'=>'群发用户',	'function'=>'weixin_mp_article_subscribe_stats_page']
		]
	];

	$subs[$base_menu.'-read-stats']		= [
		'menu_title'	=> '图文数据', 
		'function'		=> 'tab',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'extends/admin/pages/mp-stats.php',
		'tabs'			=> [
			'stats'		=> ['title'=>'图文统计',	'function'=>'weixin_mp_daily_stats_page'],
			'read'		=> ['title'=>'阅读统计',	'function'=>'weixin_mp_summary_page'],
			'share'		=> ['title'=>'分享统计',	'function'=>'weixin_mp_summary_page'],
		]
	];

	$subs[$base_menu.'-message-stats']	= [
		'menu_title'	=> '消息数据', 
		'function'		=> 'tab',
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'extends/admin/pages/mp-stats.php',
		'tabs'			=> [
			'stats'		=> ['title'=>'消息统计',	'function'=>'weixin_mp_daily_stats_page'],
			'type'		=> ['title'=>'类型统计',	'function'=>'weixin_mp_summary_page'],
			'dist'		=> ['title'=>'分布统计',	'function'=>'weixin_mp_summary_page'],
		]
	];

	$subs[$base_menu.'-interface-stats']= [
		'menu_title'	=> '接口数据', 
		'page_file'		=> WEIXIN_ROBOT_PLUGIN_DIR.'extends/admin/pages/mp-stats.php',
		'function'		=> 'weixin_mp_daily_stats_page'
	];

	if(isset($wpjam_pages[$base_menu.'-stats']['subs'])){
		$subs = array_merge($subs, $wpjam_pages[$base_menu.'-stats']['subs']);
	}

	foreach ($subs as $menu_slug => $sub) {
		$subs[$menu_slug]['capability']	= 'view_weixin';
	}

	$wpjam_pages[$base_menu.'-stats'] = [
		'menu_title'	=> '公众号数据',
		'icon'			=> 'dashicons-chart-line',
		'capability'	=> 'view_weixin',
		'position'		=> '3.92',
		'function'		=> 'tab',
		'subs'			=> $subs,
	];

	return $wpjam_pages;
});

if (weixin_get_type() >= 3 && !wpjam_is_scheduled_event('weixin_get_mp_stats')) {
	$time	= strtotime(get_gmt_from_date($today.' 09:00:00')) + rand(10,720);	//每天9点左右获取昨天的数据	

	do_action('weixin_get_mp_stats','all');

    wp_schedule_event($time, 'daily', 'weixin_get_mp_stats', array('all'));
}

add_action('weixin_activation', function($appid){
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/trait-weixin-stats.php');
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-user-stats.php');
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-article-stats.php');
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-userread-stats.php');
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-usershare-stats.php');
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-message-stats.php');
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-messagedist-stats.php');
	include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-interface-stats.php');

	WEIXIN_UserStats::create_table();
	WEIXIN_ArticleStats::create_table();
	WEIXIN_UserReadStats::create_table();
	WEIXIN_UserShareStats::create_table();
	WEIXIN_MessageStats::create_table();
	WEIXIN_MessageDistStats::create_table();
	WEIXIN_InterfaceStats::create_table();
});




