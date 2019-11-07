<?php
$date		= isset($_GET['date'])?$_GET['date']:date("Y-m-d", current_time("timestamp")-DAY_IN_SECONDS);
$sort		= isset($_GET['sort'])?$_GET['sort']:0;
$blog_id	= get_current_blog_id();

if($sort){
	$results	=	$wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->weixin_mp_articles WHERE blog_id = {$blog_id} AND ref_date = %s AND sort = %d ORDER BY sort ASC, day ASC", $date, $sort));
}else{
	$results	=	$wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->weixin_mp_articles WHERE blog_id = {$blog_id} AND ref_date = %s AND sort != 0 ORDER BY sort ASC, day ASC", $date));
}

$response	= array();

if($results){
	$response['ref_date']		= $results[0]->ref_date;
	$response['target_user']	= (int)$results[0]->target_user;

	foreach ($results as $result) {
		$sort 	= 'sort_'.$result->sort;
		$day	= 'day_'.$result->day;

		$response[$sort]['msgid'] = $result->msgid;
		$response[$sort]['title'] = $result->title;
		$response[$sort][$day]['stat_date']				= $result->stat_date;
		$response[$sort][$day]['int_page_read_user']	= (int)$result->int_page_read_user;
		$response[$sort][$day]['int_page_read_count']	= (int)$result->int_page_read_count;
		$response[$sort][$day]['ori_page_read_user']	= (int)$result->ori_page_read_user;
		$response[$sort][$day]['ori_page_read_count']	= (int)$result->ori_page_read_count;
		$response[$sort][$day]['share_user']			= (int)$result->share_user;
		$response[$sort][$day]['share_count']			= (int)$result->share_count;
		$response[$sort][$day]['add_to_fav_user']		= (int)$result->add_to_fav_user;
		$response[$sort][$day]['add_to_fav_count']		= (int)$result->add_to_fav_count;
	}
}

wpjam_send_json($response);


