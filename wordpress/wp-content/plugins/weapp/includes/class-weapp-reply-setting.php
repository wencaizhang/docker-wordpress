<?php
class WEAPP_ReplySetting extends WPJAM_Model{
	use WEAPP_Trait;

	public static function reply($touser, $data){
		$reply_type = $data['reply_type'] ?? 'text';

		if ($reply_type == 'text') {
			$content = $data['reply'];
			if (!$content) {
				return false;
			}

			$reply_content = compact('content');
		} elseif ($reply_type == 'image') {
			if (empty($data['reply'])) {
				return new WP_Error('image__missing', '图片回复的图片字段为空');
			}

			$media_id = weapp_get_wp_img_media_id($data['reply']);
			if (is_wp_error($media_id)) {
				return $media_id;
			}

			$reply_content = compact('media_id');

		} elseif ($reply_type == 'link') {
			$link = $data['reply'];

			foreach (['title', 'description', 'url', 'thumb_url'] as $key) {
				if (empty($link[$key])) {
					return new WP_Error('link_' . $key . '_missing', '图文链接回复的' . $key . '字段为空');
				}
			}

			$reply_content = $link;
		} elseif ($reply_type == 'miniprogrampage') {
			$miniprogrampage = $data['reply'];

			foreach (['title', 'pagepath', 'image'] as $key) {
				if (empty($miniprogrampage[$key])) {
					return new WP_Error('miniprogrampage_' . $key . '_missing', '小程序卡片回复的' . $key . '字段为空');
				}
			}

			$media_id = weapp_get_wp_img_media_id($miniprogrampage['image']);
			if (is_wp_error($media_id)) {
				return $media_id;
			}

			$miniprogrampage['thumb_media_id'] = $media_id;
			unset($miniprogrampage['image']);

			$reply_content = $miniprogrampage;
		} elseif ($reply_type == 'transfer_customer_service') {
			return 'transfer_customer_service';
		}

		return weapp_send_custom_message([
			'touser'	=> $touser,
			'msgtype'   => $reply_type,
			$reply_type => $reply_content,
		]);
	}

	public static function cache_get($cache_key){
	   return wp_cache_get($cache_key, static::get_appid());
	}

	public static function cache_set($cache_key, $data){
		wp_cache_set($cache_key, $data, static::get_appid(), DAY_IN_SECONDS);
	}

	public static function cache_delete($cache_key){
		wp_cache_delete($cache_key, static::get_appid());
	}

	public static function get_replies($msg_type, $keyword = ''){
		$replies = [];
		if ($msg_type == 'image' || $msg_type == 'miniprogrampage' || $msg_type == 'default') {
			$replies = static::get_keywords($msg_type);
			if (!$replies) {
				return false;
			}
		} elseif ($msg_type == 'event') {
			$replies = static::get_keywords($msg_type);

			if ($replies && isset($replies[$keyword])) {
				$replies = $replies[$keyword];
			} else {
				return false;
			}
		} elseif ($msg_type == 'text') {
			$prefix_keyword = mb_substr($keyword, 0, 2);	// 前缀匹配，只支持2个字

			$replies		= static::get_keywords($msg_type, 'full');		// 完全匹配
			$replies_prefix = static::get_keywords($msg_type, 'prefix');	// 前缀匹配
			$replies_fuzzy  = static::get_keywords($msg_type, 'fuzzy');		// 模糊匹配

			$keyword = strtolower(trim($keyword));
			if ($replies && isset($replies[$keyword])) {
				$replies = $replies[$keyword];
			} elseif ($replies_prefix && isset($replies_prefix[$prefix_keyword])) {
				$replies = $replies_prefix[$prefix_keyword];
			} elseif ($replies_fuzzy && preg_match(
					'/' . implode('|', array_keys($replies_fuzzy)) . '/',
					$keyword,
					$matches
				)) {
				$fuzzy_keyword = $matches[0];
				$replies	   = $replies_fuzzy[$fuzzy_keyword];
			} else {
				return false;
			}
		}

		if (isset($_GET['debug'])) {
			print_r($replies);
		}

		// $rand_key = array_rand($replies, 1);
		// $replies = $replies[$rand_key];

		return $replies;
	}

	public static function get_keywords($msg_type = 'text', $match = null){

		$cache_key = ($msg_type == 'text') ? 'weapp_' . $msg_type . '_' . $match . '_replies' : 'weapp_' . $msg_type . '_replies';
		$replies   = self::cache_get($cache_key);

		if ($replies === false) {
			$results = static::Query()->where('appid', static::get_appid())->where('msg_type', $msg_type)->where('status', 1)->where('match', $match)->get_results();

			if ($msg_type == 'text' || $msg_type == 'event') {
				$replies = [];

				if ($results) {
					foreach ($results as $reply) {
						$key = strtolower(trim($reply['keyword']));
						if (strpos($key, ',')) {
							foreach (explode(',', $key) as $new_key) {
								$new_key = strtolower(trim($new_key));
								if ($new_key !== '') {
									$replies[$new_key][] = $reply;
								}
							}
						} else {
							$replies[$key][] = $reply;
						}
					}
				}
			} else {
				$replies = ($results) ?: [];
			}

			self::cache_set($cache_key, $replies);
		}

		return $replies;
	}
	
	protected static $handler;
	protected static $appid;

	public static function get_handler()
	{
		if (is_null(static::$handler)) {
			global $wpdb;
			static::$handler = new WPJAM_DB(
				$wpdb->base_prefix . 'weapp_replies', [
					'primary_key'		=> 'id',
					'field_types'		=> ['id' => '%d', 'status' => '%d'],
					'searchable_fields'	=> ['keyword'],
					'filterable_fields'	=> ['msg_type', 'reply_type', 'status'],
				]
			);
		}

		return static::$handler;
	}

	public static function create_table()
	{
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$wpdb->weapp_replies = $wpdb->base_prefix . 'weapp_replies';

		if ($wpdb->get_var("show tables like '{$wpdb->weapp_replies}'") != $wpdb->weapp_replies) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->weapp_replies}` (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` varchar(32) NOT NULL,
				`keyword` varchar(255)	NOT NULL,
				`msg_type` varchar(7)	NOT NULL,
				`match` varchar(7) NOT NULL,
				`reply` text NOT NULL,
				`reply_type` varchar(31) NOT NULL,
				`status` int(1) NOT NULL,
				`time` int(10) NOT NULL,
				PRIMARY KEY	(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";

			dbDelta($sql);
		}
	}
}
