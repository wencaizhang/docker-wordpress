<?php
include(WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-topic.php');

add_filter('wpjam_topics_list_table', function(){
	return array(
		'title'		=> '讨论组',
		'plural'	=> 'wpjam_topics',
		'singular' 	=> 'wpjam_topic',
		'fixed'		=> false,
		'ajax'		=> true,
		'search'	=> true,
		'model'		=> 'WPJAM_AdminTopic',
		'style'		=> '
			th.column-username{width:160px;}
			th.column-time{width:80px;}
			th.column-group{width:100px;}
			th.column-last_reply{width:150px;}
			th.column-actions{width:16%;}

			#tr_topic td, #tr_topic_replies td, #tr_topic_reply td{padding-top:0px !important;}
			#tr_topic_reply td{padding-bottom:0px !important;}
			.topic-avatar{ float:left; margin:1em 1em 0 0; }
			.topic-content pre, .reply-content pre{ background: #eaeaea; background: rgba(0,0,0,.07); white-space: pre-wrap; word-wrap: break-word; padding:8px; }
			.topic-content code, .reply-content code{ background: none; }
			.topic-content img{max-width: 640px; }
			.topic-meta{margin: 1em 0 2em; }
			.wrap h1 a, .reply-meta a{text-decoration:none;}
			ul.replies li { padding:1px 1em; margin:1em 0; background: #fff;}
			ul.replies li.alternate{background: #f9f9f9;}
			.reply-meta, .reply-content{margin: 1em 0;}
			.reply-meta .dashicons{width:18px; height:18px; font-size:14px; line-height: 18px;}
			.reply-avatar { float:left; margin:1em 1em 0 0; }
		'	
	);
});

add_filter('wpjam_topic_ajax_form_args', function($args, $list_action, $data){
	if($list_action == 'reply' && $data['status']){
		$args['submit_text']	= '';
	}

	return $args;
}, 10, 3);


function wpjam_topics_ajax_response(){
	$action			= $_POST['page_action'];
	$action_type	= $_POST['page_action_type'];

	$data	= wp_parse_args($_POST['data']);

	if($action == 'delete_weixin_user'){
		delete_user_meta(get_current_user_id(), 'wpjam_weixin_user');
		wpjam_send_json(['errcode'=>0]);
	}elseif($action == 'profile'){

		$weixin_user	= WPJAM_Verify::update_weixin_user_profile($data);

		if(is_wp_error($weixin_user)){
			wpjam_send_json($weixin_user);
		}

		wpjam_send_json(['errcode'=>0]);
	}elseif($action == 'topic_reply'){
		$topic_id	= $data['topic_id'];

		if($action_type == 'form'){
			$fields	= WPJAM_AdminTopic::get_fields('reply', $data['topic_id']);

			ob_start();

			$fields['topic_id']	= ['title'=>'',	'type'=>'hidden',	'value'=>$topic_id];

			wpjam_ajax_form([
				'fields'		=> $fields, 
				'data'			=> $data, 
				'action'		=> 'topic_reply', 
				'submit_text'	=> '回复'
			]);
			
			$response   = ob_get_clean();

		}elseif($action_type == 'submit'){
			$response	= WPJAM_AdminTopic::reply($topic_id, $data);

			if(is_wp_error($response)){
				wpjam_send_json($response);
			}

			$fields	= WPJAM_AdminTopic::get_fields('reply', $data['topic_id']);

			ob_start();

			$fields['topic_id']	= ['title'=>'',	'type'=>'hidden',	'value'=>$topic_id];

			wpjam_ajax_form([
				'fields'		=> $fields, 
				'data'			=> $data, 
				'action'		=> 'topic_reply', 
				'submit_text'	=> '回复'
			]);
			
			$response   = ob_get_clean();
		}

		wpjam_send_json(['errcode'=>0, 'data'=>$response]);
	}
}

function wpjam_topic_user_profile_page(){
	echo '<h2>个人资料 '.wpjam_get_ajax_button(['action'=>'delete_weixin_user','button_text'=>'切换账号','class'=>'page-title-action','direct'=>true,'confirm'=>true]).'</h2>';

	$wpjam_weixin_user	= WPJAM_Verify::get_weixin_user();

	$fields = [
		'openid'		=> ['title'=>'微信昵称',		'type'=>'view',	'value'=>$wpjam_weixin_user['nickname']],
		// 'qq'			=> ['title'=>'QQ号',		'type'=>'number'],
		'site'			=> ['title'=>'个人站点',		'type'=>'mu-text',	'value'=>maybe_unserialize(wp_unslash($wpjam_weixin_user['site']??''))],
		'description'	=> ['title'=>'个人说明',		'type'=>'textarea', 'value'=>$wpjam_weixin_user['description']??'',	'class'=>'regular-text',	'rows'=>8],
	];


	wpjam_ajax_form([
		'fields'		=> $fields, 
		'action'		=> 'profile', 
		'submit_text'	=> '修改'
	]);

	?>
	<script type="text/javascript">
	jQuery(function ($) {
		$('body').on('page_action_success', function(e, response){
			var action		= response.page_action;
			var action_type	= response.page_action_type;

			if(action == 'delete_weixin_user'){
				window.location.href='<?php echo admin_url('admin.php?page=wpjam-extends');?>';
			}
		});
	});
	</script>
	<?php
}

function wpjam_topic_user_messages_page(){

	echo '<h2>消息提醒</h2>';
	echo '<p>每15分钟更新刷新！</p>';

	$topic_messages = wpjam_get_topic_messages();

	$unread_count	= $topic_messages['unread_count'];
	$messages		= $topic_messages['messages'];

	if($messages){ ?>
	<style type="text/css">
		ul.messages{ max-width:640px; }
		ul.messages li a {text-decoration:none;}
		ul.messages li {margin: 1em 0; padding:1px 1em; margin:1em 0; background: #fff;}
		ul.messages li.alternate{background: #f9f9f9;}
		ul.messages li.unread{font-weight: bold;}
		.message-avatar { float:left; margin:1em 1em 1em 0; }

		#tr_topic td, #tr_topic_replies td, #tr_topic_reply td{padding-top:0px !important;}
		#tr_topic_reply td{padding-bottom:0px !important;}
		.topic-avatar{ float:left; margin:1em 1em 0 0; }
		.topic-content pre, .reply-content pre{ background: #eaeaea; background: rgba(0,0,0,.07); white-space: pre-wrap; word-wrap: break-word; padding:8px; }
		.topic-content code, .reply-content code{ background: none; }
		.topic-content img{max-width: 640px; }
		.topic-meta{margin: 1em 0 2em; }
		.wrap h1 a, .reply-meta a{text-decoration:none;}
		ul.replies li { padding:1px 1em; margin:1em 0; background: #fff;}
		ul.replies li.alternate{background: #f9f9f9;}
		.reply-meta, .reply-content{margin: 1em 0;}
		.reply-meta .dashicons{width:18px; height:18px; font-size:14px; line-height: 18px;}
		.reply-avatar { float:left; margin:1em 1em 0 0; }
	</style>
	<ul class="messages">
		<?php foreach ($messages as $message) { $alternate = empty($alternate)?'alternate':'';?>
		<li id="messages-<?php echo $message['id']; ?>" class="<?php echo $alternate; echo empty($message['status'])?' unread':'' ?>">
			<div class="message-avatar"><img src="<?php echo str_replace('/0', '/132', $message['sender_user']['avatar']); ?>" width="48" alt="<?php echo $message['sender_user']['nickname'];?>" /></div>
			<!-- <p><?php echo $message['sender_user']['nickname'];?> 在<?php echo human_time_diff($message['time']);?>前回复了你的帖子《<a target="_blank" href="<?php echo admin_url('admin.php?page=wpjam-topics&action=reply&id='.$message['topic_id'].'#reply-'.$message['reply_id']); ?>"><?php echo $message['topic_title'];?></a>》：</p> -->

			<p><?php echo $message['sender_user']['nickname'];?> 在<?php echo human_time_diff($message['time']);?>前回复了你的帖子《<?php wpjam_ajax_button(['action'=>'topic_reply', 'class'=>'', 'page_title'=>'帖子详情', 'button_text'=>$message['topic_title'], 'data'=>['topic_id'=>$message['topic_id']]]); ?>》：</p>
			
			<?php echo wpautop(wp_unslash($message['content']));?>
		</li>
		<?php } ?>
	</ul>
	<script type="text/javascript">
	jQuery(function ($) {
		$('body').on('page_action_success', function(e, response){
			var action		= response.page_action;
			var action_type	= response.page_action_type;

			if(action == 'topic_reply'){
				if(action_type == 'submit'){
					$('#TB_ajaxContent').html(response.data);
				}else if(action_type == 'form'){
					tb_position();
				}
			}
		});
	});
	</script>
	<?php }

	if($unread_count){
		wpjam_remote_request('http://jam.wpweixin.com/api/update_topic_messages_unread_count.json',[
			'headers'	=> ['openid'=>WPJAM_Verify::get_openid()]
		]);
		$topic_messages['unread_count'] = 0;
		
		foreach ($messages as $key => $message) {
			$messages[$key]['status'] = 1;
		}
		$topic_messages['messages'] = $messages;

		$current_user_id	= get_current_user_id();
		set_transient('wpjam_topic_messages_'.$current_user_id, $topic_messages, HOUR_IN_SECONDS);
	}
}