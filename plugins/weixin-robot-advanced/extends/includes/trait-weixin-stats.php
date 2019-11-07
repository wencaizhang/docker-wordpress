<?php
trait WEIXIN_Stats{
	public static function get_dates($datespan, $appid, $first_date='', $last_date=''){
		if(empty($appid)) {
			return new WP_Error('empty_appid', 'APPID 为空');
		}
		
		$today	= date('Y-m-d',current_time('timestamp'));

		if(current_time('timestamp') < strtotime($today.' 09:00:00')){
			$yesterday	= date('Y-m-d', current_time('timestamp')-2*DAY_IN_SECONDS);	
		}else{
			$yesterday	= date('Y-m-d', current_time('timestamp')-DAY_IN_SECONDS);
		}

		$first_date	= ($first_date)?:self::Query()->where('appid', $appid)->order_by('ref_date')->order('ASC')->get_var('ref_date');
		$last_date	= ($last_date)?:self::Query()->where('appid', $appid)->order_by('ref_date')->order('DESC')->get_var('ref_date');
		
		if(empty($last_date)){
			$end_date	= $yesterday;
		}elseif($last_date < $yesterday){
			$end_date	= date('Y-m-d', strtotime($last_date)+$datespan*DAY_IN_SECONDS);
			$end_date	= ($end_date > $yesterday)?$yesterday:$end_date;
		}else{
			if($first_date <= '2014-12-01') {
				return new WP_Error('no_more_history_stats_data', '没有历史统计数据了');
			}

			$end_date	= date('Y-m-d', strtotime($first_date)-DAY_IN_SECONDS);
		}

		$begin_date	= date('Y-m-d', strtotime($end_date)-($datespan-1)*DAY_IN_SECONDS);
		$begin_date	= ($begin_date	< '2014-12-01')?'2014-12-01':$begin_date;

		return compact('begin_date', 'end_date');
	}

	public static function save_data($begin_date, $end_date, $appid, $response){
		if(is_wp_error($response)){
			return $response;
		}

		$stats_data	= array();
		$empty_data	= array();

		$date	= $begin_date;
		while ($date <= $end_date){
			$empty_data[$date] = array(
				'appid'			=> $appid,
				'ref_date'		=> $date,
			);

			$date	= date('Y-m-d', strtotime($date)+DAY_IN_SECONDS);
		}

		if($response['list']){
			foreach ($response['list'] as $data) {
				$data['appid']	= $appid;
				$stats_data[]	= $data;

				$ref_date	= $data['ref_date'];
				unset($empty_data[$ref_date]);
			}
		}

		if($empty_data){
			$result	= self::insert_multi($empty_data);
		}

		if($stats_data){
			$result	= self::insert_multi($stats_data);
		}

		return $result;
	}
}