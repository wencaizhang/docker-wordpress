<?php
class WPJAM_Notice extends WPJAM_Model{
	public static $errors = [];

	public static function add($notice, $user_id=0){
		$notice['user_id']	= $user_id;
		$notice['time']		= $notice['time'] ?? time();
		$notice['key']		= $notice['key'] ?? md5(maybe_serialize($notice));

		$notices	= self::get_notices($user_id);
		$key		= $notice['key'];

		$notices[$key]	= $notice;

		return self::update_notices($notices, $user_id);
	}

	public static function delete($key, $user_id=0){
		$result		= false;
		$notices	= self::get_notices($user_id);

		if(isset($notices[$key])){
			unset($notices[$key]);
			$result	= self::update_notices($notices, $user_id);
		}

		if(!$user_id){
			return self::delete($key, get_current_user_id());
		}else{
			return $result;
		}
	}

	public static function get_notices($user_id=0){
		if($user_id){
			$notices	= get_user_meta($user_id, 'wpjam_notices', true) ?: [];
		}else{
			$notices	= get_option('wpjam_notices') ?: [];
		}

		if($notices){
			return array_filter($notices, function($notice){
				return $notice['time'] > time() - MONTH_IN_SECONDS * 3;
			});
		}else{
			return [];
		}
	}

	public static function update_notices($notices, $user_id=0){
		if($user_id){
			if(empty($notices)){
				return delete_user_meta($user_id, 'wpjam_notices');
			}else{
				return update_user_meta($user_id, 'wpjam_notices', $notices);
			}
		}else{
			if(empty($notices)){
				return delete_option('wpjam_notices');
			}else{
				return update_option('wpjam_notices', $notices);
			}
		}
	}
}

wp_cache_add_global_groups(['wpjam_messages']);

class WPJAM_Message extends WPJAM_Model {
	public static function insert($data){
		$data = wp_parse_args($data, [
			'sender'	=> get_current_user_id(),
			'receiver'	=> '',
			'type'		=> '',
			'content'	=> '',
			'status'	=> 0,
			'time'		=> time()
		]);

		$data['content'] = wp_strip_all_tags($data['content']);

		return parent::insert($data);
	}

	public static function get_unread_count(){
		return self::Query()->where('receiver', get_current_user_id())->where('status', 0)->get_var('count(*)');
	}

	public static function set_all_read(){
		return self::Query()->where('receiver', get_current_user_id())->where('status', 0)->update(['status'=>1]);
	}

	private static 	$handler;

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'messages';
	}

	public static function get_handler(){
		global $wpdb;


		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), [
				'primary_key'		=> 'id',
				'cache_group'		=> 'wpjam_messages',
				'field_types'		=> ['id'=>'%d','time'=>'%d'],
				'searchable_fields'	=> ['content'],
				'filterable_fields'	=> ['type'],
			]);
		}
		return self::$handler;
	}

	public static function create_table($appid=''){
		global $wpdb;

		$table	= self::get_table($appid);

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if($wpdb->get_var("show tables like '{$table}'") != $table){
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` bigint(20) NOT NULL auto_increment,
				`sender` bigint(20) NOT NULL,
				`receiver` bigint(20) NOT NULL,
				`type` varchar(15) NOT NULL,
				`blog_id` bigint(20) NOT NULL,
				`post_id` bigint(20) NOT NULL,
				`comment_id` bigint(20) NOT NULL,
				`content` text NOT NULL,
				`status` int(1) NOT NULL,
				`time` int(10) NOT NULL,
				PRIMARY KEY	(`id`),
				KEY `type_idx` (`type`),
				KEY `blog_id_idx` (`blog_id`),
				KEY `sender_idx` (`sender`),
				KEY `receiver_idx` (`receiver`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}