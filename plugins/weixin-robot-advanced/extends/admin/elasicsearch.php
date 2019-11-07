<?php

// add_filter('weixin_setting','weixin_es_settings',1);
// function weixin_es_settings($sections){

//     $sections['site']['fields']['weixin_es'] = array('title'=>'使用ES来显示统计数据',   'type'=>'checkbox', 'description'=>'使用elasticsearch来显示统计数据');

//     return $sections;
// }



add_filter('wpjam_weixin_messages_tabs', function($tabs){
    
    foreach ($tabs as $key => $tab) {
        $tabs[$key]['function'] = str_replace('_page', '_es_page', $tabs[$key]['function']);
    }

    return $tabs;
}, 11);


add_filter('wpjam_weixin_menu_tabs', function($tabs){
    $tabs['menu']['function']   = 'weixin_message_stats_es_page';
    
    return $tabs;
}, 11);


add_filter('wpjam_weixin_dashboard_widgets', function ($dashboard_widgets){
    $dashboard_widgets['weixin-overview']['callback']    = 'weixin_overview_es_dashboard_widget_callback';
    $dashboard_widgets['weixin-keyword']['callback']     = 'weixin_keyword_es_dashboard_widget_callback';

    return $dashboard_widgets;
}, 999);

// add_filter('weixin_message_overview', 'weixin_message_overview_es');
// function weixin_message_overview_es($status){


// 		$blog_id	= get_current_blog_id();
// 		weixin_message_overview_es_page($blog_id);
// 		return true;
// 	return $status;
// }

// add_filter('weixin_message_stats', 'weixin_message_stats_es');
// function weixin_message_stats_es($status){
// 		$blog_id	= get_current_blog_id();
// 		weixin_message_stats_es_page($blog_id);
// 		return true;

// 	return $status;
// }



/*分割线*/
function weixin_message_overview_es_page()
{
	global $current_admin_url, $current_tab;

    $blogId    = get_current_blog_id();

  	list($from, $to, $date_type, $type) = weixin_stats_es_get_query_parameters();
    $params = ['blog_id' => $blogId, 'from' => $from, 'to' => $to, 'interval' => $date_type, 'tab' => $current_tab];
    if ($type) {
        $params['type'] = $type;
    }

    list($stats_result, $flag) = weixin_stats_es_fetch('/message', $params);
    if ($flag == false) {
    	echo $stats_result;
    	return;
    }

	echo '<h2>消息统计分析预览</h2>';
	wpjam_stats_header(array('show_date_type'=>true,'show_compare'=>true));
	weixin_messages_stats_es_display_line_chart($stats_result, 'stats', $type);
}

function weixin_message_stats_es_page() 
{
	global $current_admin_url, $plugin_page, $current_tab, $wpjam_stats_labels;
	extract($wpjam_stats_labels);
    $blogId    = get_current_blog_id();

	list($from, $to, $date_type, $type, $compare, $from2, $to2) = weixin_stats_es_get_query_parameters();
    $params = ['blog_id' => $blogId, 'from' => $from, 'to' => $to, 'interval' => $date_type, 'tab' => $current_tab];
    if ($type) {
        $params['type'] = $type;
    }

    list($stats_result, $flag) = weixin_stats_es_fetch($current_tab == 'menu' ? '/menu' : '/message', $params);
    if ($flag == false) {
    	echo $stats_result;
    	return;
    }

    $compare_stats_result = null;
    if ($type && $compare) {
    	$params = ['blog_id' => $blogId, 'from' => $from2, 'to' => $to2, 'interval' => $date_type, 'tab' => $current_tab, 'type' => $type];
        list($compare_stats_result, $flag) = weixin_stats_es_fetch($current_tab == 'menu' ? '/menu' : '/message', $params);
	    if ($flag == false) {
	    	echo $compare_stats_result;
	    	return;
	    }
    }

    wpjam_stats_header(array('show_date_type'=>true,'show_compare'=>true));
    weixin_messages_stats_es_display_donut_chart($stats_result, $current_tab, $compare_stats_result, $compare_label, $compare_label_2);
	weixin_messages_stats_es_display_line_chart($stats_result, $current_tab, $type, $compare_stats_result, $compare_label, $compare_label_2);
}

function weixin_message_summary_es_page()
{
	echo '<h2>文本回复类型统计分析</h2>';
	wpjam_stats_header();

	global $current_admin_url, $current_tab;
	$response_types = WEIXIN_AdminMessage::get_response_types();

    $blogId    = get_current_blog_id();

	list($from, $to, $date_type, $type) = weixin_stats_es_get_query_parameters();
	$params = ['blog_id' => $blogId, 'from' => $from, 'to' => $to, 'interval' => $date_type, 'tab' => $current_tab];
    if ($type) {
        $params['type'] = $type;
    }
    
    list($stats_result, $flag) = weixin_stats_es_fetch('/message', $params);
    if ($flag == false) {
    	echo $stats_result;
    	return;
    }
	
    weixin_messages_stats_es_display_donut_chart($stats_result, $current_tab);

	?>
        <table class="widefat" cellspacing="0">
            <thead>
            <tr>
                <th style="width:42px">排名</th>
                <th style="width:42px">数量</th>
                <th>关键词</th>
                <th style="width:91px">回复类型</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stats_result['histogram'] as $key => $item): ?>
                <tr class="<?php echo ((intval($key) % 2 == 0) ? 'alternate' : ''); ?>">
                    <td><?php echo ($key + 1); ?></td>
                    <td><?php echo $item['count']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo isset($response_types[$item['response']]) ? $response_types[$item['response']] : $item['response']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
}

add_filter('weixin-menu-tree-stats', 'weixin_menu_tree_es_stats');
function weixin_menu_tree_es_stats($menu_stats)
{

        $blog_id    = get_current_blog_id();
        list($from, $to, $date_type) = weixin_stats_es_get_query_parameters();

        $params = ['blog_id' => $blog_id, 'from' => $from, 'to' => $to, 'interval' => $date_type];
        list($stats_result, $flag) = weixin_stats_es_fetch('/menu/summary', $params);
        if ($flag == false) {
            echo $stats_result;
            return;
        }

        $total = $stats_result['total'];
        $data = $stats_result['data'];

        $counts = wp_list_pluck($data, 'count', 'EventKey');

        // foreach ($data as $item) {
        //     $counts[$item['EventKey']] = $item['count'];
        // }

        return compact('total','counts');
}


function weixin_overview_es_dashboard_widget_callback($blog_id)
{
    $blog_id    = get_current_blog_id();
    list($stats_result, $flag) = weixin_dashboard_widget_es_fetch(['blog_id' => $blog_id]);
    if ($flag == false) {
        echo $stats_result;
        return;
    }

    $today_counts   = isset($stats_result['user']['today'])?$stats_result['user']['today']:0;
    $yesterday_counts = isset($stats_result['user']['yesterday'])?$stats_result['user']['yesterday']:0;
    $expect_counts = isset($stats_result['user']['expect'])?$stats_result['user']['expect']:0;
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
                <td><?php echo round($today_counts['unsubscribe_ratio'] * 100, 2) . '%';?></td>
                <td><?php echo $today_counts['net_increase'];?></td>
            </tr>
            <tr class="">
                <td>昨日</td>
                <td><?php echo $yesterday_counts['subscribe'];?></td>
                <td><?php echo $yesterday_counts['unsubscribe'];?></td>
                <td><?php echo round($yesterday_counts['unsubscribe_ratio'] * 100, 2) . '%';?></td>
                <td><?php echo $yesterday_counts['net_increase'];?></td>
            </tr>
            <tr class="alternate" style="font-weight:bold;">
                <td>预计今日</td>
                <td><?php echo weixin_get_expected_count($expect_counts['subscribe'], $yesterday_counts['subscribe']); ?></td>

                <td><?php echo weixin_get_expected_count($expect_counts['unsubscribe'], $yesterday_counts['unsubscribe'], '', false); ?></td>

                <td><?php echo weixin_get_expected_count(
                        round($expect_counts['unsubscribe_ratio'] * 100, 2) . '%', 
                        round($yesterday_counts['unsubscribe_ratio'] * 100, 2) . '%','',false); ?></td>

                <td><?php echo weixin_get_expected_count($expect_counts['net_increase'], $yesterday_counts['net_increase']); ?></td>
            </tr>
        </tbody>
    </table>

    <p><a href="<?php echo admin_url('admin.php?page=weixin-users&tab=subscribe');?>">详细用户订阅数据...</a></p>
    <hr />

    <?php 

    $today_counts = isset($stats_result['message']['today'])?$stats_result['message']['today']:0;
    $yesterday_counts = isset($stats_result['message']['yesterday'])?$stats_result['message']['yesterday']:0;;
    $expect_counts = isset($stats_result['message']['expect'])?$stats_result['message']['expect']:0;;
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
                <td><?php echo $today_counts['message_count']; ?>
                <td><?php echo $today_counts['user_count']; ?>
                <td><?php echo round($today_counts['average'], 2); ?>
            </tr>
            <tr class="">
                <td>昨日</td>
                <td><?php echo $yesterday_counts['message_count']; ?>
                <td><?php echo $yesterday_counts['user_count']; ?>
                <td><?php echo round($yesterday_counts['average'], 2); ?>
            </tr>
            <tr class="alternate" style="font-weight:bold;">
                <td>预计今日</td>
                <td><?php echo weixin_get_expected_count($expect_counts['message_count'], $yesterday_counts['message_count']); ?>
                <td><?php echo weixin_get_expected_count($expect_counts['user_count'], $yesterday_counts['user_count']); ?>
                <td><?php echo weixin_get_expected_count($expect_counts['average'], $yesterday_counts['average']); ?>
            </tr>
        </tbody>
    </table>

    <p><a href="<?php echo admin_url('admin.php?page=weixin-messages&tab=stats');?>">详细消息统计...</a></p>
    <?php
}

function weixin_keyword_es_dashboard_widget_callback($blog_id)
{
    $blog_id    = get_current_blog_id();
    list($stats_result, $flag) = weixin_dashboard_widget_es_fetch(['blog_id' => $blog_id]);
    if ($flag == false) {
        echo $stats_result;
        return;
    }

    $response_types = WEIXIN_AdminMessage::get_response_types();
    $i= 0;
    if(!empty($stats_result['hot_keys'])){ ?>
    <table class="widefat" cellspacing="0">
        <tbody>
        <?php foreach ($stats_result['hot_keys'] as $weixin_message) { $alternate = empty($alternate)?'alternate':''; $i++; ?>
            <tr class="<?php echo $alternate; ?>">
                <td style="width:18px;"><?php echo $i; ?></td>
                <td><?php echo wp_strip_all_tags($weixin_message['content']); ?></td>
                <td style="width:32px;"><?php echo $weixin_message['count']; ?></td>
                <td style="width:98px;"><?php echo isset($response_types[$weixin_message['response']])?$response_types[$weixin_message['response']]:$weixin_message['response']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <p><a href="<?php echo admin_url('admin.php?page=weixin-messages&tab=summary');?>">更多热门关键字...</a></p>
    <?php
    }
}

function weixin_dashboard_widget_es_fetch($params)
{
    list($stats_result, $flag) = weixin_stats_es_fetch('/dashboard', $params);
    return array($stats_result, $flag);
}

function weixin_messages_stats_es_display_donut_chart($stats_result, $tab, $compare_result = null, $compare_label_1 = null, $compare_label_2 = null)
{
	if ($tab == 'summary') {
		$message_types	= WEIXIN_AdminMessage::get_response_types();
	}
	else {
		$message_types	= WEIXIN_AdminMessage::get_message_types($tab);
	}
    
	if ($compare_result) {
		$data1 = $stats_result['overview'];
		$data2 = $compare_result['overview'];
		$item1['label'] = $compare_label_1;
		$item1['count'] = $data1[0]['count'];

		$item2['label'] = $compare_label_2;
		$item2['count'] = isset($data2[0]['count']) ? $data2[0]['count'] : 0;
		$display_data = [$item1, $item2];
		$show_Link = false;
	}
	else {
		$display_data = $stats_result['overview'];
        if($tab != 'menu'){
            foreach ($display_data as $key => $item) {
                $item['label'] = strtolower($item['label']);
                $display_data[$key] = $item;
            }
        }
		$show_Link = true;
	}

    $total = $stats_result['total'];
    wpjam_donut_chart($display_data, ['total' => $total, 'labels'=>$message_types, 'show_link' => $show_Link, 'chart_width' => 280]);
}

add_filter('weixin_user_subscribe_stats_data', 'weixin_user_subscribe_stats_es_data');
function weixin_user_subscribe_stats_es_data()
{

    $blog_id	= get_current_blog_id();
    list($from, $to, $date_type) = weixin_stats_es_get_query_parameters();
    $params = ['blog_id' => $blog_id, 'from' => $from, 'to' => $to, 'interval' => $date_type];
    list($stats_result, $flag) = weixin_stats_es_fetch('/subscriber', $params);
    if ($flag == false) {
        return $stats_result;
    }

    $subscribe_count = $stats_result['overview']['subscribe'];
    $unsubscribe_count = $stats_result['overview']['unsubscribe'];
    $netuser = $stats_result['overview']['netuser'];
    $unsubscribe_rate = $stats_result['overview']['unsubscribe_ratio'];

    $counts_array = array();
    $histogram = $stats_result['histogram'];
    foreach ($histogram as $key => $item) {
        $counts_array[$item['date']] = $item;
    }

    return array(
        'subscribe_count' => $subscribe_count,
        'unsubscribe_count' => $unsubscribe_count,
        'netuser' => $netuser,
        'unsubscribe_rate' => $unsubscribe_rate,
        'counts_array' => $counts_array
    );
}

function weixin_user_vendor_stats_es_page($blog_id)
{
    
    list($from, $to, $date_type, $type, $compare, $from2, $to2) = weixin_stats_es_get_query_parameters();
    $params = ['blog_id' => $blog_id, 'from' => $from, 'to' => $to, 'interval' => $date_type, 'vendor' => true];
    if (!is_null($type)) {
        $params['type'] = $type;
    }

    list($stats_result, $flag) = weixin_stats_es_fetch('/subscriber', $params);
    if ($flag == false) {
        echo $stats_result;
        return;
    }

    $overview = $stats_result['overview'];
    $total = $stats_result['total'];
    wpjam_donut_chart($overview, ['total' => $total, 'labels'=>[''=>'直接订阅'], 'show_link' => true, 'chart_width' => 280]);

    $histogram = $stats_result['histogram'];
    $histogram_map = [];
    foreach ($histogram as $key => $item) {
        if (!is_null($type) && (!isset($item['event_key']) || $item['event_key'] <= 0)) {
            continue;
        }
        $histogram_map[$item['date']] = $item;
    }

    if (!is_null($type)) {
        $labels = ['event_key' => ''];
    }
    else {
        $labels = ['count' => '所有'];
    }
    
    $args = ['day_key' => 'date', 'day_labels' => array_keys($histogram_map)];
    wpjam_line_chart($histogram_map, $labels, $args);
}

function weixin_messages_stats_es_display_line_chart($stats_result, $tab, $type = null, $compare_result = null, $compare_label_1 = null, $compare_label_2 = null)
{
	$message_types = WEIXIN_AdminMessage::get_message_types($tab);    
    $histogram_map = [];
    
    if ($compare_result) {
    	$histogram1 = $stats_result['histogram'];
    	$histogram2 = $compare_result['histogram'];
    	for ($i=0; $i < count($histogram1); $i++) { 
    		$item1 = $histogram1[$i];
    		$item2 = $histogram2[$i];
    		$new_item['data1'] = $item1[$type];
    		$new_item['data2'] = $item2[$type];
    		$histogram_map[$item1['date']] = $new_item;
    	}
    	$labels = array('data1'	=> $compare_label_1,'data2'	=> $compare_label_2);
    }
    else {

		if (!$type) {
			$labels = ['count' => '所有#'];
		}
    	$histogram = $stats_result['histogram'];
    	foreach ($histogram as $item) {
	    	foreach ($item as $key => $value) {
	    		if ($key == 'date' || $key == 'count') {
	    			continue;
	    		}
                if($tab != 'menu'){
	    		    $lowerKey = strtolower($key);
                }else{
                    $lowerKey = $key;
                }
	    		$labels[$lowerKey] = isset($message_types[$lowerKey]) ? $message_types[$lowerKey] : $lowerKey;

	    		$item[$lowerKey] = $value;
	    		if ($lowerKey != $key) {
	    			unset($item[$key]);
	    		}
	    		
	    	}
	        $histogram_map[$item['date']] = $item;
	    }
	    if ($tab == 'stats') {
	    	$labels = ['message_count' => '消息发送次数', 'user_count' => '消息发送人数', 'ratio' => '人均发送次数#'];
	    }
    }

    wpjam_line_chart($histogram_map, $labels, ['day_key' => 'date', 'day_labels' => array_keys($histogram_map)]);
}


function weixin_stats_es_get_query_parameters()
{
    global $wpjam_stats_labels;
    extract($wpjam_stats_labels);

    $date_type_map = weixin_get_date_type_list();
    $date_type = $date_type_map[$wpjam_current_date_type];

    if ($date_type == 'minute') {
        $start = $wpjam_end_timestamp - DAY_IN_SECONDS;
        if ($wpjam_start_timestamp < $start) {
            $wpjam_start_timestamp = $start;
        }

        $start = $wpjam_end_timestamp_2 - DAY_IN_SECONDS;
        if ($wpjam_start_timestamp_2 < $start) {
            $wpjam_start_timestamp_2 = $start;
        }
    }
    else if ($date_type == 'hour') {

        $start = $wpjam_end_timestamp - DAY_IN_SECONDS * 7;
        if ($wpjam_start_timestamp < $start) {
            $wpjam_start_timestamp = $start;
        }

        $start = $wpjam_end_timestamp_2 - DAY_IN_SECONDS * 7;
        if ($wpjam_start_timestamp_2 < $start) {
            $wpjam_start_timestamp_2 = $start;
        }
    }

    $from = $wpjam_start_timestamp;
    $to = $wpjam_end_timestamp;

    $from2 = $wpjam_start_timestamp_2;
    $to2 = $wpjam_end_timestamp_2;

    $type = isset($_GET['type']) ? $_GET['type'] : null;

    if ($type) {	
    	if (in_array($type, ['click', 'view', 'scan', 'location'])) {
    		$type = strtoupper($type);
    	}
    }

    return array($from, $to, $date_type, $type, $wpjam_compare, $from2, $to2);
}

function weixin_stats_es_fetch($api, $params, $tab = null) {
    $api_url = 'http://172.16.3.120:8810/stats' . $api;
    $api_url = add_query_arg($params, $api_url);
    if (isset($_GET['debug'])) {
        wpjam_print_r($api_url);
    }
    
    $result = wp_remote_get($api_url);
    if (is_wp_error($result)) {
        return array($result->get_error_message(), false);
    } 
    else {
        $stats_result = json_decode($result['body'], true);
        if (isset($stats_result['code']) && $stats_result['code'] > 0) {
            return array($stats_result['code'] . ':' . $stats_result['message'], false);;
        }

        if (isset($_GET['debug'])) {
            wpjam_print_r($stats_result);
        }
    
        return array($stats_result, true);
    }
}

function weixin_get_date_type_list()
{
	return [
        '按分钟' => 'minute',
        '按小时' => 'hour',
        '按天' 	=> 'day',
        '按周' 	=> 'week',
        '按月' 	=> 'month',
    ];
}