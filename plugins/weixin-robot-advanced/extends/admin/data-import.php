<?php

add_filter('wpjam_weixin_robot_extend_tabs', function($tabs){
	$tabs['data-import']	= array('title'=>'数据导入', 'function'=>'weixin_robot_data_import_page'); 
	return $tabs;
});


function weixin_robot_data_import_page(){
	global $current_admin_url, $wpdb;

	$import_types = array(
		// 'setting'			=> '其他站点插件设置',
		// 'custom_replies'	=> '其他站点自定义回复',
		'mp_custom_replies'	=> '微信后台自定义回复',
		'mp_custom_menus'	=> '微信后台自定义菜单',
	);

	// if(WEIXIN_TYPE == 4){
	// 	$import_types['qrcodes'] = '其他站点带参数二维码';
	// }

	$form_fields = array(
		'type'	=> array('title'=>'导入的数据',	'type'=>'select',	'options'=>$import_types),
		'url'	=> array('title'=>'原数据地址',	'type'=>'url',		'description'=>'注意要输入完整的地址，如果是微信官方的数据则不用输入。'),
	);

	$nonce_action	= 'data-import';
	$blog_id		= get_current_blog_id();

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		if(!current_user_can( 'manage_sites' )){
			wpjam_admin_add_error('只有超级管理员才能进行数据导入操作', error);
		}else{
			$data = wpjam_get_form_post($form_fields, $nonce_action);

			$import_type	= $data['type'];
			$import_url		= $data['url'];

			if($import_type == 'mp_custom_replies'){
				$response =  weixin()->get_current_autoreply_info();
			}elseif ($import_type == 'mp_custom_menus'){
				$response =  weixin()->get_current_selfmenu_info();
			}else{
				$response =  wpjam_remote_request($import_url);
			}

			// wpjam_print_r($response);
			// exit;
			
			if(is_wp_error($response)){
				wpjam_admin_add_error($response->get_error_code().'：'. $response->get_error_message(), 'error');
			}elseif($response){
				if($import_type == 'setting'){
					update_option('weixin-robot', $response['setting']);
					wpjam_admin_add_error('其他站点微信设置数据导入成功');
				}elseif($import_type == 'custom_replies'){
					$weixin_custom_replies = $response['custom_replies'];
					foreach ($weixin_custom_replies as $weixin_custom_reply) {
						WEIXIN_Reply::insert($weixin_custom_reply);
					}
					wpjam_admin_add_error('其他站点自定义回复数据导入成功');
				}elseif($import_type == 'qrcodes'){
					// $weixin_qrcodes = $response['qrcodes'];
					// foreach ($weixin_qrcodes as $weixin_qrcode) {
					// 	weixin_robot_create_qrcode($weixin_qrcode);
					// }
					// wpjam_admin_add_error('其他站点带参数二维码数据导入成功');
				}elseif ($import_type == 'mp_custom_replies') {

					// if($response['is_autoreply_open']){

						if($response['keyword_autoreply_info']){
							$keyword_autoreply_list = $response['keyword_autoreply_info']['list'];
							krsort($keyword_autoreply_list);

							foreach ($keyword_autoreply_list as $keyword_autoreply) {
								$keyword_list	= $keyword_autoreply['keyword_list_info'];
								$reply_list 	= $keyword_autoreply['reply_list_info'];

								$keyword_contain	= $keyword_equal = array();

								foreach ($keyword_list as $keyword) {
									if($keyword['type'] == 'text'){
										if($keyword['match_mode'] == 'equal'){
											$keyword_equal[]	= $keyword['content'];
										}elseif ($keyword['match_mode'] == 'contain') {
											$keyword_contain[]	= $keyword['content'];
										}
									}
								}

								if($keyword_contain){
									$keyword_contain = implode(',', $keyword_contain);
								}

								if($keyword_equal){
									$keyword_equal = implode(',', $keyword_equal);
								}


								foreach ($reply_list as $reply) {
									$reply_type = $reply['type'];

									if($reply_type == 'img'){
										$reply_type = 'image';
									// }elseif($reply_type == 'news'){
									// 	$reply_type = 'img2';
									}elseif ($reply_type == 'video') {
										// 视频的暂时不能处理
										continue;
									}

									$data = array(
										'type'		=> $reply_type,
										'reply' 	=> $reply['content'],
										'status'	=> 1
									);

									if($keyword_contain){
										$data['keyword'] 	= $keyword_contain;
										$data['match']		= 'fuzzy';

										// wpjam_print_r($data);
										WEIXIN_Reply::insert($data);
									}

									if($keyword_equal){
										$data['keyword'] 	= $keyword_equal;
										$data['match']		= 'full';

										// wpjam_print_r($data);
										WEIXIN_Reply::insert($data);
									}
								}
							}
						}

						if($message_default_autoreply_info = $response['message_default_autoreply_info']){
							$message_default_autoreply_info['type'] = ($message_default_autoreply_info['type'] == 'img')?'image':$message_default_autoreply_info['type'];

							$data = array(
								'keyword'	=> '[default]',
								'match'		=> 'full',
								'type'		=> $message_default_autoreply_info['type'],
								'reply'		=> $message_default_autoreply_info['content'],
								'status'	=> 1
							);

							// wpjam_print_r($data);
							WEIXIN_Reply::insert($data);
						}
					// }

					if($response['is_add_friend_reply_open']){
						$add_friend_autoreply_info = $response['add_friend_autoreply_info'];
						$add_friend_autoreply_info['type'] = ($add_friend_autoreply_info['type'] == 'img')?'image':$add_friend_autoreply_info['type'];

						$data = array(
							'keyword'	=> '[subscribe]',
							'match'		=> 'full',
							'type'		=> $add_friend_autoreply_info['type'],
							'reply'		=> $add_friend_autoreply_info['content'],
							'status'	=> 1
						);

						// wpjam_print_r($data);
						WEIXIN_Reply::insert($data);
					}

					// wpjam_print_r($response);

					wpjam_admin_add_error('微信后台自定义回复导入成功');
				}elseif ($import_type == 'mp_custom_menus') {
					// if($response['is_menu_open']){

					$weixin_buttons = array();
					$i = 0;

					foreach ($response['selfmenu_info']['button'] as $position => $button) {
						$weixin_button = array('name'	=> $button['name']);

						if(empty($button['sub_button'])){
							$weixin_button['key'] = $button['name'];

							$button_type = $button['type'];
							if($button_type == 'text'){
								$weixin_button['type']	= 'click';
								$data = array(
									'keyword'	=> $button['name'],
									'match'		=> 'full',
									'type'		=> 'text',
									'reply'		=> $button['value'],
									'status'	=> 1
								);
								WEIXIN_Reply::insert($data);
							}elseif($button_type == 'news') {
								$weixin_button['type']	= 'click';
								$data = array(
									'keyword'	=> $button['name'],
									'match'		=> 'full',
									'type'		=> 'news',
									'reply'		=> $button['value'],
									'status'	=> 1
								);
								WEIXIN_Reply::insert($data);
							}elseif ($button_type == 'img' || $button_type == 'photo' || $button_type == 'voice') {
								$weixin_button['type']	= 'media_id';
								$weixin_button['media_id']	= $button['value'];
							}elseif($button_type == 'view'){
								$weixin_button['type']	= $button_type;
								$weixin_button['url']	= $button['url'];
							}elseif($button_type == 'media_id' || $button_type == 'view_limited' ){
								$weixin_button['type']		= $button_type;
								$weixin_button[$button_type]= $button['media_id'];
							}elseif ($button_type == 'video') {
								// 暂时不处理，以后再想办法
							}else{
								$weixin_button['type']	= $button_type;
								$weixin_button['key']	= $button['key'];
							}
						}else{
							$weixin_sub_button = array();
							foreach ($button['sub_button']['list'] as $sub_position => $sub_button) {
								$weixin_sub_button = array(
									'name'			=> $sub_button['name'],
									'key'			=> $sub_button['name'],
									'type'			=> 'click',
								);

								$sub_button_type = $sub_button['type'];
								if($sub_button_type == 'text'){
									$data = array(
										'keyword'	=> $sub_button['name'],
										'match'		=> 'full',
										'type'		=> 'text',
										'reply'		=> $sub_button['value'],
										'status'	=> 1
									);
									WEIXIN_Reply::insert($data);
								}elseif($sub_button_type == 'news') {
									$weixin_sub_button['type']	= 'click';
									$data = array(
										'keyword'	=> $sub_button['name'],
										'match'		=> 'full',
										'type'		=> 'news',
										'reply'		=> $sub_button['value'],
										'status'	=> 1
									);
									WEIXIN_Reply::insert($data);
								}elseif ($sub_button_type == 'img' || $sub_button_type == 'photo' || $sub_button_type == 'voice') {
									$weixin_sub_button['type']	= 'media_id';
									$weixin_sub_button['key']	= $sub_button['value'];
								}elseif($sub_button_type == 'view'){
									$weixin_sub_button['type']	= $sub_button_type;
									$weixin_sub_button['url']	= $sub_button['url'];
								}elseif($sub_button_type == 'media_id' || $sub_button_type == 'view_limited' ){
									$weixin_sub_button['type']				= $sub_button_type;
									$weixin_sub_button[$sub_button_type]	= $button['media_id'];
								}elseif ($sub_button_type == 'video') {
									// 暂时不处理，以后再想办法
								}else{
									$weixin_sub_button['type']	= $sub_button_type;
									$weixin_sub_button['key']	= $sub_button['key'];
								}

								$weixin_button['sub_button'][] =  $weixin_sub_button;
							}
						}

						$weixin_buttons[]	= $weixin_button;
					}

					if($menu = WEIXIN_Menu::get()){
						WEIXIN_Menu::update($menu['id'], array('button'=>$weixin_buttons,'type'=>'menu'));
					}elseif($weixin_button){
						WEIXIN_Menu::insert(array('button'=>$weixin_buttons,'type'=>'menu'));
					}

					wpjam_admin_add_error('微信后台自定义菜单导入成功');
				}
			}
		}
	}
	?>

	<h2>数据导入</h2>

	<?php wpjam_form($form_fields, $current_admin_url, $nonce_action, '数据导入'); ?>
	<?php
}

