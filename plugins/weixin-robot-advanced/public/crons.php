<?php
add_action('weixin_delete_messages', function(){
	if(!class_exists('WEIXIN_Message')){
		include_once(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-message.php');
	}

	WEIXIN_Message::Query()->where_lt('CreateTime', (time()-MONTH_IN_SECONDS))->delete();
});

// 从微信服务器获取关注用户列表
add_action('weixin_get_user_list', function($next_openid=''){
	if(weixin_get_type() < 3 ) return;

	if($next_openid == ''){
		WEIXIN_User::Query()->update(array('subscribe'=>0));	// 第一次抓取将所有的用户设置为未订阅
	}

	$response = weixin()->get_user_list($next_openid);

	if(is_wp_error($response)){
		if($response->get_error_code() != '45009'){
			wp_schedule_single_event(time()+60,'weixin_get_user_list',array($next_openid));	// 失败了，就1分钟后再来一次	
		}
		return $response;
	}

	$next_openid	= $response['next_openid'];
	$count			= $response['count'];

	if($next_openid && $count > 0){
		wp_schedule_single_event(time()+10,'weixin_get_user_list',array($next_openid));
	}else{
		wp_schedule_single_event(time()+5,'weixin_get_users');
	}

	if($count){
		$datas	= array_map(function($openid){
			return array('openid'=>$openid, 'subscribe'=>1);
		}, $response['data']['openid']);

		WEIXIN_User::insert_multi($datas);
	}

	if(!is_admin()){
		exit;
	}
},10,1);


add_action('weixin_get_users', function($i=0){
	if(weixin_get_type() < 3 ) return;

	$openids	= WEIXIN_User::Query()->where('subscribe',1)->where_lt('last_update', time()-MONTH_IN_SECONDS*6)->limit(100)->get_col('openid');

	if($openids){
		if(count($openids) > 90){	// 如果有大量的用户，就再抓一次咯
			$i++;
			wp_schedule_single_event(time()+10,'weixin_get_users',array($i));
		}else{
			update_option('weixin_'.weixin_get_appid().'_users_sync', time());
		}

		$result = WEIXIN_User::batch_get_user_info($openids, true);
		if(is_wp_error($result) && $result->get_error_code() == '40003'){	// 突然有用户取消关注
			exit;
		}
	}else{
		update_option('weixin_'.weixin_get_appid().'_users_sync', time());
		
		WPJAM_Notice::add(array(
			'type'		=> 'success',
			'notice'	=> '用户信息同步成功！',
		));
	}

	if(!is_admin()){
		exit;
	}
},10,1);


add_action('weixin_send_future_mass_message', function($tag_id, $msgtype='text', $content='', $send_ignore_reprint=1){
	$response = weixin()->sendall_mass_message($tag_id, $msgtype, $content);

	$msgtype_options	= array( 'mpnews'=>'图文',	 'image'=>'图片', 'voice'=>'语音', 'text'=>'文本' );
	

	$message = '<br />群发对象：';
	if($tag_id == 'all'){
		$message			.= '所有用户';  
	}else{
		$weixin_user_tags	= weixin()->get_tags();
		$message			.= ''.$weixin_user_tags[$tag_id]['name'];
	}

	$message	.= '<br />群发时间：'.date('Y-m-d H:i:s', current_time('timestamp'));
	$message	.= '<br />发送类型：'.$msgtype_options[$msgtype];
	$message	.= '<br />群发内容：'.'<code>'.$content.'</code>';

	if(is_wp_error($response)){
		$admin_notice = array(
			'type'		=> 'error',
			'notice'	=> '定时群发失败：'.$response->get_error_code().':'.$response->get_error_message().'！<br />'.$message.'<br />',
		);
	}else{
		$admin_notice = array(
			'type'		=> 'info',
			'notice'	=> '定时群发成功！'.$message.'<br />',
		);
	}

	WPJAM_Notice::add($admin_notice);

	if(!is_admin()){
		exit;
	}
}, 10, 4);


