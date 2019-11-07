<?php
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/trait-weixin-stats.php');
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-user-stats.php');
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-article-stats.php');
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-userread-stats.php');
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-usershare-stats.php');
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-message-stats.php');
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-messagedist-stats.php');
include_once(WEIXIN_ROBOT_PLUGIN_DIR.'extends/includes/class-weixin-interface-stats.php');

function weixin_mp_daily_stats_page(){
	global $wpdb, $wpjam_stats_labels, $plugin_page, $current_admin_url;
	extract($wpjam_stats_labels);

	$appid	= weixin_get_appid();
	$type	= isset($_GET['type'])?$_GET['type']:'all';

	if($plugin_page == 'weixin-mp-stats'){
		$table	= WEIXIN_UserStats::get_table();

		if(isset($_GET['date'])){
			$date = $_GET['date'];// 强制重新抓取一天的数据
			WEIXIN_UserStats::delete(array('appid'=>$appid,'ref_date'=>$date));
			WEIXIN_UserStats::sync();
		}

		$labels_all = $types = WEIXIN_UserStats::$types;
		$labels_all['cumulate_user']	= '总用户数#';
		$labels_all['percent1'] 		= '取消率% / 新增用户';
		$labels_all['percent2'] 		= '取消率% / 总用户数';

		$sum_sql	= "SUM(cumulate_user) as cumulate_user, SUM(new_user) AS new_user, SUM(cancel_user) AS cancel_user, (SUM(new_user) - SUM(cancel_user)) as net_user, CONCAT(ROUND(SUM(cancel_user)/SUM(new_user) * 100,2),'%') as percent1, CONCAT(ROUND(SUM(cancel_user)/SUM(cumulate_user) * 100,4),'%') as percent2";

		echo '<h3>每日用户增长</h3>';
	}elseif($plugin_page == 'weixin-mp-read-stats'){
		$table	= WEIXIN_UserReadStats::get_table();

		if(isset($_GET['date'])){
			$date = $_GET['date'];
			WEIXIN_UserReadStats::delete(array('appid'=>$appid,'ref_date'=>$date));
			WEIXIN_UserReadStats::sync();
			
			WEIXIN_UserShare::delete(array('appid'=>$appid,'ref_date'=>$date));
			WEIXIN_UserShare::sync();
		}
		
		$types	= WEIXIN_ArticleStats::get_types();
		unset($types['target_user']);
		unset($types['int_page_read_count_rate']);
		unset($types['int_page_read_user_rate']);

		$sum_sql	= array();
		foreach ($types as $key => $value) {
			$sum_sql[] = 'SUM('.$key.') as '.$key.' ';
		}

		$sum_sql = implode(',', $sum_sql);

		echo '<h3>图文统计分析</h3>';
	}elseif($plugin_page == 'weixin-mp-message-stats'){
		$table	= WEIXIN_MessageStats::get_table();

		if(isset($_GET['date'])){
			$date = $_GET['date'];

			WEIXIN_MessageStats::delete(array('appid'=>$appid,'ref_date'=>$date));
			WEIXIN_MessageStats::sync();

			WEIXIN_MessageDistStats::delete(array('appid'=>$appid,'ref_date'=>$date));
			WEIXIN_MessageDistStats::sync();
		}

		$labels_all = $types = WEIXIN_MessageStats::$fields;
		$labels_all['average'] .='#';

		$sum_sql	= "SUM(msg_user) as msg_user ,SUM(msg_count) as msg_count , SUM(msg_count)/SUM(msg_user) as average";

		echo '<h3>消息统计分析</h3>';
	}elseif($plugin_page == 'weixin-mp-interface-stats'){
		$table	= WEIXIN_InterfaceStats::get_table();

		if(isset($_GET['date'])){
			$date = $_GET['date'];

			WEIXIN_InterfaceStats::delete(array('appid'=>$appid,'ref_date'=>$date));
			WEIXIN_InterfaceStats::sync();
		}

		$labels_all = $types = WEIXIN_InterfaceStats::$types;
		$labels_all['fail_percent']	.='%';
		$labels_all['avg_time_cost'].='#';
		$labels_all['max_time_cost'].='#';

		$sum_sql	= "callback_count, fail_count,  CONCAT(ROUND(fail_count/callback_count * 100,2),'%') as fail_percent, ROUND(total_time_cost/callback_count,2) as avg_time_cost, max_time_cost";

		echo '<h2>接口统计分析</h2>';
	}

	wpjam_stats_header(array('show_compare'=>true));

	echo '<p>';
	$class = ('all' == $type)? 'button button-primary' : 'button button-secondary';
	echo '<a class="'.$class.'" href="'.$current_admin_url.'&type=all">所有</a> ';
	foreach ($types as $key => $value) {
		$class = ($key == $type)? 'button button-primary' : 'button button-secondary';
		echo '<a class="'.$class.'" href="'.$current_admin_url.'&type='.$key.'">'.$value.'</a> ';
	}
	echo '</p>';

	$where	= " appid = '{$appid}' AND ref_date >= '{$wpjam_start_date}' AND ref_date <= '{$wpjam_end_date}'";

	if($plugin_page == 'weixin-mp-stats' && $type == 'all'){
		$total_sql = "SELECT SUM(new_user) as new_user, SUM(cancel_user) as cancel_user, SUM(new_user-cancel_user) as net_user, CONCAT(ROUND(SUM(cancel_user)/SUM(new_user) * 100,2),'%') as percent, MAX(cumulate_user) as cumulate_user FROM {$table} WHERE {$where}";
		$totals	= $wpdb->get_row($total_sql);
		echo '<p>从 '.$wpjam_start_date.' 到 '.$wpjam_end_date.' 这段时间内，新增用户 <span class="green">'.$totals->new_user.' </span>人，取消关注<span class="red"> '.$totals->cancel_user.' </span>人，取消率<span class="red"> '.$totals->percent.'</span>，净增长 <span class="green">'.$totals->net_user.' </span> 人，总用户数 <span class="green">'.$totals->cumulate_user.' </span>。</p>';
	}

	$sql	= "SELECT ref_date as day, {$sum_sql} FROM {$table} WHERE {$where} GROUP BY ref_date ORDER BY ref_date DESC";

	if($wpjam_compare && $type != 'all'){

		$counts_array	= $wpdb->get_results($sql);
		$where_2		= " appid = '{$appid}' AND ref_date >= '{$wpjam_start_date_2}' AND ref_date <= '{$wpjam_end_date_2}'";
		$counts_array_2	= $wpdb->get_results("SELECT ref_date as day, {$sum_sql} FROM {$table} WHERE {$where_2} GROUP BY ref_date ORDER BY ref_date DESC");

		$new_counts_array = array();

		foreach ($counts_array as $i=>$counts) {
			$new_counts_array[$counts->day]['data']		= $counts->$type;
			$new_counts_array[$counts->day]['data2']	= isset($counts_array_2[$i])?$counts_array_2[$i]->$type:0;
		}

		$counts_array = $new_counts_array;

		$labels = array(
			'data'	=> $compare_label,
			'data2'	=> $compare_label_2
		);
	}else{
		$counts_array = $wpdb->get_results($sql,OBJECT_K);

		$labels = array();
		if($type == 'all'){
			$labels = isset($labels_all)?$labels_all:$types;
		}else{
			$labels = array($type => $types[$type]);
		}
	}

	wpjam_line_chart(
		$counts_array,
		$labels
	);
}

function weixin_mp_summary_page(){
	global $wpdb, $wpjam_stats_labels, $plugin_page, $current_tab, $current_admin_url;
	extract($wpjam_stats_labels);

	$type		= isset($_GET['type'])?$_GET['type']:'all';
	$appid		= weixin_get_appid();
	$where		= " appid = '{$appid}' AND ref_date >= '{$wpjam_start_date}' AND ref_date <= '{$wpjam_end_date}'";

	if($plugin_page == 'weixin-mp-stats'){
		if($current_tab == 'new'){
			$table			= WEIXIN_UserStats::get_table();
			$label_field	= 'user_source';
			$count_filed	= 'new_user';
			$types 			= WEIXIN_UserStats::$sources;

			echo '<h3>新增用户统计分析</h3>';
		}elseif($current_tab == 'cancle'){
			$table			= WEIXIN_UserStats::get_table();
			$label_field	= 'user_source';
			$count_filed	= 'cancel_user';
			$types 		= WEIXIN_UserStats::$sources;

			echo '<h3>取消关注统计分析</h3>';
		}elseif($current_tab == 'net'){
			$table			= WEIXIN_UserStats::get_table();
			$label_field	= 'user_source';
			$count_filed	= 'new_user - cancel_user';
			$types 		= WEIXIN_UserStats::$sources;

			echo '<h3>净增长统计分析</h3>';
		}
	}elseif($plugin_page == 'weixin-mp-read-stats'){
		if($current_tab == 'read'){
			$table			= WEIXIN_UserReadStats::get_table();
			$label_field	= 'user_source';
			$count_filed	= 'int_page_read_count';
			$types 			= WEIXIN_UserReadStats::$types;

			echo '<h3>阅读统计分析</h3>';
		}elseif($current_tab == 'share'){
			$table			= WEIXIN_UserShareStats::get_table();
			$label_field	= 'share_scene';
			$count_filed	= 'share_count';
			$types 			= WEIXIN_UserShareStats::$types;

			echo '<h3>分享统计分析</h3>';
		}
	}elseif($plugin_page == 'weixin-mp-message-stats'){
		if($current_tab == 'type'){
			$table			= WEIXIN_MessageStats::get_table();
			$label_field	= 'msg_type';
			$count_filed	= 'msg_count';
			$types 			= WEIXIN_MessageStats::$types;

			echo '<h3>消息类型统计分析</h3>';
		}elseif($current_tab == 'dist'){
			$table			= WEIXIN_MessageDistStats::get_table();
			$label_field	= 'count_interval';
			$count_filed	= 'msg_user';
			$types 			= WEIXIN_MessageDistStats::$types;

			echo '<h3>消息分布统计分析</h3>';
		}
	}

	wpjam_stats_header(array('show_compare'=>true));

	if($wpjam_compare && $type!='all'){
		$where_2	= " appid = '{$appid}' AND ref_date >= '{$wpjam_start_date_2}' AND ref_date <= '{$wpjam_end_date_2}'";
		$count		= $wpdb->get_var("SELECT SUM({$count_filed}) as count FROM {$table} WHERE {$label_field}={$type} AND {$where}  GROUP BY {$label_field} ORDER BY count DESC");
		$count_2	= $wpdb->get_var("SELECT SUM({$count_filed}) as count FROM {$table} WHERE {$label_field}={$type} AND {$where_2} GROUP BY {$label_field} ORDER BY count DESC");

		$counts		= array();
		$counts[] 	= array(
			'label'=>$compare_label, 
			'count'=>$count
		);

		$counts[] = array(
			'label'=>$compare_label_2, 
			'count'=>$count_2
		);

		wpjam_donut_chart($counts, array('chart_width'=>280));

	}else{

		if($type == 'all'){
			$sql = "SELECT {$label_field} as label, SUM({$count_filed}) as count FROM {$table} WHERE {$where}  GROUP BY {$label_field} ORDER BY count DESC";
		}else{
			$sql = "SELECT {$label_field} as label, SUM({$count_filed}) as count FROM {$table} WHERE {$label_field}={$type} AND {$where}  GROUP BY {$label_field} ORDER BY count DESC";
		}
		
		$counts = $wpdb->get_results($sql);
		$total 	= $wpdb->get_var("SELECT SUM({$count_filed}) FROM {$table} WHERE {$where}");

		if($counts){
			wpjam_donut_chart(
				$counts, 
				array(
					'total'			=>$total,
					'chart_width'	=>280,
					'table_width'	=>320,
					'show_link'		=>true,
					'labels'		=>$types
				)
			);
		}
	}
	?>

	<div class="clear"></div>

	<?php

	if($wpjam_compare && $type!='all'){
		$counts_array	= $wpdb->get_results("SELECT ref_date as day, ({$count_filed}) as count  FROM {$table} WHERE {$label_field}={$type} AND {$where}  ORDER BY day DESC;");
		$counts_array_2 = $wpdb->get_results("SELECT ref_date as day,({$count_filed}) as count  FROM {$table} WHERE {$label_field}={$type} AND {$where_2}  ORDER BY day DESC;");

		if($counts_array){

			$new_counts_array = array();

			foreach ($counts_array as $i=>$counts) {
				$new_counts_array[$counts->day]['data']		= $counts->count;
				$new_counts_array[$counts->day]['data2']	= isset($counts_array_2[$i])?$counts_array_2[$i]->count:0;
			}

			$labels = array(
				'data'	=> $compare_label,
				'data2'	=> $compare_label_2
			);

			wpjam_line_chart($new_counts_array, $labels);
		}
	}elseif($type!='all'){
		if($counts_array = $wpdb->get_results("SELECT ref_date as day, ({$count_filed}) as count  FROM {$table} WHERE {$label_field}={$type} AND {$where}  ORDER BY day DESC;", OBJECT_K)){
			wpjam_line_chart($counts_array, array('count'=>$types[$type]));
		}

	}else{
		$counts_array 	= $wpdb->get_results("SELECT ref_date as day, {$label_field} as label, {$count_filed} as count  FROM {$table} WHERE {$where}  ORDER BY day DESC;");
		$new_counts		= array();

		foreach ($counts_array as $count) {
			$day = $count->day;
			$new_counts[$day]['total'] = isset($new_counts[$day]['total'])?$new_counts[$day]['total']+$count->count:$count->count;
			$new_counts[$day][$count->label] =  $count->count;
		}

		$types = array('total'=>'所有')+$types;
		wpjam_line_chart($new_counts, $types);
	}
}

function weixin_mp_user_summary_page(){
	global $wpdb, $wpjam_stats_labels, $current_tab, $current_admin_url;
	extract($wpjam_stats_labels);

	$appid	= weixin_get_appid();
	$table	= WEIXIN_UserStats::get_table();
	$where	= "appid = '{$appid}' AND ref_date >= '{$wpjam_start_date}' AND ref_date <= '{$wpjam_end_date}'";

	$counts_array 	= $wpdb->get_results("SELECT user_source, SUM(new_user) as new_user, SUM(cancel_user) as cancel_user, SUM(new_user-cancel_user) as net_user, CONCAT(ROUND(SUM(cancel_user)/SUM(new_user) * 100,2),'%') as percent FROM {$table} WHERE {$where} GROUP BY user_source ORDER BY new_user DESC", OBJECT_K);

	$totals			= $wpdb->get_row("SELECT SUM(new_user) as new_user, SUM(cancel_user) as cancel_user, SUM(new_user-cancel_user) as net_user, CONCAT(ROUND(SUM(cancel_user)/SUM(new_user) * 100,2),'%') as percent, MAX(cumulate_user) as cumulate_user FROM {$table} WHERE {$where}");

	$counts_array['所有#'] = $totals;

	echo '<h3>用户增长渠道汇总</h3>';
	
	wpjam_stats_header();

	wpjam_bar_chart(
		$counts_array, 
		array(
			'new_user'		=>'新增用户', 
			'cancel_user'	=>'取消关注', 
			'net_user'		=>'净增长', 
			'percent'		=>'取消率%',  
		),
		array(
			'show_avg'		=>false, 
			'show_sum'		=>false,
			'day_label'		=>'渠道',
			'day_key'		=>'user_source',
			'day_labels'	=>WEIXIN_UserStats::$sources
		)
	);
}

function weixin_mp_article_daily_read_page(){
	if(!empty($_GET['msgid'])){
		weixin_mp_article_single_stats_page();
		return;
	}

	global $wpdb, $current_admin_url, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	$table = WEIXIN_ArticleStats::get_table();
	$today	= date('Y-m-d',current_time('timestamp'));
	$appid	= weixin_get_appid();
	$types	= WEIXIN_ArticleStats::get_types();
	unset($types['int_page_read_count_rate']);
	unset($types['int_page_read_user_rate']);

	if($wpjam_date >= $today){
		$wpjam_date = date('Y-m-d',current_time('timestamp')-DAY_IN_SECONDS);
	}

	if(!WEIXIN_ArticleStats::Query()->where('appid', $appid)->where('ref_date', $wpjam_date)->get_results()){
		WEIXIN_ArticleStats::sync();
	}
	
	wpjam_stats_header(array('show_start_date'=>false));
	
	echo '<h3>'.$wpjam_date.' 图文阅读统计</h3>';
	$results	=	$wpdb->get_results($wpdb->prepare("SELECT msgid, wma.* FROM $table  wma WHERE appid = '{$appid}' AND stat_date = %s AND sort != 0 ORDER BY ref_date DESC", $wpjam_date), OBJECT_K);

	$wpjam_date_prev = date('Y-m-d',strtotime($wpjam_date)-DAY_IN_SECONDS);
	$results_prev	=	$wpdb->get_results($wpdb->prepare("SELECT msgid, wma.* FROM $table wma WHERE appid = '{$appid}' AND stat_date = %s AND sort != 0 ORDER BY int_page_read_user DESC", $wpjam_date_prev), OBJECT_K);

	$new_results = array();
	foreach ($results as $msgid => $result) {
		$new_result = $result;
	
		if(isset($results_prev[$msgid])){
			$new_result->int_page_read_user		= $result->int_page_read_user - $results_prev[$msgid]->int_page_read_user;
			$new_result->int_page_read_count	= $result->int_page_read_count - $results_prev[$msgid]->int_page_read_count;
			$new_result->ori_page_read_user		= $result->ori_page_read_user - $results_prev[$msgid]->ori_page_read_user;
			$new_result->ori_page_read_count	= $result->ori_page_read_count - $results_prev[$msgid]->ori_page_read_count;
			$new_result->share_user				= $result->share_user - $results_prev[$msgid]->share_user;
			$new_result->share_count			= $result->share_count - $results_prev[$msgid]->share_count;
			$new_result->add_to_fav_user		= $result->add_to_fav_user - $results_prev[$msgid]->add_to_fav_user;
			$new_result->add_to_fav_count		= $result->add_to_fav_count - $results_prev[$msgid]->add_to_fav_count;
		}
	}

	if(!$results){
		echo '<p>暂无数据</p>';
		return;
	}

	?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th style="width:150px;">标题</th>
				<th>发布日期</th>
				<th>第几条</th>
				<?php foreach ($types as $key => $value) { ?>
				<th><?php echo $value;?></th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>标题</th>
				<th>发布日期</th>
				<th>第几条</th>
				<?php foreach ($types as $key => $value) { ?>
				<th><?php echo $value;?></th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
		<?php $i = 0; foreach ($results as $result) { ?>
			<tr class="<?php $alternate	= empty($alternate)?'alternate':''; echo $alternate;?>">
				<td>
				<?php if($result->msgid){ ?>
				<a href="<?php echo $current_admin_url.'&msgid='.$result->msgid; ?>"><?php echo $result->title; ?></a>
				<?php }else{ ?>
				<?php echo $result->title; ?>
				<?php } ?>
				</td>
				<td><?php echo $result->ref_date; ?></td>
				<td>第<?php echo $result->sort;?>条</td>
				<?php foreach ($types as $key => $value) { ?>
				<td><?php 
				echo $result->$key;
				if($key != 'target_user'){
					echo '<br />';
					echo weixin_get_mp_article_stats_round($result, $key);
				}
				?></td>
				<?php } ?>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
}

function weixin_mp_article_daily_stats_page(){
	if(!empty($_GET['msgid'])){
		weixin_mp_article_single_stats_page();
		return;
	}

	global $wpdb, $current_admin_url, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	$today	= date('Y-m-d',current_time('timestamp'));
	$table	= WEIXIN_ArticleStats::get_table();
	$appid	= weixin_get_appid();
	$types	= WEIXIN_ArticleStats::get_types();

	if($wpjam_date >= $today){
		$wpjam_date = date('Y-m-d',current_time('timestamp')-DAY_IN_SECONDS);
	}elseif(!WEIXIN_ArticleStats::Query()->where('appid', $appid)->where('ref_date', $wpjam_date)->get_results()){
		WEIXIN_ArticleStats::sync();
	}
	
	unset($types['int_page_read_count_rate']);
	unset($types['int_page_read_user_rate']);
	unset($types['target_user']);

	wpjam_stats_header(array('show_start_date'=>false));

	echo '<h3>'.$wpjam_date.' 图文群发统计</h3>';
	$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE appid = '{$appid}' AND ref_date = %s AND sort != 0 ORDER BY sort ASC, day ASC", $wpjam_date));

	if($results){
		$target_user = $results[0]->target_user;
		echo '<p>群发用户：<strong>'.$target_user.'</strong></p>';
	}else{
		echo '<p>暂无数据</p>';
		return;
	}

	$rowspan	= $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $table WHERE appid = '{$appid}' AND ref_date = %s AND sort = 1", $wpjam_date));
	$sorts		= (count($results))/$rowspan;

	if($sorts > 1){
		$sum_sql	= '';
		$avg_sql	= '';

		foreach ($types as $key => $value) {
			$sum_sql .= 'SUM('.$key.') as '.$key.', ';
			$avg_sql .= 'ROUND(AVG('.$key.')) as '.$key.', ';
		}

		$sum_results	= $wpdb->get_results($wpdb->prepare("SELECT $sum_sql '' as sort, '' as msgid, '累加' as title, stat_date, target_user FROM $table WHERE appid = '{$appid}' AND ref_date = %s AND sort != 0 GROUP BY day", $wpjam_date));
		$avg_results	= $wpdb->get_results($wpdb->prepare("SELECT $avg_sql '' as sort, '' as msgid, '平均' as title, stat_date, target_user FROM $table WHERE appid = '{$appid}' AND ref_date = %s AND sort != 0 GROUP BY day", $wpjam_date));

		$results = array_merge($results, $sum_results, $avg_results);
	}
	?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th style="width:150px;">标题</th>
				<th>天</th>
				<?php foreach ($types as $key => $value) { ?>
				<th><?php echo $value;?></th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>标题</th>
				<th>天</th>
				<?php foreach ($types as $key => $value) { ?>
				<th><?php echo $value;?></th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
		<?php $i = 0; foreach ($results as $result) { ?>
			<tr class="<?php $alternate	= empty($alternate)?'alternate':''; echo $alternate;?>">

				<?php if($i%$rowspan == 0){ $i=0; ?>
				<td rowspan="<?php echo $rowspan; ?>">
				<?php if($result->sort){ echo '<strong>'.$result->sort.'</strong>.'; }?>
				<?php if($result->msgid){ ?>
				<a href="<?php echo $current_admin_url.'&msgid='.$result->msgid; ?>"><?php echo $result->title; ?></a>
				<?php }else{ ?>
				<?php echo $result->title; ?>
				<?php } ?>
				</td>
				<?php } ?>
				<?php $i++; ?>

				<td><?php  echo $i;  // echo $result->stat_date; ?></td>
				<?php foreach ($types as $key => $value) { ?>
				<td><?php 
				echo $result->$key;
				echo '<br />';
				echo weixin_get_mp_article_stats_round($result, $key);
				?></td>
				<?php } ?>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
}

function weixin_get_mp_article_stats_round($result, $key){
	if($key == 'int_page_read_count' || $key == 'int_page_read_user'){
		return round($result->$key/$result->target_user*100,2).'%';
	}elseif ($key == 'share_count' || $key == 'ori_page_read_count' || $key == 'add_to_fav_count') {
		if($result->int_page_read_count){
			return round($result->$key/$result->int_page_read_count*100,2).'%';
		}else{
			return '0%';
		}
	}elseif ($key == 'share_user' || $key == 'ori_page_read_user' || $key == 'add_to_fav_user') {
		if($result->int_page_read_user){
			return round($result->$key/$result->int_page_read_user*100,2).'%';
		}else{
			return '0%';
		}
	}
}

function weixin_mp_article_single_stats_page(){
	global $wpdb, $current_admin_url, $wpjam_stats_labels;
	extract($wpjam_stats_labels);

	$appid	= weixin_get_appid();

	$table = WEIXIN_ArticleStats::get_table();

	$results	= $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE appid = '{$appid}' AND msgid = %s AND sort != 0 ORDER BY day ASC", $_GET['msgid']));

	$days		= count($results);
	$lastest_result	= $results[$days-1];
	
	if(!$results){
		return;
	}

	echo '<h2>'.$results[0]->title.'</h2>';
	echo '<p>
	群发时间：<strong>'.$results[0]->ref_date.'</strong><br />
	群发用户：<strong>'.$results[0]->target_user.'</strong><br />
	群发位置：<strong>第'.$results[0]->sort.'条</strong>
	</p>';

	echo '
	<h2 class="nav-tab-wrapper nav-tab-small">
		<a class="nav-tab" href="javascript:;" id="tab-title-daily">阅读趋势</a>
		<a class="nav-tab" href="javascript:;" id="tab-title-read">阅读来源</a>
		<a class="nav-tab" href="javascript:;" id="tab-title-share">转发详情</a>
	</h2>';

	echo '<div id="tab-daily" class="div-tab" style="margin-top:1em;">';

	$types	= WEIXIN_ArticleStats::get_types();
	unset($types['target_user']);
	unset($types['int_page_read_count_rate']);
	unset($types['int_page_read_user_rate']);

	$pre_array = array();

	foreach ($types as $key => $value) {
		$pre_array[$key] = 0;
	}

	$counts_array = array();

	foreach($results as $result){
		foreach ($types as $key => $value) {
			$counts_array[$result->stat_date][$key] = $result->$key - $pre_array[$key];
			$pre_array[$key]	= $result->$key;
		}
	}

	wpjam_line_chart($counts_array, $types, array('chart_id'=>'daily_chart'));

	echo '</div>';

	echo '<div id="tab-read" class="div-tab" style="margin-top:1em;">';

	$types	= WEIXIN_ArticleStats::get_types('read');

	$total = $lastest_result->int_page_read_user;

	$counts_array = array();

	foreach ($types as $key => $value) {
		if(strpos($key, '_count')){
			unset($types[$key]);
		}else{
			$counts_array[]	= array('label'=>$key, 'count'=>$lastest_result->$key);
		}
	}

	wpjam_donut_chart($counts_array, array('total'=>$total, 'labels'=>$types, 'show_link'=>false));


	$types	= WEIXIN_ArticleStats::get_types('read');

	$pre_array = array();

	foreach ($types as $key => $value) {
		$pre_array[$key] = 0;
	}

	$counts_array = array();

	foreach($results as $result){
		foreach ($types as $key => $value) {
			$counts_array[$result->stat_date][$key] = $result->$key - $pre_array[$key];
			$pre_array[$key]	= $result->$key;
		}
	}

	wpjam_line_chart($counts_array, $types, array('chart_id'=>'read_chart'));

	echo '</div>';

	echo '<div id="tab-share" class="div-tab" style="margin-top:1em;">';

	$types	= WEIXIN_ArticleStats::get_types('share');

	$total = $lastest_result->share_user;

	$counts_array = array();

	foreach ($types as $key => $value) {
		if(strpos($key, '_cnt')){
			unset($types[$key]);
		}else{
			$counts_array[]	= array('label'=>$key, 'count'=>$lastest_result->$key);
		}
	}

	wpjam_donut_chart($counts_array, array('total'=>$total, 'labels'=>$types, 'show_link'=>false));

	$types	= WEIXIN_ArticleStats::get_types('share');

	$pre_array = array();

	foreach ($types as $key => $value) {
		$pre_array[$key] = 0;
	}

	$counts_array = array();

	foreach($results as $result){
		foreach ($types as $key => $value) {
			$counts_array[$result->stat_date][$key] = $result->$key - $pre_array[$key];
			$pre_array[$key]	= $result->$key;
		}
	}

	wpjam_line_chart($counts_array, $types, array('chart_id'=>'share_chart'));

	echo '</div>';
}

function weixin_mp_articles_hot_page(){
	global $wpdb, $wpjam_stats_labels, $current_admin_url;
	extract($wpjam_stats_labels);

	echo '<h3>最热图文</h3>';	

	add_action('wpjam_stats_header',	'wpjam_stats_header_mp_stats_add_more_fields');
	wpjam_stats_header();

	$table = WEIXIN_ArticleStats::get_table();

	$today		= date('Y-m-d',current_time('timestamp'));
	$searchterm = isset($_POST['s'])?$_POST['s']:'';
	$day 		= isset($_POST['day'])?$_POST['day']:1;
	$type		= isset($_POST['type'])?$_POST['type']:'int_page_read_count';
	$appid		= weixin_get_appid();

	$where		= " appid = '{$appid}' AND ref_date >= '{$wpjam_start_date}' AND ref_date <= '{$wpjam_end_date}'";
	$where		.= ($searchterm)?" AND title like '%{$searchterm}%'":'';

	if($type == 'int_page_read_count_rate' || $type == 'int_page_read_user_rate'){
		$type		= str_replace('_rate', '', $type);	
		$results	= $wpdb->get_results("SELECT *,ROUND({$type}/target_user * 100,2) as rate FROM {$table} WHERE {$where} AND day = {$day} ORDER BY rate DESC LIMIT 0,100");
	}else{
		$results	= $wpdb->get_results("SELECT * FROM {$table} WHERE {$where} AND day = {$day} ORDER BY {$type} DESC LIMIT 0,100");
	}

	$types			= WEIXIN_ArticleStats::get_types();
	unset($types['int_page_read_count_rate']);
	unset($types['int_page_read_user_rate']);
	?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>No.</th>
				<th style="width:120px;">标题</th>
				<th>时间</th>
				<?php foreach ($types as $key => $value) { ?>
				<th><?php echo $value;?></th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>No.</th>
				<th>标题</th>
				<th>时间</th>
				<?php foreach ($types as $key => $value) { ?>
				<th><?php echo $value;?></th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
		<?php $i=0; foreach ($results as $result) { $i++;?>
			<tr  class="<?php $alternate	= empty($alternate)?'alternate':''; echo $alternate;?>">
				<td><?php echo $i;?></td>
				<td><a href="<?php echo admin_url('admin.php?page=weixin-mp-article-stats&tab=daily').'&msgid='.$result->msgid; ?>"><?php echo $result->title; ?></a></td>
				<td>
				<?php echo $result->ref_date;?>
				<br />
				第<?php echo $result->sort;?>条
				</td>
				<?php foreach ($types as $key => $value) { ?>
				<td><?php 
				echo $result->$key;
				echo '<br />';
				echo weixin_get_mp_article_stats_round($result, $key);
				?></td>
				<?php } ?>
			</tr>
		<?php }?>
		</tbody>
	</table>
	<?php
}

function weixin_mp_article_sub_stats_page($type=''){
	global $wpdb, $wpjam_stats_labels, $current_admin_url;
	extract($wpjam_stats_labels);

	echo '<h3>图文群发统计分析</h3>';	

	wpjam_stats_header();

	$today	= date('Y-m-d',current_time('timestamp'));
	$table	= WEIXIN_ArticleStats::get_table();
	$types	= WEIXIN_ArticleStats::get_types();
	unset($types['target_user']);

	$type	= ($type)?$type:((isset($_GET['type']))?$_GET['type']:'int_page_read_count');
	$day 	= isset($_GET['day'])?$_GET['day']:1;
	$sort	= isset($_GET['sort'])?$_GET['sort']:1;
	$appid	= weixin_get_appid();

	if($type != 'target_user'){
		echo '<p>';
		$i = 0;
		while($i < 7){
			$i++;
			$class = ($i == $day)? 'button button-primary' : 'button button-secondary';
			echo '<a class="'.$class.'" href="'.$current_admin_url.'&day='.$i.'&sort='.$sort.'&type='.$type.'">第'.$i.'天</a> ';
		}
		echo '</p>';

		echo '<p>';
		$i = 0;
		while($i < 8){
			$i++;
			$class = ($i == $sort)? 'button button-primary' : 'button button-secondary';
			echo '<a class="'.$class.'" href="'.$current_admin_url.'&sort='.$i.'&day='.$day.'&type='.$type.'">第'.$i.'条</a> ';
		}
		
		if($type != 'all' && $type != 'target_user'){
			$class = ('total' == $sort)? 'button button-primary' : 'button button-secondary';
			echo '<a class="'.$class.'" href="'.$current_admin_url.'&sort=total&day='.$day.'&type='.$type.'">累加</a> ';

			$class = ('avg' == $sort)? 'button button-primary' : 'button button-secondary';
			echo '<a class="'.$class.'" href="'.$current_admin_url.'&sort=avg&day='.$day.'&type='.$type.'">平均</a> ';

			$class = ('all' == $sort)? 'button button-primary' : 'button button-secondary';
			echo '<a class="'.$class.'" href="'.$current_admin_url.'&sort=all&day='.$day.'&type='.$type.'">所有</a> ';	
		}
		
		echo '</p>';

		echo '<p>';
		
		foreach ($types as $key => $value) {
			$class = ($key == $type)? 'button button-primary' : 'button button-secondary';
			echo '<a class="'.$class.'" href="'.$current_admin_url.'&type='.$key.'&day='.$day.'&sort='.$sort.'">'.$value.'</a> ';
		}

		if($sort != 'all' && $sort !='total'){
			$class = ('all' == $type)? 'button button-primary' : 'button button-secondary';
			echo '<a class="'.$class.'" href="'.$current_admin_url.'&type=all&day='.$day.'&sort='.$sort.'">所有</a> ';
		}

		echo '</p>';
	}
	

	$where	= " appid = '{$appid}' AND ref_date >= '{$wpjam_start_date}' AND ref_date <= '{$wpjam_end_date}'";

	if($type == 'all'){

	}elseif($type == 'int_page_read_count_rate'){
		if($sort == 'total'){
			$count_field = 'ROUND(SUM(int_page_read_count)/target_user * 100,2)';
		}elseif($sort == 'avg') {
			$count_field = 'ROUND(AVG(int_page_read_count)/target_user * 100,2)';
		}else{
			$count_field = 'ROUND(int_page_read_count/target_user * 100,2)';
		}
	}elseif($type == 'int_page_read_user_rate'){
		if($sort == 'total'){
			$count_field = 'ROUND(SUM(int_page_read_user)/target_user * 100,2)';
		}elseif($sort == 'avg') {
			$count_field = 'ROUND(AVG(int_page_read_user)/target_user * 100,2)';
		}else{
			$count_field = 'ROUND(int_page_read_user/target_user * 100,2)';
		}
	}elseif($type){
		if($sort == 'total'){
			$count_field = 'SUM('.$type.')';
		}elseif($sort == 'avg') {
			$count_field = 'AVG('.$type.')';
		}else{
			$count_field = $type;
		}
	}

	if($sort == 'all'){
		$sql = $wpdb->prepare("SELECT ref_date, sort, $count_field as count FROM {$table} WHERE {$where} AND day = %d ORDER BY ref_date DESC", $day);
	}elseif($sort == 'total' || $sort == 'avg'){
		$sql = $wpdb->prepare("SELECT ref_date, '$sort' as sort, $count_field as count FROM {$table} WHERE {$where} AND day = %d GROUP BY ref_date ORDER BY ref_date DESC", $day);
	}elseif($type == 'all'){
		unset($types['int_page_read_count_rate']);
		unset($types['int_page_read_user_rate']);
		$type_keys	= array_keys($types);
		$type_keys	= implode(',', $type_keys);
		$sql = $wpdb->prepare("SELECT ref_date, {$type_keys} FROM {$table} WHERE {$where} AND day = %d AND sort=%d ORDER BY ref_date DESC", $day, $sort);
	}else{
		$sql = $wpdb->prepare("SELECT ref_date, sort, $count_field as count FROM {$table} WHERE {$where} AND day = %d AND sort=%d ORDER BY ref_date DESC", $day, $sort);
	}

	if($type == 'all'){
		$new_counts_array	= $wpdb->get_results($sql, OBJECT_K);
	}else{
		$counts_array	= $wpdb->get_results($sql);
		$new_counts_array = array();
		foreach($counts_array as $count){
			if($type == 'int_page_read_count_rate' || $type == 'int_page_read_user_rate'){
				$new_counts_array[$count->ref_date][$count->sort] = $count->count.'%';
			}else{
				$new_counts_array[$count->ref_date][$count->sort] = $count->count;
			}
		}

		$types	= array();
		
		if($type == 'target_user'){
			$types[$sort]	= '群发用户';
		}else{
			if($sort == 'all'){
				$i		= 0;
				while($i < 8){
					$i++;
					$types[$i]	= '第'.$i.'条';
				}
			}elseif($sort == 'total'){
				$types['total']	= '累加';
			}elseif($sort == 'avg'){
				$types['avg']	= '平均';
			}else{
				$types[$sort]	= '第'.$sort.'条';
			}
		}	
	}
	
	wpjam_line_chart($new_counts_array, $types);
}

function weixin_mp_article_subscribe_stats_page(){
	weixin_mp_article_sub_stats_page('target_user');
}

function wpjam_stats_header_mp_stats_add_more_fields(){

	$types	= WEIXIN_ArticleStats::get_types();
	unset($types['target_user']);
	// unset($types['int_page_read_count_rate']);
	// unset($types['int_page_read_user_rate']);

	$searchterm		= isset($_POST['s'])?$_POST['s']:'';
	$current_day	= isset($_POST['day'])?$_POST['day']:1;
	$current_type	= (isset($_POST['type']) && isset($types[$_POST['type']]))?$_POST['type']:'int_page_read_count';

	echo '<p class="search-box">
	<input type="search" name="s" value="'.$searchterm.'" />
	<input type="submit" id="search-submit" class="button" value="搜索">
	</p>';

	echo '<p>数据：';
	$i=0;
	while($i < 7){
		$i++;
		$checked	= ($i == $current_day)? 'checked="checked"' : '';
		$class		= ($i == $current_day)? 'button button-primary' : 'button button-secondary';
		// echo '<span class="'.$class.'"><input type="radio" name="day" value="'.$i.'" id="day-'.$i.'" '.$checked.' style="display:none;" /><label for="day-'.$i.'">第'.$i.'天</label></span> ';
		echo '<input type="radio" name="day" value="'.$i.'" class="day-radio" id="day-'.$i.'" '.$checked.' /><label class="'.$class.'" for="day-'.$i.'">第'.$i.'天</label> ';
	}
	echo '</p>';

	echo '<p>排序：';
	foreach ($types as $key => $value) {
		$checked	= ($key == $current_type)? 'checked="checked"' : '';
		$class		= ($key == $current_type)? 'button button-primary' : 'button button-secondary';
		// echo '<span class="'.$class.'"><input type="radio" name="type" value="'.$key.'" id="type-'.$key.'" '.$checked.' style="display:none;"  /><label for="type-'.$key.'">'.$value.'<label></span> ';
		echo '<input type="radio" name="type" value="'.$key.'" class="type-radio" id="type-'.$key.'" '.$checked.'  /><label class="'.$class.'" for="type-'.$key.'">'.$value.'</label> ';
	}
	echo '</p>';

	?>
	<style type="text/css">
	input.day-radio,input.type-radio,input[type='submit']{display: none;}

	</style>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('change', 'input[name="type"]', function(){
			$("input[name='type']").next("label").removeClass('button-primary');
			$("input[name='type']:checked").next("label").addClass('button-primary');
			$('form').submit();
		});

		$('body').on('change', 'input[name="day"]', function(){
			$("input[name='day']").next("label").removeClass('button-primary');
			$("input[name='day']:checked").next("label").addClass('button-primary');
			$('form').submit();
		});
	});
	</script>
	<?php
}