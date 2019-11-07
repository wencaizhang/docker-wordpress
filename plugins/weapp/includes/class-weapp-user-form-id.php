<?php
Class WEAPP_UserFormId extends WPJAM_Model{
	use WEAPP_Trait;

	private static function get_form_id_from_cache($openid, $appid){
		$list_cache	= self::get_list_cache();
		$items		= $list_cache->get_all();

		$items		= array_filter($items, function($item) use($openid, $appid){
			return ($item['openid'] == $openid && $item['appid'] == $appid);
		});

		if($items){
			if($list_cache->remove(array_keys($items)[0])){
				$item	= current($items);
				return $item['form_id'];
			}
		}

		return false;
	}

	public static function list_cache_to_db(){
		$list_cache	= self::get_list_cache();
		$items		= $list_cache->empty();

		if($items){
			self::insert_multi($items);
		}
	}

	public static function get_form_id($openid){
		$appid		= static::get_appid();

		if($form_id = self::get_form_id_from_cache($openid, $appid)){
			return $form_id;
		}else{
			$data	= self::Query()->where('appid', $appid)->where('openid', $openid)->where_gt('time', time()-DAY_IN_SECONDS*7+600)->get_row();

			if($data){
				self::delete($data['id']);
				return $data['form_id'];
			}

			return false;
		}
	}

	public static function get_form_ids($openids = null, $delete=true){
		self::list_cache_to_db();

		$datas = self::Query()->where('appid', static::get_appid())->where_in('openid', $openids)->where_gt('time', time()-DAY_IN_SECONDS*7+600)->group_by('openid')->order_by('time')->order('ASC')->get_results('id, openid, form_id, time');

		if($datas){
			if($delete){
				self::Query()->delete_multi(array_column($datas, 'id'));
			}
		}else{
			$datas	= [];
		}

		return $datas;
	}

	public static function get_multi_form_ids($appids, $openids){
		self::list_cache_to_db();

		$datas = self::Query()->where_in('appid', $appids)->where_in('openid', $openids)->where_gt('time', time()-DAY_IN_SECONDS*7+600)->group_by('appid','openid')->order_by('time')->order('ASC')->get_results('id, appid, openid, form_id, time');

		if(!$datas){
			return [];
		}

		self::Query()->delete_multi(array_column($datas, 'id'));
	
		return $datas;
	}

	public static function add_form_id($openid, $form_id){
		$list_cache	= self::get_list_cache();
		$list_cache->add([
			'time'		=> time(),
			'appid'		=> static::get_appid(),
			'openid'	=> $openid,
			'form_id'	=> $form_id
		]);
		
		return true;
	}

	public static function add_prepay_id($openid, $prepay_id){
		for ($i=0; $i < 3; $i++) { 
			self::add_form_id($openid, $prepay_id);
		}

		return true;
	}

	public static function delete_expired_form_ids(){
		return self::Query()->where_lt('time', time()-DAY_IN_SECONDS*7+600)->delete();
	}

	protected static $handler;
	protected static $appid;

	public static function get_handler(){
		if(empty(static::$handler)){
			static::$handler =	new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'cache_group'		=> 'weapp_user_form_ids',
				'cache'				=> false,
				'searchable_fields'	=> [],
				'filterable_fields'	=> [],
			));
		}

		return static::$handler;
	}

	public static function get_table(){
		global $wpdb;

		return $wpdb->base_prefix . 'weapp_user_form_ids';
	}

	public static function create_table(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;
		
		$table	= self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` VARCHAR(32) NULL,
				`openid` varchar(30)	NOT NULL,
				`form_id` varchar(64) NOT NULL,
				`time` int(10) NOT NULL,
				PRIMARY KEY	(`id`),
				KEY `appid` (`appid`),
				KEY `openid` (`openid`),
				KEY `time` (`time`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";

			dbDelta($sql);
		}
	}
}