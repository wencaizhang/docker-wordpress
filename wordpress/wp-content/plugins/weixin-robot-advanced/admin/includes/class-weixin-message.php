<?php
// global $wpdb;
// $wpdb->weixin_messages	= WEIXIN_Message::get_table();

include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-message.php');

class WEIXIN_AdminMessage extends WEIXIN_Message {
	public static function views(){
		global $plugin_page, $current_tab;

		if($plugin_page == 'weixin-messages'){
			global $current_admin_url;

			$msg_types	= self::get_message_types();
			$msg_types['manual'] = '需要人工回复';
			$msg_type	= isset($_GET['MsgType']) ? $_GET['MsgType'] : '';

			$views	= array();
			$class	= empty($msg_type) ? 'class="current"':'';
			$views['all'] = '<a href="'.$current_admin_url.'" '.$class.'>全部</a>';

			foreach ($msg_types as $key => $value) {
				$class = ($msg_type == $key) ? 'class="current"':'';
				$views[$key] = '<a href="'.$current_admin_url.'&MsgType='.$key.'" '.$class.'>'.$value.'</a>';
			}

			return $views;
		}
	}

	public static function list($limit, $offset){
		global $plugin_page, $current_tab;

		if($plugin_page == 'weixin-messages'){
			$msg_type	= isset($_GET['MsgType']) ? $_GET['MsgType'] : '';

			if($msg_type == 'manual'){
				$items	= self::Query()->offset($offset)->limit($limit)->where('appid', weixin_get_appid())->where_in('Response', array('not-found', 'too-long'))->where_gt('CreateTime', WEIXIN_CUSTOM_SEND_LIMIT)->find();
				$total 	= self::Query()->find_total();
			}else{
				self::Query()->where('appid', weixin_get_appid())->where_not('MsgType', 'manual');
				extract(parent::list($limit, $offset));
			}

			if($items){
				$openids 	= array_column($items, 'FromUserName');
				$users		= WEIXIN_User::batch_get_user_info($openids);
			}
		}elseif($plugin_page == 'weixin-user' ){
			if($current_tab == 'subscribe'){
				$items	= self::Query()->offset($offset)->limit($limit)->where('appid', weixin_get_appid())->where('FromUserName', $_GET['openid'])->where_in('Event',['subscribe','unsubscribe'])->find();
			}else{
				$items	= self::Query()->offset($offset)->limit($limit)->where('appid', weixin_get_appid())->where_not('MsgType', 'manual')->where('FromUserName', $_GET['openid'])->find();
			}
			$total	= self::Query()->find_total();
		}elseif($plugin_page == 'weixin-users'){

			global $wpjam_stats_labels;
			extract($wpjam_stats_labels);

			wpjam_stats_header();

			$items	= self::Query()->offset($offset)->limit($limit)->where('appid', weixin_get_appid())->where('Event', 'MASSSENDJOBFINISH')->where_gt('CreateTime', $wpjam_start_timestamp)->where_lt('CreateTime', $wpjam_end_timestamp)->find();
			$total 	= self::Query()->find_total();
		}

		return compact('items', 'total');
	}

	public static function item_callback($item){
		global $plugin_page, $current_tab, $current_admin_url;
	
		if($plugin_page == 'weixin-messages' || ($plugin_page == 'weixin-user'  && $current_tab == 'messages')){

			$msg_types['manual'] = '需要人工回复';

			$MsgType 		= $item['MsgType']; 

			$Response	= $item['Response'];
			$openid		= $item['FromUserName'];
			$user		= WEIXIN_User::get($openid);

			// if(empty($_GET['openid'])) {
				if($user && ($user	= WEIXIN_AdminUser::parse_user($user))){
					$item['username']	= '<a href="'.$current_admin_url.'&FromUserName='.$openid.'">'.$user['username'].'</a>（'.$user['sex'].'）';
					// $item['address']	= $user['address'];
				}else{
					$item['username']	= '';
					// $item['address']	= '';
				}
			// }

			$item['name']	= $item['FromUserName'];

			if($MsgType == 'text'){
				$item['Content']	= wp_strip_all_tags($item['Content']); 
			}elseif($MsgType == 'link'){
				$item['Content']	= '<a href="'.$item['Url'].'" target="_blank">'.$item['Title'].'</a>';
			}elseif($MsgType == 'image'){
				if(weixin_get_type() >=3 && $item['CreateTime'] > WEIXIN_CUSTOM_SEND_LIMIT){
					$item['Content']	= '<a href="'.weixin()->get_media($item['MediaId']).'" target="_blank" title="'.$item['MediaId'].'"><img src="'.weixin()->get_media($item['MediaId']).'" alt="'.$item['MediaId'].'" width="100px;"></a>';
					$item['Content']	.= '<br /><a href="'.weixin()->get_media_download_url($item['MediaId']).'">下载图片</href>';
				}else{
					$item['Content']	.= '图片已过期，不可下载';
				}
				if(isset($_GET['debug'])) $item['Content']	.=  '<br />MediaId：'.$item['MediaId'];
			}elseif($MsgType == 'location'){
				$location = maybe_unserialize($item['Content']);
				if(is_array($location)){
					$item['Content'] = '<img src="http://st.map.qq.com/api?size=300*150&center='.$location['Location_Y'].','.$location['Location_X'].'&zoom=15&markers='.$location['Location_Y'].','.$location['Location_X'].'" />';
					if(isset($location['Label'])) $item['Content'] .= '<br />'.$location['Label'];
				}
			}elseif($MsgType == 'voice'){
				if($item['Content']){
					$item['Content']	= '语音识别成：'.wp_strip_all_tags($item['Content']);
				}
				if(weixin_get_type() >=3 && $item['CreateTime'] > WEIXIN_CUSTOM_SEND_LIMIT){
					$item['Content']	= $item['Content'].'<br /><a href="'.weixin()->get_media_download_url($item['MediaId']).'">下载语音</href>';
				}
				if(isset($_GET['debug'])) $item['Content']	.= '<br />MediaId：'.$item['MediaId'];
			}elseif($MsgType == 'video' || $MsgType == 'shortvideo'){
				if(weixin_get_type() >=3 && $item['CreateTime'] > WEIXIN_CUSTOM_SEND_LIMIT){
					$item['Content']	= '<a href="'.weixin()->get_media_download_url($item['MediaId']).'" target="_blank" title="'.$item['MediaId'].'"><img src="'.weixin()->get_media($item['Url']).'" alt="'.$item['Url'].'" width="100px;"><br >点击下载视频</a>';
				}else{
					$item['Content']	.= '视频已过期，不可下载';
				}
			}elseif($MsgType == 'event'){
				$Event = strtolower($item['Event']);
				if($Event == 'click'){
					$item['Content']	= '['.$item['Event'].'] '.$item['EventKey']; 
				}elseif($Event == 'view'){
					$item['Content']	= '['.$item['Event'].'] '.'<a href="'.$item['EventKey'].'">'.$item['EventKey'].'</a>'; 
				}elseif($Event == 'location'){
					// $location = maybe_unserialize($item['Content']);
					// if(is_array($location)){
					// 	$item['Content'] = '<img src="http://st.map.qq.com/api?size=300*150&center='.$location['Location_Y'].','.$location['Location_X'].'&zoom=15&markers='.$location['Location_Y'].','.$location['Location_X'].'" />';
					// }
					$item['Content']	= '['.$item['Event'].'] ';
				}elseif ($Event == 'templatesendjobfinish') {
					$item['Content']	= '['.$item['Event'].'] '.$item['EventKey'];
				}elseif ($Event == 'masssendjobfinish') {
					$count_array		= maybe_unserialize($item['Content']);
					if(is_array($count_array)){
						$item['Content']	= '['.$item['Event'].'] '.$item['EventKey'].'<br />'.'所有：'.$count_array['TotalCount'].'过滤之后：'.$count_array['FilterCount'].'发送成功：'.$count_array['SentCount'].'发送失败：'.$count_array['ErrorCount'];
					}
				}elseif($Event == 'scancode_push' || $Event == 'scancode_waitmsg'){
					$item['Content']	= '['.$item['Event'].'] '.$item['Title'].'<br />'.$item['Content'];
				}elseif($Event == 'location_select'){
					$location = maybe_unserialize($item['Content']);
					if(is_array($location)){
						$item['Content'] = '<img src="http://st.map.qq.com/api?size=300*150&center='.$location['Location_Y'].','.$location['Location_X'].'&zoom=15&markers='.$location['Location_Y'].','.$location['Location_X'].'" />';
						if(isset($location['Label'])) $item['Content'] .= '<br />'.$location['Label'];
					}
				}else{
					$item['Content']	= '['.$item['Event'].'] '.$item['EventKey'];
				}
			}

			if(is_numeric($Response) ){
				$item['Response'] = '人工回复';
				$reply_message = self::get($Response);
				if($reply_message){
					$item['Content']	.= '<br /><span style="background-color:yellow; padding:2px; ">人工回复：'.$reply_message['Content'].'</span>';
				}
			}elseif(isset($response_types[$Response])){
				$item['Response'] = $response_types[$Response];	
			}

			
			if($item['CreateTime'] > WEIXIN_CUSTOM_SEND_LIMIT){
				// $row_actions = array();
				if(is_numeric($Response)){
					// $item['row_actions']['reply']	= '已经回复';
					unset($item['row_actions']['reply']);
					unset($item['row_actions']['delete']);
				}elseif(empty($user['subscribe'])){
					unset($item['row_actions']['reply']);
					unset($item['row_actions']['delete']);
					// $row_actions['reply']	= '<a href="'.admin_url('admin.php?page=weixin-masssend&tab=custom&openid='.$user['openid'].'&reply_id='.$item['id'].'&TB_iframe=true&width=780&height=420').'" title="回复客服消息" class="thickbox" >回复</a>';
				}
				// $item['row_actions']	= $row_actions;
			}else{
				unset($item['row_actions']['reply']);
				unset($item['row_actions']['delete']);
				// if(isset($_GET['openid'])){
				// 	$row_actions['delete']		= '<a href="'.$current_admin_url.'&openid='.$_GET['openid'].'&action=delete&id='.$item['id'].'">删除</a>';
				// 	$item['row_actions']	= $row_actions;
				// }
			}

			$item['CreateTime']	= get_date_from_gmt(date('Y-m-d H:i:s',$item['CreateTime']));

		}elseif($plugin_page == 'weixin-user' && $current_tab == 'subscribe'){
			$item['Event']		= ($item['Event'] == 'subscribe')?'订阅':'取消订阅';

			$scene	= str_replace('qrscene_', '', $item['EventKey']);

			if($scene){
				if($qrcode = WEIXIN_Qrcode::get_qrcode($scene)) { 
					$item['EventKey']	= $qrcode['name']; 
				}else{
					$item['EventKey']	= $scene; 
				}
			}

			$item['CreateTime']	= get_date_from_gmt(date('Y-m-d H:i:s',$item['CreateTime']));
		}elseif($plugin_page == 'weixin-masssend' || $plugin_page == 'weixin-users'){
			$item['CreateTime']	= get_date_from_gmt(date('Y-m-d H:i:s',$item['CreateTime']));
			$count_list			= maybe_unserialize($item['Content']);
			if($count_list){
				$item['Status']		= isset($count_list['Status'])?$count_list['Status']:'';
				$item['TotalCount']	= $count_list['TotalCount'];
				$item['FilterCount']= $count_list['FilterCount'];
				$item['SentCount']	= $count_list['SentCount'];
				$item['SentRate']	= round($count_list['SentCount']*100/$count_list['TotalCount'],2).'%';
				$item['ErrorCount']	= $count_list['ErrorCount'];
			}else{
				$item['Status']		= '';
				$item['TotalCount']	= '';
				$item['FilterCount']= '';
				$item['SentCount']	= '';
				$item['SentRate']	= '';
				$item['ErrorCount']	= '';
			}
		}

		return $item;
	}

	public static function get_message_types($type=''){
		if($type == 'event' || $type == 'card-event'){
			return array(
				'click'				=> '点击菜单',
				'view'				=> '跳转URL',

				'subscribe'			=> '用户订阅', 
				'unsubscribe'		=> '取消订阅',

				'scancode_push'		=> '扫码推事件',
				'scancode_waitmsg'	=> '扫码带提示',
				'pic_sysphoto'		=> '系统拍照发图',
				'pic_photo_or_album'=> '拍照或者相册发图',
				'pic_weixin'		=> '微信相册发图器',
				'location_select'	=> '地理位置选择器',
				'location'			=> '获取用户地理位置',
				'scan'				=> '扫描带参数二维码',

				'user_get_card'		=> '领取卡券',
				'user_del_card'		=> '删除卡券',
				'user_consume_card'	=> '核销卡券',
				'card_pass_check'	=> '卡券通过审核',
				'card_not_pass_check'	=> '卡券未通过审核',
				'user_view_card'	=> '进入会员卡',
				'user_enter_session_from_card'	=> '从卡券进入公众号会话',
				'card_sku_remind'	=> '卡券库存报警',
				'submit_membercard_user_info'	=> '接收会员信息',

				'wificonnected'		=> 'Wi-Fi连网成功',
				'shakearoundusershake'	=> '摇一摇',
				'poi_check_notify'	=> '门店审核',
				
				'masssendjobfinish'		=> '群发信息',
				'templatesendjobfinish'	=> '收到模板消息',

				'kf_create_session'	=> '多客服接入会话',
				'kf_close_session'	=> '多客服关闭会话',
				'kf_switch_session'	=> '多客服转接会话',

				'qualification_verify_success'	=> '资质认证成功',
				'qualification_verify_fail'		=> '资质认证失败',
				'naming_verify_success'			=> '名称认证成功',	
				'naming_verify_fail'			=> '名称认证失败',
				'annual_renew'					=> '年审通知',
				'verify_expired'				=> '认证过期失效通知',	
			);
		}elseif($type == 'text'){
			return self::get_response_types();
		}elseif($type == 'menu'){
			$message_types	= array();
			// global $wpdb;
			// $buttons_list	= $wpdb->get_col("SELECT button FROM {$wpdb->weixin_menus}");
			// foreach ($buttons_list as $buttons) {
			// $buttons = json_decode($buttons,true);
			
			$menu = WEIXIN_Menu::get();

			if($buttons = $menu['button']){
				foreach($buttons as $button){
					if(empty($button['sub_button'])){
						if($button['type']	== 'view'){
							$message_types[$button['url']]	= $button['name'];
						}elseif(isset($button['key'])){
							$message_types[$button['key']]	= $button['name'];	
						}
					}else{
						foreach ($button['sub_button'] as $sub_button) {
							if($sub_button['type']	== 'view'){
								$message_types[$sub_button['url']]	= $sub_button['name'];
							}elseif($sub_button['type']	== 'miniprogram'){
								// 
							}else{
								$message_types[$sub_button['key']]	= $sub_button['name'];	
							}
						}
					}
				}
			}
			
			
			return $message_types;
		}else{
			return array(
				'text'			=>'文本消息', 
				'event'			=>'事件消息',  
				'location'		=>'位置消息', 
				'image'			=>'图片消息', 
				'link'			=>'链接消息', 
				'voice'			=>'语音消息',
				'video'			=>'视频消息',
				'shortvideo'	=>'小视频'
			);
		}

		return $message_types;
	}

	public static function get_response_types(){
		$response_types = array(
			'advanced'		=> '高级回复',

			'welcome'		=> '欢迎语',

			'subscribe'		=> '订阅',
			'unsubscribe'	=> '取消订阅',
			'scan'			=> '扫描带参数二维码',
			
			'tag'			=> '标签最新日志',
			'cat'			=> '分类最新日志',
			'taxonomy'		=> '自定义分类最新日志',
			
			'custom-text'	=> '自定义文本回复',
			'custom-img'	=> '文章图文回复',
			'custom-img2'	=> '自定义图文回复',
			'custom-news'	=> '自定义素材图文回复',
			'custom-image'	=> '自定义图片回复',
			'custom-voice'	=> '自定义音频回复',
			'custom-music'	=> '自定义音乐回复',
			'custom-video'	=> '自定义视频回复',

			'empty'			=> '空白字符回复',
			
			'query'			=> '搜索查询回复',
			'too-long'		=> '关键字太长',
			'not-found'		=> '没有匹配内容',

			'voice'			=> '语音自动回复',
			'loction'		=> '位置自动回复',
			'link'			=> '链接自动回复',
			'image'			=> '图片自动回复',
			'video'			=> '视频自动回复',

			'enter-reply'	=> '进入微信回复',
			'3rd'			=> '第三方回复',
			'view'			=> '打开网页',
			'scancode_push'		=> '扫码推事件',
			'scancode_waitmsg'	=> '扫码带提示',
			'pic_sysphoto'		=> '系统拍照发图',
			'pic_photo_or_album'=> '拍照或者相册发图',
			'pic_weixin'		=> '微信相册发图器',
			'location_select'	=> '地理位置选择器',
			'templatesendjobfinish'	=> '收到模板消息',
			
			'checkin'		=> '回复签到',
			'credit'		=> '回复积分',
			'top-credits'	=> '积分排行榜',
	    	'top-checkin'	=> '签到排行榜',
			
			'dkf'			=> '转到多客服'
		);

		return apply_filters('weixin_response_types',$response_types);
	}

	public static function reply(){
		$args_num	= func_num_args();
		$args		= func_get_args();

		if($args_num == 2){
			$id		= $args[0];
			$data	= $args[1];
		}else{
			$id		= 0;
			$data	= $args[0];
		}

		$openid = $data['FromUserName'];
		if(!WEIXIN_Message::can_send($openid)){
			return new WP_Error('out_of_custom_message_time_limit', '48小时没有互动过，无法发送消息！');
		}

		$type		= $data['type'];
		$content	= $data['content'];
		$kf_account	= isset($data['kf_account'])?$data['kf_account']:'';

		$response = self::send($openid, $content, $type, $kf_account);

		if(is_wp_error($response)){
			return $response;
		}

		if(isset($id)){
			$message_data = [
				'MsgType'		=> 'manual',
				'FromUserName'	=> $openid,
				'CreateTime'	=> time(),
				'Content'		=> $content,
			];

			$insert_id	= self::insert($message_data);
			self::update($id, ['Response'=>$insert_id]);
		}

		if($kf_account){
			$response	= weixin()->create_customservice_kf_session($kf_account, $openid); 

			if(is_wp_error($response)){
				return $response;
			}
		}

		return true;
	}

	public static function dummy($id){
		return true;
	}

	public static function get_reply_fields(){
		$weixin_setting	= weixin_get_setting();

		$type	= 'text';

		$content	= '';

		$type_options 		= array('text'=>'文本', 'news'=>'素材图文', 'img'=>'文章图文','img2'=>'自定义图文',	'wxcard'=>'卡券');
		if(empty($weixin_setting['weixin_search']))	unset($type_options['img']);

		$content_descriptions	= WEIXIN_AdminReplySetting::get_descriptions();

		$fields = [
			'FromUserName'	=> ['title'=>'',	'type'=>'hidden'],
			'type'			=> ['title'=>'类型',	'type'=>'radio',	'value'=>$type,		'options'=> $type_options],
			'content'		=> ['title'=>'内容',	'type'=>'textarea',	'value'=>$content,	'rows'=>'8',	'description'=>$content_descriptions[$type]]
		];

		if(weixin_get_type() >= 3 && !empty($weixin_setting['weixin_dkf'])){
			$weixin_kf_list	= weixin()->get_customservice_kf_list();

			if(!is_wp_error($weixin_kf_list)){
				$weixin_kf_options	= [''=>' '];
				foreach ($weixin_kf_list as $weixin_kf_account) {
					$weixin_kf_options[$weixin_kf_account['kf_account']] = $weixin_kf_account['kf_nick'];
				}
				$fields['kf_account'] = ['title'=>'以客服账号回复',	'type'=>'select',	'options'=>$weixin_kf_options];
			}
		}

		return $fields;
	}

	public static function list_page(){
		$content_descriptions	= WEIXIN_AdminReplySetting::get_descriptions();
		?>
		<script type="text/javascript">
		jQuery(function($){
			var content_descriptions	= <?php echo wpjam_json_encode($content_descriptions);?>;

			$('body').on('change', '#tr_type input[type=radio]', function(){
				var selected = $('#tr_type input[type=radio]:checked').val();

				$('#tr_content span').html(content_descriptions[selected]);

				tb_position();
			});
		});
		</script> 
		<?php
	}

	public static function get_actions(){
		global $plugin_page, $current_tab;
		if($plugin_page == 'weixin-users' && $current_tab == 'masssend'){
			return [];
		}else{
			return [
				'reply'		=> ['title'=>'回复',	'page_title'=>'回复客服消息'],
				'delete'	=> ['title'=>'删除',	'direct'=>true,	'confirm'=>true,	'bulk'=>true],
				// 'dummy'		=> ['title'=>'测试',	'direct'=>true,	'confirm'=>true,	'bulk'=>true]
			];
		}
	}

	public static function get_fields($action_key='', $id=''){
		global $plugin_page, $current_tab;
		if($plugin_page == 'weixin-users' && $current_tab == 'masssend'){
			return [
				'MsgId'			=> ['title' => '群发ID',		'type' => 'text',	'show_admin_column' => true],
				'CreateTime'	=> ['title' => '时间',		'type' => 'text',	'show_admin_column' => true],
				'Status'		=> ['title' => '状态',		'type' => 'text',	'show_admin_column' => true],
				'TotalCount'	=> ['title' => '所有',		'type' => 'text',	'show_admin_column' => true],
				'FilterCount'	=> ['title' => '过滤之后',	'type' => 'text',	'show_admin_column' => true],
				'SentCount'		=> ['title' => '发送成功',	'type' => 'text',	'show_admin_column' => true],
				'SentRate'		=> ['title' => '成功率',		'type' => 'text',	'show_admin_column' => true],
				'ErrorCount'	=> ['title' => '发送失败',	'type' => 'text',	'show_admin_column' => true],
			];
		}else{
			if($action_key == 'reply'){
				return self::get_reply_fields();
			}else{
				return [
					'username'	=> ['title'=>'用户',	'type'=>'text',		'show_admin_column'=>true],
					// 'address'	=> ['title'=>'地址',	'type'=>'text',		'show_admin_column'=>true],
					'MsgType'	=> ['title'=>'类型',	'type'=>'select',	'show_admin_column'=>true,	'options'=>self::get_message_types()],
					'Content'	=> ['title'=>'内容',	'type'=>'text',		'show_admin_column'=>true],
					'Response'	=> ['title'=>'回复',	'type'=>'select',	'show_admin_column'=>true,	'options'=>self::get_response_types()],
					'CreateTime'=> ['title'=>'时间',	'type'=>'text',		'show_admin_column'=>true],
				];
			}
		}
	}
}