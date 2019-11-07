<?php
class WEIXIN_UserStats extends WPJAM_Model {
	use WEIXIN_Stats;
	use WEIXIN_Trait;
	
	public static function sync($appid=''){
		$appid	= ($appid)?:static::get_appid();
		$dates	= self::get_dates(7, $appid);
		if(is_wp_error($dates)){
			return $dates;
		}

		$begin_date	= $dates['begin_date'];
		$end_date	= $dates['end_date'];

		$summary	= weixin()->get_user_summary($begin_date, $end_date);
		$result 	= self::save_data($begin_date, $end_date, $appid, $summary);
		if(is_wp_error($result)){
			return $result;
		}

		$cumulate	= weixin()->get_user_cumulate($begin_date, $end_date);
		if(is_wp_error($cumulate)){
			return $cumulate;
		}

		if($cumulate['list']){
			$cumulate	= array_map(function($stat) use ($appid){
				$stat['appid']	= $appid;
				return $stat;
			}, $cumulate['list']);
			return self::insert_multi($cumulate);
		}else{
			return $result;
		}
	}

	public static $types = array(
		'new_user'		=>'新增用户', 
		'cancel_user'	=>'取消关注', 
		'net_user'		=>'净增长', 
		'cumulate_user'	=>'总用户数'
	);

	public static $sources = array(
		'0'	=>'其他',
		'1'	=>'公众号搜索',
		// '3'	=>'搜索微信号',
		'17'=>'名片分享',
		'30'=>'扫描二维码',
		// '35'=>'搜公众号名称',
		// '39'=>'查询微信公众帐号',
		'43'=>'图文页右上角菜单',
		'51'=>'支付后关注',
		'57'=>'图文页内公众号名称',
		'75'=>'公众号文章广告',
		'78'=>'朋友圈广告',
	);

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_user_stats';
	}

	protected static $handler;
	protected static $appid;

	public static function get_handler(){
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'field_types'		=> ['id'=>'%d','user_source'=>'%d','new_user'=>'%d','cancel_user'=>'%d','cumulate_user'=>'%d'],
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
				`user_source` int(3) NOT NULL,
				`new_user` int(10) NOT NULL,
				`cancel_user` int(10) NOT NULL,
				`cumulate_user` int(10) NOT NULL,
				PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `appid` (`appid`),
				ADD KEY `ref_date` (`ref_date`),
				ADD KEY `user_source` (`user_source`);");

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD UNIQUE( `appid`, `ref_date`, `user_source`);");
		}
	}
}