<?php
class WEAPP_AdminMessage extends WEAPP_Message{
	public static function reply_text($id, $data){
		return WEAPP_AdminReplySetting::reply($data['touser'], $data);
	}

	public static function reply_image($id, $data){
		return WEAPP_AdminReplySetting::reply($data['touser'], $data);
	}

	public static function reply_link($id, $data){
		return WEAPP_AdminReplySetting::reply($data['touser'], $data);
	}

	public static function reply_miniprogrampage($id, $data){
		return WEAPP_AdminReplySetting::reply($data['touser'], $data);
	}

	public static function list($limit, $offset){
		self::Query()->where('appid', self::get_appid());
		return parent::list($limit, $offset);
	}

	public static function get_actions(){
		$reply_types	= WEAPP_AdminReplySetting::get_types();
		unset($reply_types['transfer_customer_service']);

		$actions	= [];
		foreach ($reply_types as $key => $title) {
			$actions['reply_'.$key]	= array('title'=>'回复'.$title);
		}

		return $actions;
	}

	public static function get_fields($action_key='', $id=''){
		if($action_key == ''){
			return [
				'username'		=> array('title'=>'用户',	'type'=>'text',	'show_admin_column'=>true),
				'Content'		=> array('title'=>'内容',	'type'=>'text',	'show_admin_column'=>true),
				'MsgType'		=> array('title'=>'类型',	'type'=>'text',	'show_admin_column'=>true, 'options'=>self::get_types()),
				'CreateTime'	=> array('title'=>'时间',	'type'=>'text',	'show_admin_column'=>true)
			];
		}else{
			$fields		= array();

			$message	= self::get($id);
			$touser		= $message['FromUserName'];
			$user		= WEAPP_User::prepare($touser);

			if($user){
				$fields['username'] = array('title'=>'用户',	'type'=>'view', 'value'=>$user['username']);
			}

			$reply_type	= str_replace('reply_', '', $action_key);

			if($reply_type == 'text') {
				$fields['reply']	= array('title'=>'内容',		'type'=>'textarea');
			}elseif($reply_type == 'image') {
				$fields['reply']	= array('title'=>'图片',		'type'=>'img');
			}elseif($reply_type == 'link') {
				$fields['reply']	= array('title'=>'图文链接', 	'type'=>'fieldset',		'fieldset_type'=>'array',	'fields'=>array(
					'title'			=> array('title'=>'标题',	'type'=>'text'),
					'description'	=> array('title'=>'描述',	'type'=>'textarea'),
					'url'			=> array('title'=>'链接',	'type'=>'url'),
					'thumb_url'		=> array('title'=>'图片',	'type'=>'image'),
				));
			}elseif($reply_type == 'miniprogrampage') {
				$fields['reply']	= array('title'=>'小程序卡片', 	'type'=>'fieldset',		'fieldset_type'=>'array',	'fields'=>array(
					'title'			=> array('title'=>'标题',	'type'=>'text'),
					'pagepath'		=> array('title'=>'路径',	'type'=>'text'),
					'image'			=> array('title'=>'封面图',	'type'=>'img'),
				));
			}

			$fields['reply_type']	= array('title'=>'',	'type'=>'hidden', 'value'=>$reply_type);
			$fields['touser']		= array('title'=>'',	'type'=>'hidden', 'value'=>$touser);

			return $fields;
		}
	}

	public static function item_callback($item){
		$user	= WEAPP_User::prepare($item['FromUserName']);

		$item['username']	= ($user)?$user['username']:'';

		$MsgType	= $item['MsgType'];

		if($MsgType == 'text'){
			$item['Content']	= wpautop(wp_strip_all_tags($item['Content']));	// 过滤用户输入的 html 代码，防止攻击
		}elseif($MsgType == 'image'){
			$item['Content']	= '<img src="'.$item['PicUrl'].'" width="200" />';
		}elseif($MsgType == 'event'){
			if($item['Event'] == 'user_enter_tempsession'){
				$item['Content']	= '用户从 <strong>'.$item['SessionFrom'].'</strong> 进入会话';
			}elseif($item['Event'] == 'kf_create_session'){
				$item['Content']	= '用户从自定义回复转到客服工具，客服：'.$item['KfAccount'];
			}elseif($item['Event'] == 'kf_close_session'){
				$item['Content']= '结束客服，转回自定义回复，客服：'.$item['KfAccount'].'，结束类型：'.$item['CloseType'];
			}
		}

		$item['CreateTime']		= get_date_from_gmt(date('Y-m-d H:i:s',$item['CreateTime']));

		return $item;
	}

	public static function views(){
		global $wpdb, $current_admin_url, $plugin_page;

		$appid			= self::get_appid();
		$MsgTypes		= self::get_types();	
		$MsgType		= ($_GET['MsgType'])??'';
		
		$total			= static::Query()->where('appid',$appid)->get_var('count(*)');
		$msg_counts 	= static::Query()->where('appid',$appid)->group_by('MsgType')->order_by('count')->get_results('COUNT( * ) AS count, `MsgType`');

		$views	= array();

		$class = empty($MsgType)? 'class="current"':'';
		$views['all'] = '<a href="'.$current_admin_url.'" '.$class.'>全部<span class="count">（'.$total.'）</span></a>';

		foreach ($msg_counts as $count) { 
			$class		= ($MsgType == $count['MsgType']) ? 'class="current"':'';
			$MsgType	= ($MsgTypes[$count['MsgType']])??$count['MsgType'];

			$views['msg-'.$count['MsgType']]	= '<a href="'.$current_admin_url.'&MsgType='.$count['MsgType'].'" '.$class.'>'.$MsgType.'<span class="count">（'.$count['count'].'）</span></a>';
		}

		return $views;
	}

	public static function get_types(){
		return [
			'text'				=>'文本',
			'event'				=>'事件',
			'image'				=>'图片',
			'miniprogrampage'	=>'小程序卡片'
		];
	}
}
