<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-reply-setting.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-message.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-user.php');
// include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-masssend.php');

add_action('wpjam_weixin_masssend_tabs', function($tabs){
	if(!empty($_GET['openid'])){
		// return [
		// 	'advanced'	=> '高级群发',
		// 	'custom'	=> '客服群发',
		// 	'template'	=> '模板消息',
		// ];
	}else{
		return [
			// 'advanced'	=> '高级群发',
			'history'	=> ['title'=>'历史记录',		'function'=>'list'],
			// 'cron'		=> ['title'=>'定时群发作业',	'function'=>'list'],
			// 'template'	=> '模板消息测试',
		];
	}
});

add_filter('wpjam_weixin_masssend_list_table', function (){
		return [
			'title'				=> '群发记录',
			'singular'			=> 'weixin-message',
			'plural'			=> 'weixin-messages',
			'primary_column'	=> 'MsgId',
			'primary_key'		=> 'MsgId',
			'model'				=> 'WEIXIN_AdminMessage',
			'actions'			=> [],
		];
});

function weixin_robot_masssend_advanced_page(){
	global $wpdb, $current_admin_url;
	
	$type		= $_GET['type']??'all';
	$msgtype	= isset($_GET['msgtype'])?$_GET['msgtype']:'mpnews';
	$msgtype	= ($msgtype == 'news')?'mpnews':$msgtype;
	$content	= isset($_GET['content'])?$_GET['content']:'';

	$form_fields	= array();
	$nonce_action	= 'weixin-masssend-advanced';

	if($type == 'ids'){	// 用户列表页面选择了一些用户来群发
		$ids	= isset($_GET['ids'])?$_GET['ids']:'';

		if(!$ids){
			wp_die('至少要选择一个用户来群发');
		}
	}else{
		$type_options 			= ['all'=>'全部', 'tag'=>'按照标签', 'preview'=>'预览'];
		$form_fields['type']	= ['title'=>'群发对象', 'type'=>'radio', 'value'=>'', 'options'=>$type_options];
		$form_fields['preview']	= ['title'=>'预览微信号', 'type'=>'text'];
		
		$weixin_user_tags	= weixin()->get_tags();
		
		if(!is_wp_error($weixin_user_tags)){
			$tag_options	= array_combine(array_keys($weixin_user_tags), array_column($weixin_user_tags, 'name'));
			$form_fields['tag']	= ['title'=>'选择标签',	'type'=>'select',	'value'=>'',	'options'=>$tag_options];
		}
	}

	$msgtype_options	= ['mpnews'=>'图文', 'image'=>'图片', 'voice'=>'语音', 'text'=>'文本', 'wxcard'=>'卡券'];

	$content_descriptions				= WEIXIN_AdminReplySetting::get_descriptions();
	$content_descriptions['articls']	= $content_descriptions['img'];
	$content_descriptions['mpnews']		= $content_descriptions['news'].'<br /><strong>如果公众号支持留言和原创功能，请到微信公众号后台群发，这里群发不支持留言和原创功能！</strong>';

	$form_fields['msgtype']	= ['title'=>'群发类型',	'type'=>'radio',	'value'=>$msgtype,	'options'=> $msgtype_options];
	$form_fields['content']	= ['title'=>'群发内容',	'type'=>'textarea',	'value'=>$content,	'class'=>'large-text code',	'description'=>$content_descriptions[$msgtype]];
	
	if($type != 'ids'){
		$form_fields['time']	= ['title'=>'发送时间',	'type'=>'datetime',	'value'=>date('Y-m-d H:i:s', current_time('timestamp')+3600),	'description'=>'如果要定时发送，请输入未来要发送的时间，留空立即发送！'];
	}

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		$data = wpjam_get_form_post($form_fields, $nonce_action, 'masssend_weixin');

		if($type != 'ids'){
			$type		= $data['type'];
			$tag		= $data['tag'];
			$time		= $data['time'];
			$towxname	= $data['preview'];

			$time		= strtotime($time.' +0800');
			
			if($time <= time()){
				$time	= '';
			}
		}

		$msgtype		= $data['msgtype']; 
		$content		= $data['content'];
		$send_content	= '';

		if($msgtype == 'text'){
			$send_content = addslashes_gpc($content);
		}elseif($msgtype == 'mpnews' || $msgtype == 'image' || $msgtype == 'voice'){
			$send_content = trim($content);
		}elseif($msgtype == 'wxcard'){
			$send_content = ['card_id'=>trim($content)];
		}

		if($send_content){
			if($type == 'all'){
				if($time){
					wp_schedule_single_event($time,'weixin_send_future_mass_message',['all', $msgtype, $send_content]);
					wpjam_admin_add_error('定时群发作业已经被设置！<a href="'.admin_url('admin.php?page=weixin-robot-masssend&tab=cron').'">点击查看</a>');
				}else{
					$response = weixin()->sendall_mass_message('all', $msgtype, $send_content);
					if(is_wp_error($response)){
						wpjam_admin_add_error($response->get_error_code().'：'. $response->get_error_message(), 'error');
					}else{
						wpjam_admin_add_error('群发成功');
						wpjam_admin_add_error('群发类型：全部');
					}
				}
			}elseif($type == 'tag'){
				if($time){
					wp_schedule_single_event($time,'weixin_send_future_mass_message', [$tag, $msgtype, $send_content]);
					wpjam_admin_add_error('定时群发作业已经被设置！<a href="'.admin_url('admin.php?page=weixin-robot-masssend&tab=cron').'">点击查看</a>！');
				}else{
					$response = weixin()->sendall_mass_message($tag, $msgtype, $send_content);
					if(is_wp_error($response)){
						wpjam_admin_add_error($response->get_error_code().'：'. $response->get_error_message(), 'error');
					}else{
						wpjam_admin_add_error('群发成功');
						wpjam_admin_add_error('群发类型：按分组群发');
						wpjam_admin_add_error('群发分组：'.$tag_options[$tag]);
					}
				}
			}elseif($type == 'ids'){
				$openids	= $ids;  
				$response = weixin()->send_mass_message($openids, $msgtype, $send_content);
				if(is_wp_error($response)){
					wpjam_admin_add_error($response->get_error_code().'：'. $response->get_error_message(), 'error');
				}else{
					wpjam_admin_add_error('群发成功');
					wpjam_admin_add_error('群发类型：按照 IDs');
				}
			}elseif($type == 'preview'){
				$response = weixin()->preview_mass_message($towxname, $msgtype, $send_content);
				if(is_wp_error($response)){
					wpjam_admin_add_error($response->get_error_code().'：'. $response->get_error_message(), 'error');
				}else{
					wpjam_admin_add_error('预览发送成功');
				}
			}
			wpjam_admin_add_error('发送类型：'.$msgtype_options[$msgtype]);
			wpjam_admin_add_error('群发内容：'.'<code>'.$content.'</code>');
		}

		foreach ($form_fields as $key => $form_field) {
			$form_fields[$key]['value']	= $data[$key];
		}

		$form_fields['content']['description']	= $content_descriptions[$msgtype];
	}
	
	$ids_str = '';
	if($type == 'ids'){
		echo '<h2>群发以下用户</h2>';

		echo '<p><strong>群发以下用户</strong>：</p><p>';
		foreach ($ids as $openid) {
			$ids_str		.= '&ids[]='.$openid;
			$weixin_user	= WEIXIN_User::get($openid);
			echo '<img src="'.$weixin_user['headimgurl'].'" alt="'.$weixin_user['nickname'].'" width="32" />';
		}
		echo '</p>';
		$form_url	= $current_admin_url.'&type=ids'.$ids_str;
	}else{
		echo '<h2>高级群发</h2>';
		$form_url	= $current_admin_url;
	}
	?>

	<?php wpjam_form($form_fields, $form_url, $nonce_action, '群发消息'); ?>

	<script type="text/javascript">
	jQuery(function($){
		var content_descriptions	= $.parseJSON('<?php echo wpjam_json_encode($content_descriptions);?>');

		$('#tr_msgtype input[type=radio]').change(function(){
			var selected = $('#tr_msgtype input[type=radio]:checked').val();
			$('#tr_content p').html(content_descriptions[selected]);
		});
		<?php if($type == 'all'){ ?>
		$('#tr_tag').hide();
		$('#tr_preview').hide();
		<?php }elseif($type == 'tag'){ ?>
		$('#tr_preview').hide();
		$('#tr_tag').show();
		<?php }elseif($type == 'preview') { ?>
		$('#tr_tag').hide();
		$('#tr_preview').show();
		$('#tr_time').hide();
		<?php } ?>
		$('#tr_type input[type=radio]').change(function(){
			var selected = $('#tr_type input[type=radio]:checked').val();
			if(selected == 'all'){
				$('#tr_tag').hide();
				$('#tr_time').show();
				$('#tr_preview').hide();
			}else if(selected == 'tag'){
				$('#tr_tag').show();
				$('#tr_time').show();
				$('#tr_preview').hide();
			}else if(selected == 'preview'){
				$('#tr_tag').hide();
				$('#tr_time').hide();
				$('#tr_preview').show();
			}
		});
	});
	</script> 
	<?php
}

function weixin_robot_masssend_custom_page(){
	global $wpdb,  $current_admin_url;

	$weixin_setting	= weixin_get_setting();

	if(empty($_GET['openid'])){
		$openids	= WEIXIN_Message::get_can_send_users();
		$capability		= 'masssend_weixin';
	}else{
		$openid = $_GET['openid'];
		if(!WEIXIN_Message::can_send($openid)){
			wp_die('48小时没有互动过，无法发送消息！');
		}
		$capability		= 'edit_weixin';
	}

	$type	= 'text';

	$content	= '';

	$type_options 		= array('text'=>'文本', 'news'=>'素材图文', 'img'=>'文章图文','img2'=>'自定义图文',	'wxcard'=>'卡券');
	if(empty($weixin_setting['weixin_search']))	unset($type_options['img']);

	$content_descriptions	= WEIXIN_AdminReplySetting::get_descriptions();

	$form_fields	= array(
		'type'		=> array('title'=>'类型',	'type'=>'radio',	'value'=>$type,		'options'=> $type_options ),
		'content'	=> array('title'=>'内容',	'type'=>'textarea',	'value'=>$content,	'rows'=>'8',	'class'=>'large-text code',	'description'=>$content_descriptions[$type])
	);

	if(!empty($weixin_setting['weixin_dkf'])){
		$weixin_kf_list	= weixin()->get_customservice_kf_list();

		if(!is_wp_error($weixin_kf_list)){
			$weixin_kf_options	= array(''=>' ');
			foreach ($weixin_kf_list as $weixin_kf_account) {
				$weixin_kf_options[$weixin_kf_account['kf_account']] = $weixin_kf_account['kf_nick'];
			}
			$form_fields['kf_account'] = array('title'=>'以客服账号回复',	'type'=>'select',	'options'=>$weixin_kf_options);
		}
	}

	$nonce_action = 'weixin-masssend-custom';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data = wpjam_get_form_post($form_fields, $nonce_action, $capability);

		$type		= $data['type'];
		$content	= $data['content'];
		$kf_account	= isset($data['kf_account'])?$data['kf_account']:'';

		if(empty($_GET['openid'])){
			foreach ($openids as $openid) {
				if($content){	
					$response = WEIXIN_Message::send($openid, $content, $type);
					if(is_wp_error($response)){
						wpjam_admin_add_error($openid.' '.$response->get_error_code().'：'. $response->get_error_message(), 'error');
					}
				}	
			}
		}else{
			if(isset($_GET['reply_id'])){
				$message_data = array(
					'MsgType'		=> 'manual',
					'FromUserName'	=> $openid,
					'CreateTime'	=> current_time('timestamp',true),
					'Content'		=> $content,
				);

				WEIXIN_Message::insert($message_data);
				WEIXIN_Message::update($_GET['reply_id'], array('Response'=>$wpdb->insert_id));
			}

			$response = WEIXIN_Message::send($openid, $content, $type, $kf_account);

			if(is_wp_error($response)){
				wpjam_admin_add_error($openid.' '.$response->get_error_code().'：'. $response->get_error_message(), 'error');
			}

			if($kf_account){
				$response	= weixinc()->create_customservice_kf_session($kf_account, $openid); 

				if(is_wp_error($response)){
					wpjam_admin_add_error($openid.' '.$response->get_error_code().'：'. $response->get_error_message(), 'error');
				}
			}
		}

		if(!is_wp_error($response)){
			wpjam_admin_add_error('发送成功');
		}

		foreach ($form_fields as $key => $form_field) {
			$form_fields[$key]['value']	= $data[$key];
		}
		$form_fields['content']['description']	= $content_descriptions[$type];
	}

	$form_url = $current_admin_url;

	if(empty($_GET['openid'])){
		echo '<h2>使用客服接口群发</h2>';
		echo '<p>使用客服接口进行群发可能会违反微信公众号规定而被封号，请注意使用！</p>';
	}else{
		echo '<h2>发送消息</h2>';
		$form_url	=$form_url.'&openid='.$_GET['openid'];
		if(!empty($_GET['reply_id'])){
			$form_url	=$form_url.'&reply_id='.$_GET['reply_id'];
		}

		$weixin_user	= WEIXIN_AdminUser::parse_user(WEIXIN_User::get($openid));
		echo '<p style="height:32px; line-height:32px;">'.
		'<img style="float:left; margin-right:10px;" src="'.$weixin_user['headimgurl'].'" alt="'.$weixin_user['nickname'].'" width="32" aligin="left" /> '.
		$weixin_user['nickname'].'（'.$weixin_user['sex'].'）'.
		'</p>';
	}
	?>

	<?php wpjam_form($form_fields, $form_url, $nonce_action, '发送信息'); ?>

	<?php if(empty($_GET['openid'])){ ?><p>* 消息将群发到<?php echo count($openids); ?>用户</p><?php } ?>

	<script type="text/javascript">
	jQuery(function($){
		var content_descriptions	= $.parseJSON('<?php echo json_encode($content_descriptions);?>');

		$('#tr_type input[type=radio]').change(function(){
			var selected = $('#tr_type input[type=radio]:checked').val();
			$('#tr_content p').html(content_descriptions[selected]);
		});
	});
	</script> 
	<?php
}

function weixin_robot_masssend_template_page(){
	global $wpdb,  $current_admin_url;

	$content	= 'first
	loeny提交了新的工单
	#0A0A0A

	keyword1
	08250603
	#FF0000

	keyword2
	XXX公司
	#00FF00

	keyword3
	需重启服务器
	#0000FF

	remark
	请重启10.10.10.10服务器。
	#00FFFF';

	$form_fields 	= array(
		'touser'		=> array('title'=>'发送的用户',	'type'=>'text' ),
		'template_id'	=> array('title'=>'模板ID',		'type'=>'text' ),
		'url'			=> array('title'=>'链接',		'type'=>'url' ),
		'topcolor'		=> array('title'=>'顶部颜色',		'type'=>'color',	'value'=>'#FF0000'),
		'content'		=> array('title'=>'内容',		'type'=>'textarea', 'value'=>$content,	'rows'=>'12',	'class'=>'large-text code')
	);

	$nonce_action = 'weixin-masssend-template';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data = wpjam_get_form_post($form_fields, $nonce_action, 'masssend_weixin');

		$touser			= $data['touser'];
		$template_id	= $data['template_id'];
		$url			= $data['url'];
		$topcolor		= $data['topcolor'];
		$content		= $data['content'];

		$send_data = array();

		$items = explode("\n\n", str_replace("\r\n", "\n", $content));
		foreach ($items as $item ) {
			$lines = explode("\n", $item);
			$send_data[$lines[0]]['value']	= urlencode($lines[1]);
			$send_data[$lines[0]]['color']	= urlencode($lines[2]);
		}

		$response = weixin()->send_template_message($touser, $template_id, $send_data, $url, $topcolor);

		if(is_wp_error($response)){
			wpjam_admin_add_error($response->get_error_code().'：'. $response->get_error_message(), 'error');
		}else{
			wpjam_admin_add_error('发送成功');
		}

		foreach ($form_fields as $key => $form_field) {
			$form_fields[$key]['value']	= $data[$key];
		}
	}
	?>

	<h2>模板接口测试</h2>

	<p>这个界面用于测试模板消息接口，模板消息接口真正实现需要通过程序实现。</p>

	<?php wpjam_form($form_fields, $current_admin_url, $nonce_action, '发送信息'); ?>

	<?php
}