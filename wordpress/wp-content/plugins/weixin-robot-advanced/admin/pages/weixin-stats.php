<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-users.php');

add_filter('wpjam_weixin_dashboard_widgets', function (){
	$end 	= current_time('timestamp',true);
	$start	= $end - (DAY_IN_SECONDS);

	$args 	= compact('start', 'end');

	return [
		'weixin-overview'	=>['title'=>'数据预览',			'callback'=>'weixin_overview_dashboard_widget_callback',	'args'=>$args],
		'weixin-keyword'	=>['title'=>'24小时热门关键字',	'callback'=>'weixin_keyword_dashboard_widget_callback',		'args'=>$args,	'context'=>'side'],
	];
});

function weixin_get_message_counts($start, $end){
	$total	= WEIXIN_AdminMessage::Query()->where('appid', weixin_get_appid())->where_gt('CreateTime', $start)->where_lt('CreateTime', $end)->get_var("count(id) as total");
	$people	= WEIXIN_AdminMessage::Query()->where('appid', weixin_get_appid())->where_gt('CreateTime', $start)->where_lt('CreateTime', $end)->get_var("count(DISTINCT FromUserName) as people");
	
	$avg	= ($people)?round($total/$people,4):0;

	return compact('total', 'people', 'avg');
}

function weixin_get_expected_count($today_count, $yesterday_count, $yesterday_compare_count='', $asc=true){

	if($yesterday_compare_count){
		$expected_count = round($today_count/$yesterday_compare_count*$yesterday_count);
	}else{
		$expected_count	= $today_count;
	}

	if(floatval($expected_count) >= floatval($yesterday_count)){
		if($asc){
			$expected_count	.= '<span class="green">&uarr;</span>';
		}else{
			$expected_count	.= '<span class="red">&uarr;</span>';
		}
	}else{
		if($asc){
			$expected_count	.= '<span class="red">&darr;</span>';
		}else{
			$expected_count	.= '<span class="green">&darr;</span>';
		}
	}

	return $expected_count;
}

function weixin_overview_dashboard_widget_callback($dashboard, $meta_box){
	global $wpdb,  $wpjam_stats_labels;

	$today						= date('Y-m-d',current_time('timestamp'));
	$today_start_timestamp		= strtotime(get_gmt_from_date($today.' 00:00:00'));
	$today_end_timestamp		= current_time('timestamp',true);

	$yesterday					= date('Y-m-d',current_time('timestamp')-DAY_IN_SECONDS);
	$yesterday_start_timestamp	= strtotime(get_gmt_from_date($yesterday.' 00:00:00'));
	$yesterday_end_timestamp	= strtotime(get_gmt_from_date($yesterday.' 23:59:59'));

	$yesterday_end_timestamp_c	= current_time('timestamp',true)-DAY_IN_SECONDS;

	$today_counts 				= weixin_get_user_subscribe_counts($today_start_timestamp, $today_end_timestamp);
	$yesterday_counts 			= weixin_get_user_subscribe_counts($yesterday_start_timestamp, $yesterday_end_timestamp);
	$yesterday_compare_counts	= weixin_get_user_subscribe_counts($yesterday_start_timestamp, $yesterday_end_timestamp_c);
	
	?>
	<h3>用户订阅</h3>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>时间</th>
				<th>用户订阅</th>	
				<th>取消订阅</th>	
				<th>取消率%</th>	
				<th>净增长</th>	
			</tr>
		</thead>
		<tbody>
			<tr class="alternate">
				<td>今日</td>
				<td><?php echo $today_counts['subscribe'];?></td>
				<td><?php echo $today_counts['unsubscribe'];?></td>
				<td><?php echo $today_counts['percent'];?></td>
				<td><?php echo $today_counts['netuser'];?></td>
			</tr>
			<tr class="">
				<td>昨日</td>
				<td><?php echo $yesterday_counts['subscribe'];?></td>
				<td><?php echo $yesterday_counts['unsubscribe'];?></td>
				<td><?php echo $yesterday_counts['percent'];?></td>
				<td><?php echo $yesterday_counts['netuser'];?></td>
			</tr>
			<tr class="alternate" style="font-weight:bold;">
				<td>预计今日</td>
				<td><?php echo $expected_subscribe = weixin_get_expected_count($today_counts['subscribe'], $yesterday_counts['subscribe'], $yesterday_compare_counts['subscribe']); ?></td>
				<td><?php echo $expected_unsubscribe = weixin_get_expected_count($today_counts['unsubscribe'], $yesterday_counts['unsubscribe'], $yesterday_compare_counts['unsubscribe'], false); ?></td>
				<td><?php echo weixin_get_expected_count($today_counts['percent'], $yesterday_counts['percent'],'',false); ?></td>
				<td><?php echo weixin_get_expected_count($expected_subscribe - $expected_unsubscribe, $yesterday_counts['netuser']); ?></td>
			</tr>
		</tbody>
	</table>

	<p><a href="<?php echo admin_url('admin.php?page=weixin-users&tab=subscribe');?>">详细用户订阅数据...</a></p>
	<hr />
	<?php

	$today_counts 				= weixin_get_message_counts($today_start_timestamp, $today_end_timestamp);
	$yesterday_counts 			= weixin_get_message_counts($yesterday_start_timestamp, $yesterday_end_timestamp);
	$yesterday_compare_counts	= weixin_get_message_counts($yesterday_start_timestamp, $yesterday_end_timestamp_c);
	?>
	<h3>消息统计</h3>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>时间</th>
				<th>消息发送次数</th>	
				<th>消息发送人数</th>	
				<th>人均发送次数</th>	
			</tr>
		</thead>
		<tbody>
			<tr class="alternate">
				<td>今日</td>
				<td><?php echo $today_counts['total']; ?>
				<td><?php echo $today_counts['people']; ?>
				<td><?php echo $today_counts['avg']; ?>
			</tr>
			<tr class="">
				<td>昨日</td>
				<td><?php echo $yesterday_counts['total']; ?>
				<td><?php echo $yesterday_counts['people']; ?>
				<td><?php echo $yesterday_counts['avg']; ?>
			</tr>
			<tr class="alternate" style="font-weight:bold;">
				<td>预计今日</td>
				<td><?php echo weixin_get_expected_count($today_counts['total'], $yesterday_counts['total'], $yesterday_compare_counts['total']); ?>
				<td><?php echo weixin_get_expected_count($today_counts['people'], $yesterday_counts['people'], $yesterday_compare_counts['people']); ?>
				<td><?php echo weixin_get_expected_count($today_counts['avg'], $yesterday_counts['avg']); ?>
			</tr>
		</tbody>
	</table>

	<p><a href="<?php echo admin_url('admin.php?page=weixin-messages&tab=stats');?>">详细消息统计...</a></p>
	<?php
	
}

function weixin_keyword_dashboard_widget_callback($dashboard, $meta_box){

	global $wpdb;

	$start	= $meta_box['args']['start'];
	$end	= $meta_box['args']['end'];

	$where = " CreateTime > {$start} AND CreateTime < {$end}";

	$hot_messages	= WEIXIN_AdminMessage::Query()->where('appid', weixin_get_appid())->where_gt('CreateTime', $start)->where_lt('CreateTime', $end)->where('MsgType','text')->where_not('Content','')->group_by('Content')->order_by('count')->order('DESC')->limit(10)->get_results("COUNT( * ) AS count, Response, MsgType, LOWER(Content) as Content");

	$response_types = WEIXIN_AdminMessage::get_response_types();

	$i= 0;
	if($hot_messages){ ?>
	<table class="widefat" cellspacing="0">
		<tbody>
		<?php foreach ($hot_messages as $message) { $alternate = empty($alternate)?'alternate':''; $i++; ?>
			<tr class="<?php echo $alternate; ?>">
				<td style="width:18px;"><?php echo $i; ?></td>
				<td><?php echo $message['Content']; ?></td>
				<td style="width:32px;"><?php echo $message['count']; ?></td>
				<td style="width:98px;"><?php echo ($response_types[$message['Response']])??''; ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<p><a href="<?php echo admin_url('admin.php?page=weixin-messages&tab=summary');?>">更多热门关键字...</a></p>
	<?php
	}
}

