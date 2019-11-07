<?php 
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/pages/weixin-messages.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-user-tag.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-qrcode.php');

function wpjam_weixin_user_page(){
	global $plugin_page, $current_admin_url, $current_tab;

	$openid	= ($_GET['openid'])??'';
	$action = ($_GET['action'])??''; 

	if(!$openid) return;

	if($current_tab == 'detail' && $action == 'update'){
		$user	= WEIXIN_AdminUser::get($openid, array('force'=>true));
	}else{
		$user	= WEIXIN_AdminUser::get($openid);
	}
	
	if(!$user) return;

	?>
	<div class="wrap">
	<h1>
		<?php if($user['headimgurl']){ ?><img src="<?php echo $user['headimgurl'];?>" style="float: left; padding: 2px 4px; width: 24px;" /> <?php } ?><?php echo $user['nickname']; ?>
		<?php if($user['subscribe'] && $current_tab == 'detail'){ ?>
			<a href="<?php echo $current_admin_url.'&openid='.$openid.'&action=update'; ?>" class="button">更新</a> 
		<?php }?>
	</h1>
	</div>

	<?php

	return compact('openid');
}

// 用户详细信息后台页面
function weixin_user_detail_page(){
	global $current_admin_url;

	$openid			= $_GET['openid'];
	$nonce_action	= 'weixin_user'; 

	$user		= WEIXIN_AdminUser::get($openid);
	$user		= WEIXIN_AdminUser::parse_user($user);
	$user_tags 	= weixin()->get_tags();

	$tag_options		= array_combine(array_keys($user_tags), array_column($user_tags, 'name'));
	$current_tags		= ($user['tagid_list'])?array_keys($user['tagid_list']):array();

	$form_fields = array(
		'tags'		=> array('title'=>'标签',	'type'=>'checkbox',	'value'=>$current_tags,	'options'=> $tag_options),
		'remark'	=>array('title'=>'备注',		'type'=>'textarea',	'value'=>$user['remark'],'rows'=>3, 'description'=>'长度必须小于30字符')
	);
	
	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		$data		= wpjam_get_form_post($form_fields, $nonce_action);

		$new_tags	= (array)$data['tags'];

		$untagging_tags	= array_diff($current_tags, $new_tags);
		$tagging_tags 	= array_diff($new_tags, $current_tags);

		$openid_list	= array($openid);

		if($untagging_tags){
			foreach ($untagging_tags as $tagid) {
				$result = weixin()->batch_untagging($openid_list, $tagid);
				if(is_wp_error($result)){
					wpjam_admin_add_error($result); 
				}else{
					// WEIXIN_AdminUser::batch_get_user_info($openid_list, true);
				}
			}
		}

		if($tagging_tags){
			foreach ($tagging_tags as $tagid) {
				$result = weixin()->batch_tagging($openid_list, $tagid);
				if(is_wp_error($result)){
					wpjam_admin_add_error($result);
				}else{
					// WEIXIN_AdminUser::batch_get_user_info($openid_list, true);
				}
			}
		}

		if(!empty($data['remark'])){
			$result = weixin()->update_user_remark($openid, $data['remark']);
			if(is_wp_error($result)){
				wpjam_admin_add_error($result);
			}	
		}

		WEIXIN_AdminUser::get($openid, array('force'=>true));

		foreach ($form_fields as $key=>$form_field) {
			$form_fields[$key]['value']	= $data[$key];
		}

		wpjam_admin_add_error('修改成功');
	}

	if($user){ 
	?>
		<div class="user-profile" style="max-width:780px">
			<?php if($user['headimgurl']){?><a href="<?php echo str_replace('/64', '/0', $user['headimgurl']);?>" target="_blank"><img src="<?php echo str_replace('/64', '/132',$user['headimgurl']);?>" style="width:80px; height:80px; float:right; margin:0 0 10px 10px" /></a><?php } ?>
			<style type="text/css">
			td, th {padding: 4px 8px;} 
			td{font-weight: bold;}
			input.regular-text{width:15em; padding:4px;}
			th strong {padding:10px 0; font-size:16px; display: block;}
			</style>
			
			<script type="text/javascript">
			jQuery(function($){
				$(".form-table input:checkbox").click(function(){
					if($("input:checkbox:checked").length>20){ 
						alert('一个用户最多只能设置20个标签！'); 
						return false; //令刚才勾选的取消 
					}
				})
			});
			</script>
			
			<h2>详细资料</h2>
			<table class="user-profile" class="widefat" cellspacing="0">
				<tbody>
					<?php do_action('weixin_user_show_detail', $openid); ?>
					<tr> <th style="width:100px;">OPENID</th><td><?php echo $openid;?></td> </tr>
					<tr> <th>昵称</th>	<td><?php echo $user['nickname'];?></td> </tr>
					<tr> <th>性别</th>	<td><?php echo $user['sex'];?></td> </tr>
					<tr> <th>订阅</th>	<td><?php echo $user['subscribe_time']; ?></td> </tr>
					<tr> <th>地址</th>	<td><?php echo $user['address'];?></td> </tr>
				</tbody>
			</table>

			<div style="clear:both"></div>
			<hr />
			
			<?php

			$form_url = add_query_arg(compact('openid'), $current_admin_url);
		
			wpjam_form($form_fields, $form_url, $nonce_action, '保存');
			?>
		</div>
	<?php
	}
}

add_filter('wpjam_weixin_user_list_table', function(){
	global $current_tab;

	if($current_tab == 'subscribe'){
		return [
			'title'				=> '订阅历史',
			'singular'			=> 'weixin-message',
			'plural'			=> 'weixin-messages',
			'primary_column'	=> 'username',
			'primary_key'		=> 'id',
			'model'				=> 'WEIXIN_AdminMessage',
			'actions'			=> [],
			'fields'			=> [
				'Event'			=> ['title'=>'动作',		'type'=>'text',	'show_admin_column'=>true],
				'EventKey'		=> ['title'=>'渠道',		'type'=>'text',	'show_admin_column'=>true],
				'CreateTime'	=> ['title'=>'时间',		'type'=>'text',	'show_admin_column'=>true],
			]
		];
	}
});