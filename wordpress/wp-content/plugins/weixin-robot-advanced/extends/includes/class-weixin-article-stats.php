<?php
class WEIXIN_ArticleStats extends WPJAM_Model {
	use WEIXIN_Stats;
	use WEIXIN_Trait;

	public static function sync($appid=''){
		$appid		= ($appid)?:static::get_appid();

		$today	= date('Y-m-d',current_time('timestamp'));

		if(current_time('timestamp') < strtotime($today.' 09:00:00')){
			$yesterday	= date('Y-m-d', current_time('timestamp')-2*DAY_IN_SECONDS);	
		}else{
			$yesterday	= date('Y-m-d', current_time('timestamp')-DAY_IN_SECONDS);
		}

		$last_date	= '';

		$i 			= 7;
		while ($i > 0) {
			$date	= date('Y-m-d', strtotime($yesterday)-($i-1)*DAY_IN_SECONDS);
			if(!self::Query()->where('appid', $appid)->where('ref_date', $date)->where_any(array('day'=>$i,'msgid'=>'0'))->get_var('ref_date')){
				$last_date = date('Y-m-d', strtotime($date)-DAY_IN_SECONDS);
				break;
			}

			$i--;
		}

		$last_date	= ($last_date)?:self::Query()->where('appid', $appid)->order_by('ref_date')->order('DESC')->get_var('ref_date');
		$first_date	= self::Query()->where('appid', $appid)->order_by('ref_date')->order('ASC')->get_var('ref_date');

		$dates		= self::get_dates(1, $appid, $first_date, $last_date);
		if(is_wp_error($dates)){
			return $dates;
		}

		$begin_date	= $dates['begin_date'];
		$end_date	= $dates['end_date'];

		 // trigger_error(var_export($dates, true));

		$response	= weixin()->get_article_total($begin_date, $end_date);
		if(is_wp_error($response)){
			return $response;
		}

		$datas	= array();
		if($response['list']){
			foreach ($response['list'] as $i=>$data_list) {
				$sort			= $i+1;
				$ref_date		= $data_list['ref_date'];
				$title			= $data_list['title'];
				$msgid			= $data_list['msgid'];
				
				$user_source	= $data_list['user_source'];
				$details		= $data_list['details'];

				if($i > 7){
					continue;
				}

				$days	= (strtotime($yesterday) - strtotime($ref_date)) / DAY_IN_SECONDS;
				$days	= $days > 7 ? 7 : $days;

				foreach ($details as $j=>$detail) {
					$detail['day']			= (strtotime($detail['stat_date']) - strtotime($ref_date)) / DAY_IN_SECONDS + 1;
					$detail['sort']			= $sort;
					$detail['ref_date']		= $ref_date;
					$detail['title']		= $title;
					$detail['msgid']		= $msgid;
					$detail['user_source']	= $user_source;
					$detail['appid']		= $appid;

					$datas[$detail['day']]	= $detail;
				}

				if(count($details) < $days && empty($datas[$days])){
					self::insert([
						'day'			=> $days,
						'sort'			=> $sort,
						'ref_date'		=> $ref_date,
						'title'			=> $title,
						'user_source'	=> $user_source,
						'appid'			=> $appid,
						'msgid'			=> $msgid,
					]);
				}

				$datas = array_values($datas);
			}
		}else{
			$datas[] = array('appid'=>$appid, 'msgid'=>0, 'ref_date'=>$begin_date);
		}

		return self::insert_multi($datas);
	}

	public static function get_types($type=''){
		if($type == ''){
			return array(
				'target_user'			=>'群发用户',

				'int_page_read_count'	=>'阅读次数',
				'int_page_read_user'	=>'阅读人数',
				'share_count'			=>'分享次数',
				'share_user'			=>'分享人数',
				'ori_page_read_count'	=>'原文点击次数',
				'ori_page_read_user'	=>'原文点击人数',
				'add_to_fav_count'		=>'收藏次数',
				'add_to_fav_user'		=>'收藏人数',
				
				'int_page_read_count_rate'	=>'阅读次数比率',
				'int_page_read_user_rate'	=>'阅读人数比率',
			);
		}elseif ($type == 'read') {
			return array(
				'int_page_from_session_read_count'	=> '会话阅读次数',
				'int_page_from_session_read_user'	=> '会话阅读人数',
				'int_page_from_hist_msg_read_count'	=> '历史消息页阅读次数',
				'int_page_from_hist_msg_read_user'	=> '历史消息页阅读人数',
				'int_page_from_feed_read_user'		=> '朋友圈阅读人数',
				'int_page_from_feed_read_count'		=> '朋友圈阅读次数',
				'int_page_from_friends_read_count'	=> '好友转发阅读次数',
				'int_page_from_friends_read_user'	=> '好友转发阅读人数',
				'int_page_from_other_read_count'	=> '其他场景阅读次数',
				'int_page_from_other_read_user'		=> '其他场景阅读人数',	
			);
		}elseif ($type=='share') {
			return array(
				'feed_share_from_session_cnt'		=> '会话转发朋友圈次数',
				'feed_share_from_session_user'		=> '会话转发朋友圈人数',
				'feed_share_from_feed_cnt'			=> '朋友圈转发朋友圈次数',
				'feed_share_from_feed_user'			=> '朋友圈转发朋友圈人数',
				'feed_share_from_other_cnt'			=> '其他场景转发朋友圈次数',
				'feed_share_from_other_user'		=> '其他场景转发朋友圈人数',
			);
		}
	}

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_article_stats';
	}

	protected static $handler;
	protected static $appid;

	public static function get_handler(){
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'field_types'		=> ['id'=>'%d'],
				'searchable_fields'	=> [],
				'filterable_fields'	=> [],
			));
		}
		
		return self::$handler;
	}

	public static function create_table(){
		global $wpdb;

		$table = self::get_table();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if($wpdb->get_var("show tables like '".$table."'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS {$table} (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` varchar(32) NOT NULL,
				`ref_date` date NOT NULL,
				`sort` int(1) NOT NULL,
				`msgid` varchar(16) NOT NULL,
				`title` text NOT NULL,
				`day` int(1) NOT NULL,
				`user_source` int(1) NOT NULL,
				`target_user` int(10) NOT NULL,
				`stat_date` date NOT NULL,
				`int_page_read_user` int(10) NOT NULL,
				`int_page_read_count` int(10) NOT NULL,
				`ori_page_read_user` int(10) NOT NULL,
				`ori_page_read_count` int(10) NOT NULL,
				`share_user` int(10) NOT NULL,
				`share_count` int(10) NOT NULL,
				`add_to_fav_user` int(10) NOT NULL,
				`add_to_fav_count` int(10) NOT NULL,
				`int_page_from_session_read_user` int(10) NOT NULL,
				`int_page_from_session_read_count` int(10) NOT NULL,
				`int_page_from_hist_msg_read_user` int(10) NOT NULL,
				`int_page_from_hist_msg_read_count` int(10) NOT NULL,
				`int_page_from_feed_read_user` int(10) NOT NULL,
				`int_page_from_feed_read_count` int(10) NOT NULL,
				`int_page_from_friends_read_user` int(10) NOT NULL,
				`int_page_from_friends_read_count` int(10) NOT NULL,
				`int_page_from_other_read_user` int(10) NOT NULL,
				`int_page_from_other_read_count` int(10) NOT NULL,
				`feed_share_from_session_user` int(10) NOT NULL,
				`feed_share_from_session_cnt` int(10) NOT NULL,
				`feed_share_from_feed_user` int(10) NOT NULL,
				`feed_share_from_feed_cnt` int(10) NOT NULL,
				`feed_share_from_other_user` int(10) NOT NULL,
				`feed_share_from_other_cnt` int(10) NOT NULL,
				PRIMARY KEY(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `appid` (`appid`),
				ADD KEY `ref_date` (`ref_date`),
				ADD KEY `sort` (`sort`),
				ADD KEY `day` (`day`),
				ADD KEY `msgid` (`msgid`);");
			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD UNIQUE( `appid`, `ref_date`, `msgid`, `stat_date`);");
		}
	}
}