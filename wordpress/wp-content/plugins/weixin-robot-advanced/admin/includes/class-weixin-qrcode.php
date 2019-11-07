<?php
class WEIXIN_Qrcode extends WPJAM_Model {
	use WEIXIN_Trait;
	
	public static 	$qrcode_types = array(
		'QR_LIMIT_SCENE'	=> '永久二维码',
		'QR_SCENE'			=> '临时二维码'
	);

	public static function get_qrcode($scene){
		$appid	= static::get_appid();
		$qrcode = wp_cache_get($scene, 'weixin_qrcode');

		if($qrcode === false){
			$qrcode = static::Query()->where('appid', static::get_appid())->where('scene', $scene)->get_row();
			wp_cache_set($scene, $qrcode, 'weixin_qrcode', DAY_IN_SECONDS);
		}

		return $qrcode;
	}

	public static function insert($data){
		if(empty($data['scene'])){
			return;
		}

		$type	= $data['type'];
		$scene	= $data['scene'];
		$expire	= $data['expire']??0;

		if(self::get_qrcode($scene)){
			return new WP_Error('scene_already_added','该 scene 已存在');
		}

		$response	= weixin()->create_qrcode($type, $scene, $expire); 

		if(is_wp_error($response)){
			return $response;
		}

		$data['ticket']	= $response['ticket'];
		$data['appid']	= static::get_appid();

		if($type == 'QR_SCENE' || $type == 'QR_STR_SCENE'){
			$data['expire'] = time()+$response['expire_seconds'];
		}

		wp_cache_delete($data['scene'], 'weixin_qrcode');

		return parent::insert($data);
	}

	public static function update($id, $data){
		$qrcode	= self::get($id);

		if(!$qrcode){
			return new WP_Error('qrcode_not_exits','该 qrcode 不存在');
		}

		$scene	= $qrcode['scene'];

		wp_cache_delete($scene, 'weixin_qrcode');

		return parent::update($id, $data);
	}

	public static function delete($id){
		$qrcode	= self::get($id);

		if(!$qrcode){
			return new WP_Error('qrcode_not_exits','该 qrcode 不存在');
		}

		$scene	= $qrcode['scene'];

		wp_cache_delete($data['scene'], 'weixin_qrcode');

		return parent::delete($id);
	}

	public static function subscribe($id, $data){
		$reply_type		= $data['reply_type']??'text';
		$reply			= maybe_serialize($data[$reply_type]);

		$reply_data		= [
			'keyword'	=> $data['keyword'],
			'match'		=> $data['match']??'full',
			'type'		=> $reply_type,
			$reply_type	=> $reply,
			'status'	=> 1
		];

		return WEIXIN_AdminReplySetting::set($reply_data);
	}

	public static function scan($id, $data){
		return self::subscribe($id, $data);
	}

	public static function list($limit, $offset){
		global $plugin_page;

		self::Query()->where('appid', static::get_appid());
		
		if($plugin_page == 'weixin-qrcode-stats'){

			global $qrscene_counts, $scene_counts, $wpjam_stats_labels;
			echo '<h2>渠道统计分析</h2>'; 
			wpjam_stats_header();

			extract($wpjam_stats_labels);

			extract(parent::list($limit, $offset));

			$scenes		= array_column($items, 'scene');
			$qrscenes	= array_map(function($scene){ return 'qrscene_'.$scene; }, $scenes);
			
			$scene_counts	= WEIXIN_Message::Query()->where('MsgType', 'event')->where('Event', 'SCAN')->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where_in('EventKey',$scenes)->group_by('EventKey')->get_results('EventKey, count(*) as count');
			$qrscene_counts	= WEIXIN_Message::Query()->where('MsgType', 'event')->where('Event', 'subscribe')->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->where_in('EventKey',$qrscenes)->group_by('EventKey')->get_results('EventKey, count(*) as count');

			$scene_counts	= array_combine(array_column($scene_counts, 'EventKey'), array_column($scene_counts, 'count'));
			$qrscene_counts	= array_combine(array_column($qrscene_counts, 'EventKey'), array_column($qrscene_counts, 'count'));

			return compact('items', 'total');
		}else{
			return parent::list($limit, $offset);
		}
	}

	public static function item_callback($item){
		global $plugin_page, $current_admin_url;

		$scene = $item['scene'];

		$item['expire']	= ($item['type']=='QR_SCENE')?(($item['expire']-time()>0)?get_date_from_gmt(date('Y-m-d H:i:s', $item['expire'])):'已过期'):'';
		
		if($plugin_page == 'weixin-qrcode'){
			$item['ticket']	= '<img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($item['ticket']).'" width="100">';

			// $row_actions	= array(
			// 	// 'subscribe'	=> WEIXIN_AdminReplySetting::set_keyword('[subscribe_'.$scene.']', '关注回复'),
			// 	// 'scan'		=> WEIXIN_AdminReplySetting::set_keyword('[scan_'.$scene.']', '扫描回复'),
			// );

			// $item['row_actions']	= $row_actions + $item['row_actions'];
		}else{
			global $qrscene_counts, $scene_counts;
			$item['qrscene_count']	= isset($qrscene_counts['qrscene_'.$scene])?'<a href="'.admin_url('admin.php?page=weixin-robot-users&subscribe=qrscene_'.$scene).'">'.$qrscene_counts['qrscene_'.$scene].'</a>':'';
			$item['scene_count']	= isset($scene_counts[$scene])?'<a href="'.admin_url('admin.php?page=weixin-robot-users&scan='.$scene).'">'.$scene_counts[$scene].'</a>':'';
		}

		return $item;
	}

	public static function views(){
		global $current_admin_url;
	
		$qrcode_types	= self::$qrcode_types;
		$type			= isset($_GET['type']) ? $_GET['type'] : '';
		$total 			= self::Query()->where('appid', static::get_appid())->get_var('count(*)');
		$counts 		= self::Query()->where('appid', static::get_appid())->group_by('type')->order_by('count')->get_results('COUNT( * ) AS count, `type`');
		$views			= [];

		$class = empty($type) ? 'class="current"':'';
		$views['all'] = '<a href="'.$current_admin_url.'" '.$class.'>全部<span class="count">（'.$total.'）</span></a>';

		foreach ($counts as $count) { 
			$class = ($type == $count['type']) ? 'class="current"':'';
			$views[$count['type']] = '<a href="'.$current_admin_url.'&type='.$count['type'].'" '.$class.'>'.$qrcode_types[$count['type']].'<span class="count">（'.$count['count'].'）</span></a>';
		}

		return $views;
	}

	public static function get_actions(){
		global $plugin_page;

		if($plugin_page == 'weixin-qrcode-stats'){
			return [];
		}else{
			return [
				'add'		=> ['title' =>'新增'],
				'edit'		=> ['title'	=>'编辑'],
				'subscribe'	=> ['title'	=>'关注回复'],
				'scan'		=> ['title'	=>'扫描回复'],
				'delete'	=> ['title'	=>'删除',	'confirm'=>true,	'direct'=>true,	'bulk'=>true]
			];
		}
	}

	public static function list_page(){
		?>
		<script type="text/javascript">
		jQuery(function ($) {
			$('select#type').change(function () {
				var selected = $(this).val();
				if (selected == 'QR_LIMIT_SCENE') {
					$('#tr_expire').hide();
				} else if (selected == 'QR_SCENE') {
					$('#tr_expire').show();
				}

				tb_position();
			});

			$('body').on('list_table_action_success', function(response){
				$('body select#type').change();
			});
		});
		</script>
		<?php

		WEIXIN_AdminReplySetting::list_page();
	}

	public static function get_fields($action_key='', $id=''){
		global $plugin_page;

		if($plugin_page == 'weixin-qrcode-stats'){
			return [
				'name'			=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>true],
				'scene'			=> ['title'=>'场景 ID',	'type'=>'number',	'show_admin_column'=>true],
				'qrscene_count'	=> ['title'=>'关注',		'type'=>'number',	'show_admin_column'=>true],
				'scene_count'	=> ['title'=>'扫描',		'type'=>'number',	'show_admin_column'=>true],
			];
		}else{
			if($action_key == 'subscribe' || $action_key=='scan'){
				global $current_tab;

				$fields		= WEIXIN_AdminReplySetting::get_fields();
				$item		= self::get($id);

				if($action_key == 'subscribe'){
					$keyword	= '[subscribe_'.$item['scene'].']';
				}elseif($action_key == 'scan'){
					$keyword	= '[scan_'.$item['scene'].']';
				}

				$fields['keyword']['value']	= $keyword;

				$custom_reply	= WEIXIN_AdminReplySetting::get_by_keyword($keyword);
				if($custom_reply){
					$reply_type		= $custom_reply['type'];
					$fields['match']['value']		= $custom_reply['match'];
					$fields['reply_type']['value']	= $reply_type;
					$fields[$reply_type]['value']	= $custom_reply['reply'];
				}

				unset($fields['status']);
			}else{
				$fields	= [
					'ticket'	=> ['title'=>'二维码',	'type'=>'text',		'show_admin_column'=>'only'],
					'name'		=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>true,		'required',	'description'=>'二维码名称无实际用途，仅用于更加容易区分。'],
					'scene'		=> ['title'=>'场景 ID',	'type'=>'number',	'show_admin_column'=>true,		'min'=>'1',	'max'=>'100000',	'required',	'description'=>'临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）'],
					'type'		=> ['title'=>'类型',		'type'=>'select',	'show_admin_column'=>true,		'options'=> WEIXIN_Qrcode::$qrcode_types],
					'expire'	=> ['title'=>'过期时间',	'type'=>'text',		'show_admin_column'=>true,		'description'=> '二维码有效时间，以秒为单位。最大不超过1800'],
				];

				if($action_key == 'edit'){
					if($id){
						$weixin_qrcode = self::get($id);

						unset($fields['ticket']);

						$fields['scene']['type']	= 'view';
						$fields['type']['type']		= 'view';

						if($weixin_qrcode['type'] == 'QR_LIMIT_SCENE'){
							unset($fields['expire']);
						}else{
							$fields['expire']['type']	= 'view';
						}
					}
				}
			}
		}

		return $fields;
	}

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix.'weixin_qrcodes';
	}

	protected static $handler;
    protected static $appid;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'id',
				'field_types'		=> ['id'=>'%d','expire'=>'%d','time'=>'%d'],
				'searchable_fields'	=> ['name', 'scene'],
				'filterable_fields'	=> ['type'],
			));
		}
		
		return static::$handler;
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
				`scene` varchar(64) NOT NULL,
				`name` varchar(255) NOT NULL,
				`type` varchar(31) NOT NULL,
				`ticket` text NOT NULL,
				`expire` int(10) NOT NULL,
				`time` int(10) NOT NULL,
				PRIMARY KEY  (`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);

			$wpdb->query("
				ALTER TABLE `{$table}`
				ADD KEY `type` (`type`);");
		}
	}
}