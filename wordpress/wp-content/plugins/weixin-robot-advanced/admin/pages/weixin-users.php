<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-user.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-user-tag.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-reply-setting.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-message.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-qrcode.php');

add_action('wpjam_weixin_users_tabs', function($tabs){
	// $users_stats_tabs['masssend']	= ['title'=>'群发统计',	'function'=>'list'];

	// if(weixin_get_type() == 4){
	// 	$users_stats_tabs['vendor']	= ['title'=>'订阅渠道',	'function'=>'weixin_user_vendor_stats_page'];
	// }

	if(weixin_get_type() >= 3){
		return [
			'subscribe'	=> ['title'=>'用户增长', 	'function'=>'weixin_user_subscribe_stats_page'],
			'masssend'	=> ['title'=>'群发统计', 	'function'=>'list',	'list_table_name'=>'weixin_masssend'],
			'list'		=> ['title'=>'用户列表', 	'function'=>'list'],
			'tags'		=> ['title'=>'标签管理', 	'function'=>'list',	'list_table_name'=>'weixin_user_tags'],
			'sync'		=> ['title'=>'同步用户', 	'function'=>'weixin_sync_users_page'],
		];
	}else{
		return [
			'subscribe'	=> ['title'=>'用户增长', 	'function'=>'weixin_user_subscribe_stats_page']
		];
	}
});

add_filter('wpjam_weixin_users_list_table', function(){
	$style = '
	th.column-username{width:20%; min-width:224x;}
	th.column-tagid_list{width:10%;}
	';

	return [
		'title'				=> '微信用户',
		'singular'			=> 'weixin-user',
		'plural'			=> 'weixin-users',
		'primary_column'	=> 'username',
		'primary_key'		=> 'openid',
		'model'				=> 'WEIXIN_AdminUser',
		'ajax'				=> true,
		'style'				=> $style
	];
});

add_filter('wpjam_weixin_masssend_list_table', function (){
	return [
		'title'				=> '群发记录',
		'singular'			=> 'weixin-message',
		'plural'			=> 'weixin-messages',
		'primary_column'	=> 'MsgId',
		'primary_key'		=> 'MsgId',
		'model'				=> 'WEIXIN_AdminMessage',
		'actions'			=> [],
	];
});

add_filter('wpjam_weixin_user_tags_list_table', function(){
	return [
		'title'				=> '用户标签',
		'singular'			=> 'weixin-user-tag',
		'plural'			=> 'weixin-user-tags',
		'primary_column'	=> 'name',
		'primary_key'		=> 'id',
		'model'				=> 'WEIXIN_UserTag',
		'ajax'				=> true,
	];
});

function wpjam_weixin_users_ajax_response(){
	$action	= $_POST['page_action'];
	$data	= wp_parse_args($_POST['data']);

	if($action == 'submit'){
		$type			= $data['type']??'';

		if($type == 'list'){
			$next_openid	= $data['next_openid']??'';

			if($next_openid == ''){
				WEIXIN_User::Query()->update(array('subscribe'=>0));	// 第一次抓取将所有的用户设置为未订阅
			}

			$response = weixin()->get_user_list($next_openid);

			if(is_wp_error($response)){
				wpjam_send_json($response);
			}

			$next_openid	= $response['next_openid'];
			$count			= $response['count'];

			if($count){
				$datas	= array_map(function($openid){return array('openid'=>$openid, 'subscribe'=>1); }, $response['data']['openid']);

				WEIXIN_User::insert_multi($datas);
			}

			if($next_openid && $count > 0){
				wpjam_send_json(['errcode'=>0, 'type'=>'list', 'next_openid'=>$next_openid, 'errmsg'=>'同步列表中，请勿关闭浏览器。下一组：'.$next_openid]);
			}else{
				wpjam_send_json(['errcode'=>0, 'type'=>'batch', 'errmsg'=>'开始同步用户信息，请勿关闭浏览器。']);
			}
		}else{
			$openids	= WEIXIN_User::Query()->where('subscribe',1)->where_lt('last_update', (time()-DAY_IN_SECONDS))->limit(100)->get_col('openid');

			if($openids){
				$result = WEIXIN_User::batch_get_user_info($openids, true);
				if(is_wp_error($result)){
					wpjam_send_json($response);
				}

				if(count($openids) > 90){	// 如果有大量的用户，就再抓一次咯
					wpjam_send_json(['errcode'=>0, 'type'=>'batch', 'errmsg'=>'同步用户信息中，请勿关闭浏览器。下一组：'.current($openids)]);
				}else{
					wpjam_send_json(['errcode'=>0]);
				}
			}else{
				wpjam_send_json(['errcode'=>0]);
			}
		}
	}
}

function weixin_sync_users_page(){
	global $current_admin_url;
	// $users_sync = get_option('weixin_'.weixin_get_appid().'_users_sync');

	if(isset($_GET['force_update'])){
		WEIXIN_User::Query()->update(array('last_update'=>0));
	}

	?>
	<h2>同步用户</h2>
	
	<p>从微信获取订阅用户列表，同步到本地数据库。</p>
	
	<?php 

	$fields	= [
		'type'			=> ['title'=>'',	'type'=>'hidden',	'value'=>'list'],
		'next_openid'	=> ['title'=>'',	'type'=>'hidden',	'value'=>''],
	];

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'action'		=> 'submit', 
		'submit_text'	=> '同步'
	]);
	?>

	<script type="text/javascript">
	jQuery(function($){
		$('body').on('page_action_success', function(e, response){
			var action	= response.page_action;

			if(action == 'submit'){
				if(response.errmsg){
					$('#next_openid').val(response.next_openid);
					$('#type').val(response.type);

					setTimeout(function(){
						$('#wpjam_form').submit();
					}, 400);
				}else{
					$('#next_openid').val('');
				}		
			}
		});
	});
	</script>
	<?php

	// if(isset($_GET['action'])){
	// 	update_option('weixin_'.weixin_get_appid().'_users_sync', 'syncing');
		
	// 	if(!wpjam_is_scheduled_event('weixin_get_user_list')){	
	// 		wp_schedule_single_event(time()+3, 'weixin_get_user_list');
	// 	}

	// 	echo '<p>开始同步，请稍后！</p>';
	// }else{
	// 	if($users_sync == 'syncing'){
	// 		echo '<p>系统正在同步，请稍后！</p>';
	// 	}else{
	// 		if($users_sync){
	// 			echo '<p>上次同步是在'.wpjam_human_time_diff($users_sync).'</p>';
	// 			echo '<p>从微信服务器同步订阅用户到本地服务器所需要时间和公众号的粉丝数有关！</p>';
	// 		}

	// 		echo '<p><a class="button-primary" href="'.$current_admin_url.'&action=1">开始同步</a></p>';
	// 	}
	// }
}

// add_filter('wpjam_weixin_users_stats_list_table', function(){
// 	return [
// 		'title'				=> '群发记录',
// 		'singular'			=> 'weixin-message',
// 		'plural'			=> 'weixin-messages',
// 		'primary_column'	=> 'MsgId',
// 		'primary_key'		=> 'MsgId',
// 		'model'				=> 'WEIXIN_AdminMessage'
// 	];
// });


function weixin_get_user_subscribe_counts($start, $end){
	// global $wpdb;
	// $where 	= "CreateTime > {$start_timestamp} AND CreateTime < {$end_timestamp}";

	$counts	= WEIXIN_AdminMessage::Query()->where('appid', weixin_get_appid())->where_gt('CreateTime', $start)->where_lt('CreateTime', $end)->where('MsgType','event')->where_in('Event',['subscribe','unsubscribe'])->group_by('Event')->order_by('count')->get_results("Event as label, count(*) as count");
	
	// $sql 	= "SELECT Event as label, count(*) as count  FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType = 'event' AND (Event = 'subscribe' OR Event = 'unsubscribe') GROUP BY Event ORDER BY count DESC;";

	// $counts = $wpdb->get_results($sql,OBJECT_K);

	if($counts){
		$counts			= wp_list_pluck($counts, 'count', 'label');

		$subscribe		= $counts['subscribe']??0;
		$unsubscribe	= $counts['unsubscribe']??0;
	}else{
		$subscribe		= 0;
		$unsubscribe	= 0;
	}
	
	$netuser			= $subscribe - $unsubscribe;
	$percent			= ($subscribe)?round($unsubscribe/$subscribe, 4)*100:0;

	return ['subscribe'=>$subscribe, 'unsubscribe'=>$unsubscribe, 'netuser'=>$netuser, 'percent'=>$percent.'%'];
}

// 订阅统计
function weixin_user_subscribe_stats_page() {

	global $wpdb,  $wpjam_stats_labels;

	?>

	<h2>每日订阅统计</h2>

	<?php
	
	extract($wpjam_stats_labels);

	wpjam_stats_header(array('show_date_type'=>true));

	$stats_data = apply_filters('weixin_user_subscribe_stats_data', false);
	if ($stats_data !== false) {
		if (is_string($stats_data)) {
			echo $stats_data;
			return;
		}

		$subscribe_count = $stats_data['subscribe_count'];
		$unsubscribe_count = $stats_data['unsubscribe_count'];
		$netuser = $stats_data['netuser'];
		$unsubscribe_rate = $stats_data['unsubscribe_rate'];
		$counts_array = $stats_data['counts_array'];
	}
	else {

		$counts	= weixin_get_user_subscribe_counts($wpjam_start_timestamp, $wpjam_end_timestamp);
		$subscribe_count	= $counts['subscribe'];
		$unsubscribe_count	= $counts['unsubscribe'];
		$netuser			= $counts['netuser'];
		$unsubscribe_rate	= ($subscribe_count)?round($unsubscribe_count*100/$subscribe_count,2):0;

		$sum 	= array();
		$sum[]	= "SUM(case when Event='subscribe' then 1 else 0 end) as subscribe";
		$sum[]	= "SUM(case when Event='unsubscribe' then 1 else 0 end) as unsubscribe";
		$sum[] 	= "SUM(case when Event='subscribe' then 1 when Event='unsubscribe' then -1 else 0 end ) as netuser";
		$sum	= implode(', ', $sum);

		$counts	= WEIXIN_AdminMessage::Query()->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where('MsgType','event')->where_in('Event',['subscribe','unsubscribe'])->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as total, {$sum}");

		$counts_array	= [];

		foreach ($counts as $count) {
			$count['percent']	= round($count['unsubscribe']/$count['subscribe'] * 100, 2);
			$counts_array[$count['day']]	= $count;
		}
	}


	echo '从 '.$wpjam_start_date.' 到 '.$wpjam_end_date.' 这段时间内，共有 <span class="green">'.$subscribe_count.'</span> 人订阅，<span class="red">'.$unsubscribe_count.'</span> 人取消订阅，取消率 <span class="red">'.$unsubscribe_rate.'%</span>，净增长 <span class="green">'.$netuser.'</span> 人。';

	$types 	= array('subscribe'=>'用户订阅', 'unsubscribe'=>'取消订阅', 'percent'=>'取消率%', 'netuser'=>'净增长');
	
	wpjam_line_chart($counts_array, $types);
}

// 订阅渠道
function weixin_user_vendor_stats_page() {
	global $wpdb, $current_admin_url, $wpjam_stats_labels;
	extract($wpjam_stats_labels);
	wpjam_stats_header(array('show_date_type'=>true));
	?>
	<h2>用户订阅渠道统计分析</h2>

	<?php

	$weixin_qrcodes	= WEIXIN_Qrcode::get_all();

	// $weixin_qrcodes = $wpdb->get_results("SELECT concat('qrscene_',wwqr.scene) as new_scene, wwqr.* FROM $wpdb->weixin_qrcodes wwqr;");

	$qrcode_types = array();
	foreach ($weixin_qrcodes as $weixin_qrcode) {
		$qrcode_types['qrscene_'.$weixin_qrcode['scene']] = $weixin_qrcode['name'];
	}
	$qrcode_types['']	= '直接订阅';

	$qrcode_type	= $_GET['type']??'';

	$counts	= WEIXIN_AdminMessage::Query()->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where('MsgType','event')->where('Event','subscribe')->group_by('EventKey')->order_by('count')->get_results("count(*) as count, LOWER(EventKey) as label");

	
	// $total = $wpdb->get_var("SELECT count(id) FROM {$wpdb->weixin_messages} WHERE {$where}");
	$total	= WEIXIN_AdminMessage::Query()->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where('MsgType','event')->where('Event','subscribe')->get_var("count(id)");

	$counts	= array_filter($counts, function($count){
		return $count['count'] > 1;
	});

	$total_link	= $current_admin_url.'#daily-chart';

	wpjam_donut_chart($counts, array('total'=>$total,'show_link'=>true,'labels'=>$qrcode_types,'chart_width'=>280));
	?>

	<div class="clear"></div>

	<?php
	if($qrcode_type){
		// if($wpjam_compare){
			// $time_diff = strtotime($wpjam_start_date) - strtotime($wpjam_start_date_2);

			// // $sql = "SELECT FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as data FROM {$wpdb->weixin_messages} WHERE {$where} GROUP BY day ORDER BY day DESC;";

			// // $sql_2 = "SELECT FROM_UNIXTIME(CreateTime+{$time_diff}, '{$wpjam_date_format}') as day, count(id) as data2 FROM {$wpdb->weixin_messages} WHERE {$where_2} GROUP BY day ORDER BY day DESC;";
			
			// // $counts_array = $wpdb->get_results($sql,OBJECT_K);

			// // $counts_array_2 = $wpdb->get_results($sql_2,OBJECT_K);

			// $counts_array	= WEIXIN_AdminMessage::Query()->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where('MsgType','event')->where('Event','subscribe')->where('EventKey',$qrcode_type)->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as data");

			// $counts_array	= wp_list_pluck($counts_array, 'data', 'day');

			// $counts_array_2	= WEIXIN_AdminMessage::Query()->where_gt('CreateTime', $wpjam_start_timestamp_2)->where_lt('CreateTime', $wpjam_end_timestamp_2)->where('MsgType','event')->where('Event','subscribe')->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as data");

			// $counts_array_2	= wp_list_pluck($counts_array_2, 'data', 'day');

			// $new_counts_array = array();

			// foreach ($counts_array as $day => $data) {
			// 	$new_counts_array[$day]['data'] = $data;
			// }

			// foreach ($counts_array_2 as $day => $data) {
			// 	$new_counts_array[$day]['data2'] = $data;
			// }

			// $labels = array(
			// 	'data'	=> $compare_label,
			// 	'data2'	=> $compare_label_2
			// );

			// wpjam_line_chart($new_counts_array, $labels);

		// }else{
			
			$counts_array	= WEIXIN_AdminMessage::Query()->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where('MsgType','event')->where('Event','subscribe')->where('EventKey', $qrcode_type)->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as {$qrcode_type}");

			$counts_array	= array_combine(array_column($counts_array, 'day'), $counts_array);
			
			wpjam_line_chart($counts_array, array($qrcode_type=>$qrcode_types[$qrcode_type]));
		// }
		
	}else{
		// $sql = "SELECT FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as total FROM {$wpdb->weixin_messages} WHERE {$where} GROUP BY day ORDER BY day DESC;";

		// $counts_array = $wpdb->get_results($sql,OBJECT_K);

		// $counts_array	= WEIXIN_AdminMessage::Query()->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where('MsgType','event')->where('Event','subscribe')->group_by('day')->order_by('day')->get_results("FROM_UNIXTIME(CreateTime, '{$wpjam_date_format}') as day, count(id) as total");

		// $counts_array	= array_combine(array_column($counts_array, 'day'), $counts_array);

		// wpjam_line_chart($counts_array, array('total'=>'所有'));
	}
}

// 用户属性
function weixin_user_summary_page(){
	global $wpdb, $plugin_page, $current_admin_url, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	$type = 'all';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$type = $_POST['type'];
	}

	?>

	<h2>用户属性</h2>
	<?php /*if(WEIXIN_TYPE >= 3) { ?>
	<form action="<?php echo $current_admin_url ?>" method="post" style="margin-top:15px;">
		<input name="type" type="radio" id="type" onclick="javascript: submit();" value="all" <?php checked("all", $type);?>>所有用户 
		<input name="type" type="radio" id="type" onclick="javascript: submit();" value="subscribe" <?php checked("subscribe", $type);?>>订阅用户 
		<input name="type" type="radio" id="type" onclick="javascript: submit();" value="unsubscribe" <?php checked("unsubscribe", $type);?>>取消订阅 
	</form>
	<?php } */?>
	<?php 

	$where = "subscribe_time !='' AND subscribe = 1";
	

	// if($type == 'all'){
	// 	$subscribe_tab	= array(
	// 		'name'		=>'用户订阅',
	// 		'counts_sql'=> "SELECT count(openid) as count, subscribe as label FROM {$wpdb->weixin_users} WHERE {$where} GROUP BY subscribe ORDER BY count DESC;",
	// 		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where}",
	// 		'labels'	=> array('0'=>'取消订阅', '1'=>'订阅')
	// 	);
	// }elseif($type == 'subscribe'){
	// 	$where .= " AND subscribe = 1";
	// }else{
	// 	$where .= " AND subscribe = 0";
	// }

	// $where .= " AND subscribe = 1";

	$sex_tab	= array(
		'name'		=>'用户性别',
		'counts_sql'=> "SELECT count(openid) as count, sex as label FROM {$wpdb->weixin_users} WHERE {$where} GROUP BY sex ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where}",
		'labels'	=> array('0'=>'未知', '1'=>'男', '2'=>'女'),
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$language_tab	= array(
		'name'		=>'用户语言',
		'counts_sql'=> "SELECT count(openid) as count, language as label FROM {$wpdb->weixin_users} WHERE {$where} GROUP BY language ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where}",
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$country_tab	= array(
		'name'		=>'国家和地区',
		'counts_sql'=> "SELECT count(openid) as count, country as label FROM {$wpdb->weixin_users} WHERE {$where} AND country != '' GROUP BY country ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND country != ''",
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$province_tab	= array(
		'name'		=>'省份',
		'counts_sql'=> "SELECT count(openid) as count, province as label FROM {$wpdb->weixin_users} WHERE {$where} AND country = '中国' AND province !='' GROUP BY province ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND country = '中国' AND province !='' ",
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$city_tab	= array(
		'name'		=>'城市',
		'counts_sql'=> "SELECT count(openid) as count, city as label FROM {$wpdb->weixin_users} WHERE {$where} AND country = '中国' AND city !='' GROUP BY city ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND country = '中国' AND city !=''",
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$tabs = array();

	
	// if($type == 'all'){
	// 	$tabs['subscribe'] = $subscribe_tab;
	// }
	$tabs = array_merge($tabs, array('sex'=>$sex_tab,'language'=>$language_tab,'country'=>$country_tab,'province'=>$province_tab,'city'=>$city_tab));

	wpjam_sub_summary($tabs);
}

// 手机设备
function weixin_user_devices_page(){
	global $wpdb, $plugin_page, $current_admin_url, $wpjam_stats_labels;
	extract($wpjam_stats_labels);
	?>
	<h2>手机设备</h2>
	<?php 

	$where = "subscribe_time !='' AND subscribe = 1";

	$os_tab	= array(
		'name'		=>'操作系统',
		'counts_sql'=> "SELECT count(openid) as count, os as label FROM {$wpdb->weixin_users} WHERE {$where} AND os != '' GROUP BY os ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND os != ''",
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$ios_tab	= array(
		'name'		=>'iOS 版本',
		'counts_sql'=> "SELECT count(openid) as count, os_ver as label FROM {$wpdb->weixin_users} WHERE {$where} AND os = 'iOS' AND os_ver !='' GROUP BY os_ver ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND os = 'iOS' AND os_ver !='' "
	);

	$android_tab	= array(
		'name'		=>'安卓版本',
		'counts_sql'=> "SELECT count(openid) as count, os_ver as label FROM {$wpdb->weixin_users} WHERE {$where} AND os = 'Android' AND os_ver !='' GROUP BY os_ver ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND os = 'Android' AND os_ver !='' "
	);

	$weixin_tab	= array(
		'name'		=>'微信版本',
		'counts_sql'=> "SELECT count(openid) as count, weixin_ver as label FROM {$wpdb->weixin_users} WHERE {$where} AND weixin_ver != '' GROUP BY weixin_ver ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND weixin_ver != ''"
	);

	$brand_tab	= array(
		'name'		=>'手机品牌',
		'counts_sql'=> "SELECT count(openid) as count, brand as label FROM {$wpdb->weixin_users} wut LEFT JOIN $wpdb->devices wdt ON trim(wut.device) = wdt.device WHERE {$where} AND wut.device != '' GROUP BY brand ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND device != ''",
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$devices = wpjam_get_devices();

	$device_tab	= array(
		'name'		=>'手机型号',
		'counts_sql'=> "SELECT count(openid) as count, (case when device = 'iPhone' AND screen_width > 0 then concat(device,'_',screen_width,'x',screen_height) else device end ) as label FROM {$wpdb->weixin_users} WHERE {$where} AND device != '' GROUP BY label ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND device != ''",
		'labels'	=> $devices,
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$size_tab	= array(
		'name'		=>'屏幕尺寸',
		'counts_sql'=> "SELECT count(openid) as count, size as label FROM (SELECT openid, (case when device = 'iPhone' AND screen_width > 0 then concat(device,'_',screen_width,'x',screen_height) else device end ) as device FROM {$wpdb->weixin_users} WHERE {$where} AND device != '') wut LEFT JOIN $wpdb->devices wdt ON trim(wut.device) = wdt.device GROUP BY size ORDER BY count DESC;",
		'total_sql'	=> "SELECT count(openid) FROM {$wpdb->weixin_users} WHERE {$where} AND device != ''",
		'labels'	=> $devices,
		'link'		=> admin_url('admin.php?page=weixin-users')
	);

	$tabs = array();

	if(function_exists('weixin_robot_insert_pageview')){
		$tabs = array_merge($tabs, array('os'=>$os_tab,'ios'=>$ios_tab,'android'=>$android_tab,'weixin'=>$weixin_tab,'brand'=>$brand_tab,'device'=>$device_tab,'size'=>$size_tab));
	}
	
	wpjam_sub_summary($tabs);
}

function weixin_robot_get_subscribe_count(){
	global $wpdb;
	return $wpdb->get_var("SELECT count(*) FROM  $wpdb->weixin_users WHERE subscribe = 1;");
}

// 微信用户活跃度
function weixin_user_activity_page(){ ?>
	<h2>用户活跃度</h2>
	<?php
	$times = array(
		1	=> '1天内',
		3	=> '3天内',
		7	=> '7天内',
		15	=> '15天内',
		30	=> '1个月',
		//'90'	=> '3个月',
		//'365'	=> '1年',
	);

	$now				= time();
	$subscribe_count	= weixin_robot_get_subscribe_count();

	$counts_array		= array();

	foreach ($times as $key => $value) {
		$start		= $now - (DAY_IN_SECONDS*$key);
		$activity	= weixin_user_sub_activity($start);
		$percent	= round($activity / $subscribe_count, 4)*100;
		$counts_array[$value]	= array('users'=>$activity, 'percent'=>$percent.'%');
	}

	$labels = array('users'=>'活跃用户', 'percent'=>'所占比率%');

	wpjam_bar_chart($counts_array, $labels, array('day_label'=>'时长'));
}

function weixin_user_sub_activity($start){
	global $wpdb;
	return count($wpdb->get_results("SELECT FromUserName, COUNT( * ) AS count FROM  {$wpdb->weixin_messages} WHERE CreateTime > {$start} AND FromUserName !=''  GROUP BY FromUserName "));
}

function weixin_user_loyalty_page(){ ?>
	<h2>用户忠诚度</h2>
	<?php
	$times = array(
		30		=> '1个月内',
		90		=> '1-3个月',
		180		=> '3-6个月',
		365		=> '6个月-1年',
		366		=> '1年以上'
		//'90'	=> '3个月',
		//'365'	=> '1年'
	);

	$now				= time();
	$subscribe_count	= weixin_robot_get_subscribe_count();

	$counts_array		= array();

	$pre_start = $now;
	foreach ($times as $key => $value) {
		$start		= ($key>365)?0:$now - (DAY_IN_SECONDS*$key);
		$loyalty	= weixin_user_sub_loyalty($pre_start,$start);
		$percent	= round($loyalty / $subscribe_count, 4)*100;

		$counts_array[$value]	= array('users'=>$loyalty, 'percent'=>$percent.'%');
		$pre_start	= $start;
	}

	$labels = array('users'=>'用户',	'percent'=>'所占比率%');

	wpjam_bar_chart($counts_array, $labels, array('day_label'=>'关注时长'));
}

function weixin_user_sub_loyalty($pre, $start){
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT( * ) AS count FROM {$wpdb->weixin_users} WHERE subscribe = 1 AND subscribe_time < {$pre} AND subscribe_time > {$start}");
}

// 活跃用户
function weixin_user_hot_stats_page(){
	global $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	$tabs = array('messages'	=> '最多互动');

	if(function_exists('weixin_robot_insert_pageview')){
		$tabs['views']	= '最多浏览';
		$tabs['shares']	= '最多分享';
		$tabs['refers']	= '最多推荐';
	}
	?>
	<h2>影响力</h2>

	<?php wpjam_stats_header(); ?>

	<h2 class="nav-tab-wrapper">
    <?php foreach ($tabs as $key => $name) { ?>
        <a class="nav-tab" href="javascript:;" id="tab-title-<?php echo $key;?>"><?php echo $name;?></a>   
    <?php }?>
    </h2>

    <?php foreach ($tabs as $key => $name) { ?>
    <div id="tab-<?php echo $key;?>" class="div-tab" style="margin-top:1em;">
    	<?php weixin_user_sub_hot_stats_page($key)?>
    </div>
    <?php }
}

function weixin_user_sub_hot_stats_page($tab='messages'){
	global $wpdb, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	if($tab == 'messages'){
		$types = WEIXIN_Message::get_message_types();

		$where = " CreateTime > {$wpjam_start_timestamp} AND CreateTime < {$wpjam_end_timestamp}";

		$sum = array();
		foreach (array_keys($types) as $message_type){
			$sum[] = "SUM(case when MsgType='{$message_type}' then 1 else 0 end) as {$message_type}";
		}
		$sum = implode(', ', $sum);

		$sql = "SELECT COUNT( * ) AS total, FromUserName, {$sum} FROM {$wpdb->weixin_messages} WHERE {$where} GROUP BY FromUserName ORDER BY total DESC LIMIT 0,150 ";
	}elseif($tab == 'views'){
		$types =  weixin_robot_get_source_types();

		$where = " time > {$wpjam_start_timestamp} AND time < {$wpjam_end_timestamp} AND weixin_openid != ''";

		$sum = array();
		foreach (array_keys($types) as $type){
			$sum[] = "SUM(case when sub_type='{$type}' then 1 else 0 end) as {$type}";
		}
		$sum = implode(', ', $sum);

		$sql = "SELECT COUNT( * ) AS total, weixin_openid, {$sum} FROM {$wpdb->weixin_pageviews} WHERE {$where} AND type = 'View' GROUP BY weixin_openid ORDER BY total DESC LIMIT 0,150 ";
	}elseif($tab == 'shares'){
		$types =  weixin_robot_get_share_types();

		$where = " time > {$wpjam_start_timestamp} AND time < {$wpjam_end_timestamp} AND weixin_openid != ''";

		$sum = array();
		foreach (array_keys($types) as $type){
			$sum[] = "SUM(case when sub_type='{$type}' then 1 else 0 end) as {$type}";
		}
		$sum = implode(', ', $sum);

		$sql = "SELECT COUNT( * ) AS total, weixin_openid, {$sum} FROM {$wpdb->weixin_pageviews} WHERE {$where} AND type = 'Share' GROUP BY weixin_openid ORDER BY total DESC LIMIT 0,150 ";
	}elseif($tab == 'refers'){
		$types =  weixin_robot_get_source_types();

		$where = " time > {$wpjam_start_timestamp} AND time < {$wpjam_end_timestamp} AND weixin_openid != ''";

		$sum = array();
		foreach (array_keys($types) as $type){
			$sum[] = "SUM(case when sub_type='{$type}' then 1 else 0 end) as {$type}";
		}
		$sum = implode(', ', $sum);

		$sql = "SELECT COUNT( * ) AS total, refer, {$sum} FROM {$wpdb->weixin_pageviews} WHERE {$where} AND type = 'View' AND refer !='' GROUP BY refer ORDER BY total DESC LIMIT 0,150 ";
	}
	
	$counts = $wpdb->get_results($sql);
	$types = array('total'=>'所有') + $types;

	if($counts){
	?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>排名</th>
				<th>用户</th>
				<?php foreach ($types as $key=>$value) {?>
				<th><?php echo $value;?></th>
				<?php }?>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th>排名</th>
				<th>用户</th>
				<?php foreach ($types as $key=>$value) {?>
				<th><?php echo $value;?></th>
				<?php }?>
			</tr>
		</tfoot>

		<tbody>
		<?php  $i=1;?>
		<?php foreach ($counts as $count) { 
			if($i > 100 ) break;
			$alternate = empty($alternate)?'alternate':'';

			if($tab == 'messages') {
				$weixin_openid = $count->FromUserName;
			}elseif($tab == 'views') {
				$weixin_openid = $count->weixin_openid;
			}elseif($tab == 'shares') {
				$weixin_openid = $count->weixin_openid;
			}elseif($tab == 'refers'){
				$weixin_openid = $count->refer;
			}
			
			$weixin_user = WEIXIN_User::get($weixin_openid);
			$weixin_user = WEIXIN_AdminUser::parse_user($weixin_user);
			if($weixin_user){ 
			?>
			<tr class="<?php echo $alternate;?>">
				<td><?php echo $i; $i++;?></td>
				<td><?php echo $weixin_user['username'];?></td>
				<?php foreach ($types as $key=>$value) {?>
				<td><?php if($count->$key){ ?>
				<a href="<?php echo $weixin_user['link'].'&tab='.$tab;?>"><?php echo $count->$key;?></a>
				<?php } else { ?>
				<?php echo $count->$key;?>
				<?php }?></td>
				<?php }?>
			</tr>
			<?php }?>
		<?php } ?>
		</tbody>
	</table>
	<?php
	} else{
		echo '<p>暂无数据</p>';
	}
}