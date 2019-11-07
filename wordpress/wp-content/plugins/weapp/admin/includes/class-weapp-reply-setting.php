<?php
class WEAPP_AdminReplySetting extends WEAPP_ReplySetting{
	public static function get($id){
		$reply = parent::get($id);
		if (!$reply) {
			return $reply;
		}

		if (is_admin()) {
			if ($reply['msg_type'] == 'event') {
				$reply['sessionfrom'] = $reply['keyword'];
			}
		}

		$reply_type = ($reply['reply_type']) ?? 'text';

		$reply[$reply_type] = maybe_unserialize($reply['reply']);

		return $reply;
	}

	public static function insert($data){
		$msg_type = $data['msg_type'] ?? 'text';

		if ($msg_type == 'text') {
			$cache_key = 'weapp_' . $msg_type . '_full_replies';
			self::cache_delete($cache_key);
			$cache_key = 'weapp_' . $msg_type . '_prefix_replies';
			self::cache_delete($cache_key);
			$cache_key = 'weapp_' . $msg_type . '_fuzzy_replies';
			self::cache_delete($cache_key);
		} else {
			$cache_key = 'weapp_' . $msg_type . '_replies';
			self::cache_delete($cache_key);
		}

		$data = self::prepare($data);

		return parent::insert($data);
	}

	public static function update($id, $data){
		$reply = self::get($id);
		if (!$reply) {
			return new WP_Error('reply_no_exists', '该自定义回复不存在');
		}

		$msg_type = $reply['msg_type'] ?? 'text';

		if (is_admin()) {
			$data['msg_type'] = $msg_type;
		}

		$data = self::prepare($data);

		$result = parent::update($id, $data);

		if ($msg_type == 'text') {

			$cache_key = 'weapp_' . $msg_type . '_full_replies';
			self::cache_delete($cache_key);
			$cache_key = 'weapp_' . $msg_type . '_prefix_replies';
			self::cache_delete($cache_key);
			$cache_key = 'weapp_' . $msg_type . '_fuzzy_replies';
			self::cache_delete($cache_key);
		} else {
			$cache_key = 'weapp_' . $msg_type . '_replies';
			self::cache_delete($cache_key);
		}

		return $result;
	}

	public static function prepare($data){
		if (is_admin()) {

			$data['time'] = $data['time'] ?? time();

			$msg_type = $data['msg_type'];

			if ($msg_type == 'event') {
				$data['keyword'] = $data['sessionfrom'];
			}

			unset($data['sessionfrom']);

			$reply_type = $data['reply_type'] ?? 'text';

			$data['reply'] = ($reply_type == 'transfer_customer_service') ? '' : maybe_serialize($data[$reply_type]);
			$data['appid'] = static::get_appid();

			unset($data['text']);
			unset($data['image']);
			unset($data['link']);
			unset($data['miniprogrampage']);
		}

		return $data;
	}

	public static function delete($id){
		$reply = self::get($id);
		if (!$reply) {
			return new WP_Error('reply_no_exists', '该自定义回复不存在');
		}

		$msg_type  = $reply['msg_type'];
		$cache_key = ($msg_type == 'text') ? 'weapp_' . $msg_type . '_' . $reply['match'] . '_replies' : 'weapp_' . $msg_type . '_replies';

		self::cache_delete($cache_key);

		return parent::delete($id);
	}

	public static function get_fields($action_key='', $id=0){
		

		$fields	= array(
			'msg_type'		=> array('title'=>'消息类型',		'type'=>'radio',	'show_admin_column'=>true,	'options'=>WEAPP_AdminMessage::get_types()+array('default'=>'默认')),
			'keyword'		=> array('title'=>'关键字',	 	'type'=>'text',		'show_admin_column'=>true),
			'match'			=> array('title'=>'匹配方式', 	'type'=>'radio',	'options'=>self::get_matches()),
			'sessionfrom'	=> array('title'=>'进入会话参数',	'type'=>'text'),
			'reply_type' 	=> array('title'=>'回复类型',		'type'=>'radio', 	'show_admin_column'=>true,	'options'=>self::get_types()),
			'reply'			=> array('title'=>'回复内容',		'type'=>'view',		'show_admin_column'=>'only'),
			'text'			=> array('title'=>'内容',		'type'=>'textarea'),
			'image'			=> array('title'=>'图片',	 	'type'=>'img'),
			'link'			=> array('title'=>'图文链接', 	'type'=>'fieldset',		'fieldset_type'=>'array',	'fields'=>array(
				'title'			=> array('title'=>'标题', 	'type'=>'text'),
				'description'	=> array('title'=>'描述', 	'type'=>'textarea'),
				'url'			=> array('title'=>'链接', 	'type'=>'url'),
				'thumb_url'		=> array('title'=>'图片', 	'type'=>'image'),
			)),
			'miniprogrampage'=> array('title'=>'小程序卡片', 	'type'=>'fieldset',		'fieldset_type'=>'array',	'fields'=>array(
				'title'			=> array('title'=>'标题', 	'type'=>'text'),
				'pagepath'		=> array('title'=>'路径', 	'type'=>'text'),
				'image'			=> array('title'=>'封面图', 	'type'=>'img'),
			)),
			'status'		=> array('title'=>'状态',		'type'=>'checkbox',	'value'=> 1,'description'=>'激活'),
		);

		if($action_key == 'edit'){
			$weapp_reply = self::get($id);

			$fields['msg_type']['type'] = 'view';

			if ($weapp_reply['msg_type'] == 'text') {
				unset($fields['sessionfrom']);
			}elseif ($weapp_reply['msg_type'] == 'event') {
				unset($fields['keyword']);
				unset($fields['match']);
				$fields['sessionfrom']['value'] = $weapp_reply['keyword'];
			} else {
				unset($fields['keyword']);
				unset($fields['match']);
				unset($fields['sessionfrom']);
			}
		}

		return $fields;
	}

	public static function list($limit, $offset){
		self::Query()->where('appid', parent::get_appid());

		return parent::list($limit, $offset);
	}

	public static function item_callback($item){
		if ($item['reply_type'] == 'image') {
			$item['reply'] = '<img src="' . wpjam_get_thumbnail(
					wp_get_attachment_url($item['reply']),
					400
				) . '" width="200"/>';
		} elseif ($item['reply_type'] == 'link') {
			$reply = maybe_unserialize($item['reply']);

			$item['reply'] = '<div class="reply_item"><a target="_blank" href="' . $reply['url'] . '">';
			$item['reply'] .= '<h3>' . $reply['title'] . '</h3>';
			$item['reply'] .= '<div class="img_container small" style="background-image:url(' . wpjam_get_thumbnail(
					$reply['thumb_url'],
					['width' => 80, 'height' => 80, 'mode' => 1]
				) . ');"></div>';
			$item['reply'] .= '<p>' . $reply['description'] . '</p>';
			$item['reply'] .= '</a></div>';

		} elseif ($item['reply_type'] == 'miniprogrampage') {
			$reply = maybe_unserialize($item['reply']);

			$item['reply'] = '<div class="reply_item">
				<h3>' . $reply['title'] . '</h3>
				<div class="img_container big" style="background-image:url(' . wpjam_get_thumbnail(
					wp_get_attachment_url($reply['image']),
					['width' => 640, 'height' => 320, 'mode' => 1]
				) . '); background-size:320px 160px;">
				</div>
				<p>路径：' . $reply['pagepath'] . '</p>
			</div>';
		}

		if ($item['msg_type'] == 'text') {
			$matches	= self::get_matches(); 
			$match		= $matches[$item['match']] ?? $item['match'];
			$item['keyword']	.= '<br />'.$match.'匹配';
		} elseif ($item['msg_type'] == 'event') {
			$item['match'] = '';
		} else {
			$item['sessionfrom'] = '';
			$item['keyword']	 = '';
			$item['match']	   = '';
		}

		return $item;
	}

	public static function views(){
		global $wpdb, $current_admin_url, $plugin_page;

		$appid = static::get_appid();

		$msg_types = WEAPP_AdminMessage::get_types() + ['default' => '默认'];

		$msg_type = ($_GET['msg_type']) ?? '';
		$status   = ($_GET['status']) ?? 1;

		$total	  = static::Query()->where('appid', $appid)->where('status', 1)->get_var('count(*)');
		$status_0   = static::Query()->where('appid', $appid)->where('status', 0)->get_var('count(*)');
		$msg_counts = static::Query()->where('appid', $appid)->where('status', 1)->group_by('msg_type')->order_by(
			'count'
		)->get_results('COUNT( * ) AS count, `msg_type`');


		$views = [];

		$class		= (empty($msg_type) && $status) ? 'class="current"' : '';
		$views['all'] = '<a href="' . $current_admin_url . '" ' . $class . '>全部<span class="count">（' . $total . '）</span></a>';

		foreach ($msg_counts as $count) {
			$class	= ($msg_type == $count['msg_type']) ? 'class="current"' : '';
			$msg_type = ($msg_types[$count['msg_type']]) ?? $count['msg_type'];

			$views['msg-' . $count['msg_type']] = '<a href="' . $current_admin_url . '&msg_type=' . $count['msg_type'] . '" ' . $class . '>' . $msg_type . '<span class="count">（' . $count['count'] . '）</span></a>';
		}

		$class			 = empty($status) ? 'class="current"' : '';
		$views['status-0'] = '<a href="' . $current_admin_url . '&status=0" ' . $class . '>未激活<span class="count">（' . $status_0 . '）</span></a>';

		return $views;
	}

	public static function get_types(){
		return [
			'text'						=> '文本',
			'image'						=> '图片',
			'link'						=> '图文链接',
			'miniprogrampage'			=> '小程序卡片',
			'transfer_customer_service' => '转发到客服工具',
		];
	}

	public static function get_matches(){
		return [
			'full'		=> '完全',
			'prefix'	=> '前缀', 
			'fuzzy'		=> '模糊'
		];
	}
}
