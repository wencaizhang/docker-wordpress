<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-user.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-message.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-reply-setting.php');

add_action('wpjam_weixin_messages_tabs', function($tabs){
	$tabs	= [
		'stats'		=> ['title'=>'消息预览',	'function'=>'weixin_message_overview_page'],
		'message'	=> ['title'=>'消息统计',	'function'=>'weixin_message_stats_page'],
		'event'		=> ['title'=>'事件统计',	'function'=>'weixin_message_stats_page'],
		'text'		=> ['title'=>'文本统计',	'function'=>'weixin_message_stats_page'],
		'summary'	=> ['title'=>'文本汇总',	'function'=>'weixin_message_summary_page'],
		'messages'	=> ['title'=>'最新消息',	'function'=>'list']
	];

	if(weixin_get_type() < 3) {
		unset($tabs['messages']);
	}

	return $tabs;
});

add_filter('wpjam_weixin_messages_list_table', function(){
	return [
		'title'				=> '消息管理',
		'singular'			=> 'weixin-message',
		'plural'			=> 'weixin-messages',
		'primary_column'	=> 'username',
		'primary_key'		=> 'id',
		'model'				=> 'WEIXIN_AdminMessage',
		'ajax'				=> true,
		'style'				=> '
			th.column-MsgType{width:60px;}
			th.column-Response{width:94px;}
			th.column-CreateTime{width:84px;}
			th.column-username{width:240px;}
		'
	];
});

function weixin_message_overview_page(){

	global $wpdb, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	echo '<h2>消息统计分析预览</h2>';

	wpjam_stats_header(array('show_date_type'=>true));

	// $counts_array	= $wpdb->get_results("SELECT FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as cnt, count(DISTINCT FromUserName) as user, (COUNT(id)/COUNT(DISTINCT FromUserName)) as avg FROM {WEIXIN_Message::get_table()} WHERE CreateTime > {$wpjam_start_timestamp} AND CreateTime < {$wpjam_end_timestamp} GROUP BY day ORDER BY day DESC;", OBJECT_K);

	$counts_array	= WEIXIN_AdminMessage::Query()->where('appid', weixin_get_appid())->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as cnt, count(DISTINCT FromUserName) as user, (COUNT(id)/COUNT(DISTINCT FromUserName)) as avg");

	$counts_array	= array_combine(array_column($counts_array, 'day'), $counts_array);

	wpjam_line_chart($counts_array, array(
		'cnt'	=>'消息发送次数', 
		'user'	=>'消息发送人数', 
		'avg'	=>'人均发送次数#'
	));
}

function weixin_message_stats_page() {
	global $wpdb, $current_admin_url, $current_tab, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	$message_types	= WEIXIN_AdminMessage::get_message_types($current_tab);
	$message_query	= WEIXIN_AdminMessage::Query()->where('appid', weixin_get_appid())->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp);

	if($current_tab == 'event'){
		$title		= '事件消息统计分析';
		$field		= 'LOWER(Event)';

		$message_query->where('MsgType', 'event');
		// $where_base	= "MsgType = 'event' AND ";
	}elseif ($current_tab == 'text') {
		$title		= '文本消息统计分析';
		$field		= 'LOWER(Response)';

		$message_query->where('MsgType', 'text');

		// $where_base	= "MsgType = 'text' AND ";
		if(!empty($_GET['s'])){
			$message_query->where('Content', trim($_GET['s']));
			// $where_base	.= "Content = '".trim($_GET['s'])."' AND ";
		}
	}elseif($current_tab == 'menu'){
		$weixin_menu = WEIXIN_Menu::get();
		if(!$weixin_menu) return;

		$message_types	= WEIXIN_AdminMessage::get_message_types('menu');

		$title		= '菜单点击统计分析';
		$field		= 'EventKey';

		$message_query->where('MsgType', 'event')->where_in('Event',['CLICK','VIEW','scancode_push','scancode_waitmsg','location_select','pic_sysphoto','pic_photo_or_album','pic_weixin'])->where_not('EventKey', '');

		// $where_base	= "MsgType = 'event' AND Event in('CLICK','VIEW','scancode_push','scancode_waitmsg','location_select','pic_sysphoto','pic_photo_or_album','pic_weixin') AND EventKey !='' AND ";
	}elseif($current_tab == 'subscribe'){
		$title		= '订阅统计分析';
		$field		= 'LOWER(EventKey)';
		$message_query->where('MsgType', 'event')->where_in('Event',['subscribe','unsubscribe']);

		// $where_base	= "MsgType = 'event' AND (Event = 'subscribe' OR Event = 'unsubscribe') AND ";
	}elseif($current_tab == 'wifi-shop'){
		$title		= 'Wi-Fi连接门店统计分析';
		$field		= 'LOWER(EventKey)';

		$message_query->where('MsgType', 'event')->where('Event', 'WifiConnected')->where_not('EventKey', '')->where_not('EventKey', '');

		// $where_base	= "MsgType = 'event' AND Event = 'WifiConnected' AND EventKey!='' AND EventKey!='0' AND ";
	}elseif($current_tab == 'card-event'){
		$title		= '卡券事件统计分析';
		$field		= 'LOWER(Event)';

		$message_query->where('MsgType', 'event')->where_in('Event', ['card_not_pass_check', 'card_pass_check', 'user_get_card', 'user_del_card', 'user_view_card', 'user_enter_session_from_card', 'user_consume_card']);

		// $where_base	= "MsgType = 'event' AND Event in('card_not_pass_check', 'card_pass_check', 'user_get_card', 'user_del_card', 'user_view_card', 'user_enter_session_from_card', 'user_consume_card') AND ";
	}else{
		$title		= '消息统计分析';
		$field		= 'LOWER(MsgType)';

		$message_query->where_not('MsgType', 'manual');
		// $where_base	= "MsgType !='manual' AND ";
	}

	$message_type 	=  isset($_GET['type'])?$_GET['type']:'';

	if($message_type){
		$message_query->where($field, $message_type);
	}

	if($message_type && $wpjam_compare){
		$title = $message_types[$message_type].'消息对比';
	}

	echo '<h2>'.$title.'</h2>';
	if($current_tab == 'menu'){
		echo '<p>下面的名称，如果是默认菜单的按钮，则显示名称，如果是个性化菜单独有的按钮，则显示key。</p>';
	}

	wpjam_stats_header(array('show_date_type'=>true));

	$wheres	= $message_query->get_wheres();

	$counts = WEIXIN_AdminMessage::Query()->where_fragment($wheres)->group_by($field)->order_by('count')->get_results("count(id) as count, {$field} as label");
	$labels	= wp_array_slice_assoc($message_types, array_column($counts, 'label'));
	$total 	= WEIXIN_AdminMessage::Query()->where_fragment($wheres)->get_var('count(*)');

	if(empty($_GET['s'])){
		// wpjam_donut_chart($counts, array('total'=>$total, 'labels'=>$new_message_types, 'show_link'=>true,'chart_width'=>280));
		wpjam_donut_chart($counts, ['total'=>$total, 'labels'=>$labels, 'show_link'=>true,'chart_width'=>280]);
	}

	?>

	<div class="clear"></div>

	<?php

	if($message_type){
		$counts_array	= WEIXIN_AdminMessage::Query()->where_fragment($wheres)->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as `{$message_type}`");

		$counts_array	= array_combine(array_column($counts_array, 'day'), $counts_array);

		$message_type_label = $message_types[$message_type]??$message_type;

		wpjam_line_chart($counts_array, [$message_type=>$message_type_label]);
	}else{
		if(empty($_GET['s'])){
			$sum = array();
			foreach (array_keys($message_types) as $message_type){
				$sum[] = "SUM(case when {$field}='{$message_type}' then 1 else 0 end) as `{$message_type}`";
			}
			$sum = implode(', ', $sum);
		
			$counts_array	= WEIXIN_AdminMessage::Query()->where_fragment($wheres)->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as total, {$sum}");

			$counts_array	= array_combine(array_column($counts_array, 'day'), $counts_array);

			$labels = ['total'=>'所有#']+$labels;
			wpjam_line_chart($counts_array, $labels);
		}else{
			$counts_array	= WEIXIN_AdminMessage::Query()->where_fragment($wheres)->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as total");

			$counts_array	= array_combine(array_column($counts_array, 'day'), $counts_array);

			wpjam_line_chart($counts_array, array('total'=>$_GET['s']));
		}
		
	}
}

// 文本汇总
function weixin_message_summary_page(){

	global $wpdb, $current_admin_url, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	echo '<h2>文本回复类型统计分析</h2>';

	wpjam_stats_header();
	
	$response_types = WEIXIN_AdminMessage::get_response_types();
	
	$response_type = $_GET['type']??null;

	// $response_types_string = "'".implode("','", array_keys($response_types))."'";

	$wheres	= WEIXIN_AdminMessage::Query()->where('appid', weixin_get_appid())->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where('MsgType', 'text')->where_not('Response', '')->get_wheres();

	$counts_array	= WEIXIN_AdminMessage::Query()->where_fragment($wheres)->group_by('Response')->order_by('count')->get_results("COUNT( * ) AS count, Response as label");

	// wpjam_print_r($counts_array);

	// $counts_array	= array_combine(array_column($counts_array, 'Response'), $counts_array);

	// $where = "CreateTime > {$wpjam_start_timestamp} AND CreateTime < {$wpjam_end_timestamp}";
	// $sql = "SELECT COUNT( * ) AS count, Response FROM {$wpdb->weixin_messages} WHERE {$where} AND Response in ({$response_types_string}) AND (MsgType ='text' OR (MsgType = 'event' AND Event!='subscribe' AND Event!='unsubscribe' AND EventKey != '')) GROUP BY Response ORDER BY count DESC";
	//$sql = "SELECT COUNT( * ) AS count, Response FROM {$wpdb->weixin_messages} WHERE {$where} AND Response in ({$response_types_string}) AND MsgType ='text' GROUP BY Response ORDER BY count DESC";
	// $sql = "SELECT COUNT( * ) AS count, Response FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType ='text' GROUP BY Response ORDER BY count DESC";

	// $counts = $wpdb->get_results($sql);

	// $new_counts = array();
	// foreach ($counts as $count) {
	// 	if(isset($response_types[$count->Response])){
	// 		$new_counts[] = array(
	// 			'label'	=>isset($response_types[$count->Response])?$response_types[$count->Response]:$count->Response,
	// 			'count'	=>$count->count
	// 		);
	// 		$new_response_types[$count->Response] = isset($response_types[$count->Response])?$response_types[$count->Response]:$count->Response;
	// 	}
	// }
	
	// $total = $wpdb->get_var("SELECT COUNT( id ) FROM {$wpdb->weixin_messages} WHERE {$where} AND Response in ({$response_types_string}) AND (MsgType ='text' OR (MsgType = 'event' AND Event!='subscribe' AND Event!='unsubscribe' AND EventKey != ''))");
	// $total = $wpdb->get_var("SELECT COUNT( id ) FROM {$wpdb->weixin_messages} WHERE {$where} AND Response in ({$response_types_string}) AND MsgType ='text' ");
	// $total = $wpdb->get_var("SELECT COUNT( id ) FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType ='text' ");

	// wpjam_donut_chart($new_counts, array('total'=>$total, 'show_link'=>true, 'chart_width'=> '280'));
	wpjam_donut_chart($counts_array, array('labels'=>$response_types, 'show_link'=>true, 'chart_width'=> '280'));
	?>

	<div style="clear:both;"></div>

	<?php

	// $filpped_response_types = array_flip($response_types);
	if($response_type){
		echo '<h2 id="detail">“'.($response_types[$response_type]??$response_type).'”热门关键字</h2>';
	}else{
		echo '<h2 id="detail">热门关键字</h2>';
		// $where .= " AND Response in ({$response_types_string})";
	}

	//$sql = "SELECT COUNT( * ) AS count, Response, MsgType, Content FROM ( SELECT Response, MsgType, LOWER(Content) as Content FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType ='text' AND Content !='' UNION ALL SELECT Response, MsgType,  LOWER(EventKey) as Content FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType = 'event'  AND Event!='subscribe' AND Event!='unsubscribe' AND EventKey !='' ) as T1 GROUP BY Content ORDER BY count DESC LIMIT 0 , 100";
	// $sql = "SELECT COUNT( * ) AS count, Response, MsgType, LOWER(Content) as Content FROM ( SELECT * FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType ='text' AND Content !='' ORDER BY CreateTime DESC) abc GROUP BY Content ORDER BY count DESC LIMIT 0, 100";
	
	// $weixin_hot_messages = $wpdb->get_results($sql);

	$weixin_hot_messages	= WEIXIN_AdminMessage::Query()->where_fragment($wheres)->where('Response', $response_type)->where_not('Content', '')->group_by('Content')->order_by('count')->limit(100)->get_results("COUNT( * ) AS count, Response, MsgType, LOWER(Content) as Content");

	if($weixin_hot_messages){
	?>
	<table class="widefat striped" cellspacing="0">
	<thead>
		<tr>
			<th style="width:42px">排名</th>
			<th style="width:42px">数量</th>
			<th>关键词</th>
			<th style="width:91px">回复类型</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$i = 0;
	foreach ($weixin_hot_messages as $weixin_message) { $i++; ?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><?php echo $weixin_message['count']; ?></td>
			<td><?php echo wp_strip_all_tags($weixin_message['Content']); ?></td>
			<td><?php echo $response_types[$weixin_message['Response']]??$weixin_message['Response']; ?></td>
		</tr>
	<?php } ?>
	</tbody>
	</table>
	<?php
	}
}
