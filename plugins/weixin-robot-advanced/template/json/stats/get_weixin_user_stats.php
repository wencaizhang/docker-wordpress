<?php
$begin_date	= isset($_GET['begin_date'])?$_GET['begin_date']:date("Y-m-d", current_time("timestamp")-DAY_IN_SECONDS*31);
$end_date	= isset($_GET['end_date'])?$_GET['end_date']:date("Y-m-d", current_time("timestamp")-DAY_IN_SECONDS);
$blog_id	= get_current_blog_id();

$results	= $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->weixin_mp_users WHERE blog_id = %d AND ref_date >= %s AND ref_date <= %s ORDER BY ref_date DESC, user_source ASC", $blog_id, $begin_date, $end_date));

$response	= array();
$list		= array();

if($results){
	
	foreach ($results as $result) {
		unset($result->id);
		unset($result->blog_id);
		foreach ($result as $key => $value) {
			if($key != 'ref_date'){
				$result->$key = (int)$value;
			}	
		}
		$list[]	= $result;
	}
}

$response	= compact('list');

wpjam_send_json($response);


