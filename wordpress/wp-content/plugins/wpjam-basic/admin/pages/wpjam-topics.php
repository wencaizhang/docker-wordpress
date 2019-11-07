<?php
add_filter('wpjam_basic_topics_tabs', function($tabs){
	return [
		'topics'	=> ['title'=>'讨论组',	'function'=>'list'],
		'message'	=> ['title'=>'消息提醒',	'function'=>'wpjam_topic_user_messages_page'],
		'profile'	=> ['title'=>'个人资料',	'function'=>'wpjam_topic_user_profile_page'],
	];
});

add_filter('wpjam_basic_topics_list_table', function(){
	return array(
		'title'			=> '讨论组',
		'plural'		=> 'wpjam_topics',
		'singular' 		=> 'wpjam_topic',
		'fixed'			=> false,
		'ajax'			=> true,
		'search'		=> true,
		'model'			=> 'WPJAM_AdminTopic'	
	);
});

function wpjam_basic_topics_ajax_response(){
	$action			= $_POST['page_action'];
	$action_type	= $_POST['page_action_type'];

	$data	= wp_parse_args($_POST['data']);

	if($action == 'delete_weixin_user'){
		delete_user_meta(get_current_user_id(), 'wpjam_weixin_user');
		wpjam_send_json();
	}elseif($action == 'delete_reply'){
		$reply_id	= $data['reply_id'];

		if(current_user_can('manage_options')){
			$result	= WPJAM_Reply::delete($reply_id);
		}else{
			$result	= new WP_Error('bad_authentication', '无权限');
		}

		if(is_wp_error($result)){
			wpjam_send_json($result);
		}else{
			wpjam_send_json(['reply_id'=>$reply_id]);
		}
	}
}

function wpjam_topic_user_profile_page(){
	echo '<h2>个人资料 </h2>';

	echo '<style type="text/css"> .form-table th{width: 80px; } </style>';

	$weixin_user	= WPJAM_Verify::get_weixin_user();

	wpjam_fields([
		'nickname'	=> ['title'=>'微信昵称',	'type'=>'view',	'value'=>$weixin_user['nickname']],
		'avatar'	=> ['title'=>'微信头像',	'type'=>'view',	'value'=>'<img src="'.str_replace('/132', '/0', $weixin_user['headimgurl']).'" width="200" />']
	]);

	wpjam_ajax_button(['action'=>'delete_weixin_user','button_text'=>'切换账号','class'=>'button-primary large','direct'=>true,'confirm'=>true]);
}

function wpjam_topic_user_messages_page(){

	echo '<h2>消息提醒</h2>';
	echo '<p>每15分钟更新刷新！</p>';

	$topic_messages = wpjam_get_topic_messages();

	if($topic_messages['unread_count']){
		wpjam_remote_request('http://jam.wpweixin.com/api/topic/messages/read.json',[
			'headers'	=> ['openid'=>WPJAM_Verify::get_openid()]
		]);
		$topic_messages['unread_count'] = 0;

		$messages = $topic_messages['messages'];
		
		foreach ($messages as $key => $message) {
			$messages[$key]['status'] = 1;
		}
		$topic_messages['messages'] = $messages;

		$current_user_id	= get_current_user_id();
		set_transient('wpjam_topic_messages_'.$current_user_id, $topic_messages, 900);
	}

	if($messages = $topic_messages['messages']){ ?>
	<ul class="messages">
		<?php foreach ($messages as $message) { $alternate = empty($alternate)?'alternate':'';?>
		<li id="message-<?php echo $message['id']; ?>" class="<?php echo $alternate; echo empty($message['status'])?' unread':'' ?>">
			<div class="sender-avatar"><img src="<?php echo str_replace('/0', '/132', $message['sender_user']['avatar']); ?>" width="60" alt="<?php echo $message['sender_user']['nickname'];?>" /></div>

			<?php 
			$message_type		= $message['type'] ?: 'reply'; 
			$topic_reply_url	= admin_url('admin.php?page=wpjam-basic-topics&tab=topics&action=reply&id='.$message['topic_id'].'#reply_'.$message['reply_id']);
			?>

			<div class="message-meta">
				<span class="message-sender"><?php echo $message['sender_user']['nickname'];?></span>
				- <span class="message-time"><?php echo wpjam_human_time_diff($message['time']);?></span>
				- 在帖子「<a href="<?php echo $topic_reply_url;?>"><?php echo $message['topic_title']; ?></a>」
					<?php if($message_type	== 'reply'){ echo '给你留言了：'; }elseif($message_type	== 'reply_to'){ echo '回复了你的留言：'; } ?>
			</div>
			
			<div class="message-content">
				<?php echo wpautop(wp_unslash($message['content']));?>
			</div>
		</li>
		<?php } ?>
	</ul>
	<?php }
}

class WPJAM_AdminTopic  {
	public static function get($id){
		$topic = wpjam_remote_request('http://jam.wpweixin.com/api/topic/get.json?id='.$id);
		if(is_wp_error($topic)){
			return $topic;
		}

		$topic['images']	= maybe_unserialize(wp_unslash($topic['images']));

		return $topic;
	}

	public static function insert($data){
		$response	= self::topic($data);
		if(is_wp_error($response)){
			return $response;
		}else{
			return $response['id'];
		}
	}

	public static function update($id, $data){
		return self::topic($data, $id);
	}

	public static function topic($data, $id=0){
		$title		= $data['title'];
		$content	= $data['content'];
		$group		= $data['group'];

		if(empty($title)){
			return new WP_Error('empty_title', '标题不能为空。');
		}

		if(empty($title)){
			return new WP_Error('empty_content', '内容不能为空。');
		}

		if($images = $data['images']){
			$images = maybe_serialize($images);
		}

		return wpjam_remote_request('http://jam.wpweixin.com/api/topic/create.json', [
			'method'	=> 'POST',
			'body'		=> compact('title','content','group','images','id'),
			'headers'	=> ['openid'=>WPJAM_Verify::get_openid()]
		]);
	}

	public static function reply($topic_id, $data){
		$content	= $data['reply'];
		$reply_to	= $data['reply_to'] ?? 0;

		if(empty($content)){
			return new WP_Error('empty_reply_content', '回复内容不能为空。');
		}

		return wpjam_remote_request('http://jam.wpweixin.com/api/topic/reply.json', array(
			'method'	=> 'POST',
			'body'		=> compact('topic_id','content','reply_to'),
			'headers'	=> ['openid'=>WPJAM_Verify::get_openid()]
		));
	}

	public static function get_groups(){
		return [
			'all'		=> '全部',
			'wpjam'		=> 'WPJAM Basic',
			'weixin'	=> '微信机器人',
			'xintheme'	=> 'xintheme',
			'share'		=> '资源分享',
			'wp'		=> '其他帖子'
		];
	}

	public static function views(){
		global $wpjam_list_table;

		$groups	= self::get_groups();
		$group	= $_REQUEST['group'] ?? 'all';

		$views = [];
		foreach ($groups as $key => $value) {
			$class         = ($group == $key) ? 'current' : '';
			$views[$value] = $wpjam_list_table->get_filter_link(['group' => $key], $value, $class);
		}

		return $views;
	}

	public static function list($limit, $offset){

		$view	= $_REQUEST['view'] ?? '';
		$s		= $_REQUEST['s'] ?? '';
		$group	= $_REQUEST['group'] ?? 'all';
		$openid	= $_REQUEST['openid'] ?? '';
		$paged	= $_REQUEST['paged'] ?? 1;
		
		// $orderby	= isset($_REQUEST['orderby'])?$_REQUEST['orderby']:'last_reply_time';
		// $order		= isset($_REQUEST['order'])?$_REQUEST['order']:'DESC';

		// $args		= compact('paged','view','s','group','orderby', 'order');
		$args	= compact('paged','view','s','group', 'openid');
		$url	= add_query_arg($args, 'http://jam.wpweixin.com/api/topic/list.json');

		$response = wpjam_remote_request($url);

		if(is_wp_error($response)){
			wpjam_admin_add_error($response->get_error_message(),'error');
			$total	= 0;
			$items	= [];
		}else{
			$total	= $response['total'];
			$items	= $response['topics'];
		}

		return compact('items', 'total');
	}

	public static function item_callback($item){
		global $wpjam_list_table, $current_admin_url;

		$username	=  $item['user']['nickname'] ?? $item['user']['openid'];
		$avatar		=  $item['user']['avatar'] ? '<img src="'.str_replace('/0', '/132', $item['user']['avatar']).'" alt="'.$item['user']['nickname'].'" width="60" height="60" />' : '';

		$item['topic']	= '<div class="topic-avatar">'.$wpjam_list_table->get_filter_link(['openid'=>$item['user']['openid']], $avatar).'</div>';

		$item['topic']	.= '<p class="topic-title">';

		if($item['sticky']){
			$item['topic']	.= '<span style="color:#0073aa; width:16px; height:16px; font-size:16px; line-height:18px;" class="dashicons dashicons-sticky"></span>';
		}

		$item['topic']	.= $wpjam_list_table->get_row_action('reply',['id'=>$item['id'],'title'=>$item['title']]);

		if($item['reply_count']){
			$item['topic']	.= '<span class="topic-reply-count">（'.$item['reply_count'].'）</span>';
		}

		if(time()-$item['time'] < 10*MINUTE_IN_SECONDS && (WPJAM_Verify::get_openid() == $item['openid']) ){
			$item['topic']	.= ' | '.$wpjam_list_table->get_row_action('edit',['id'=>$item['id']]);
		}

		$item['topic']	.= '</p>';

		$groups	= self::get_groups();

		$group_name	= $groups[$item['group']] ?? $item['group'];

		unset($item['row_actions']['edit']);
		unset($item['row_actions']['reply']);
		unset($item['row_actions']['id']);	

		$item['topic']	.= $wpjam_list_table->row_actions($item['row_actions']);

		$item['topic']	.= '<p class="topic-meta">';
		$item['topic']	.= $item['group'] ? '<span class="topic-group">'.$wpjam_list_table->get_filter_link(['group'=>$item['group']], $group_name).'</span>  - ' : '';	
		$item['topic']	.= '<span class="topic-user">'.$wpjam_list_table->get_filter_link(['openid'=>$item['user']['openid']], $username).'</span>';
		$item['topic']	.= ' - <span class="topic-time">'.wpjam_human_time_diff($item['time']).'</span>';

		if($item['last_reply_openid']){
			// $item['topic']	.= ' - <span class="topic-last_reply">最后回复来自 '.$wpjam_list_table->get_filter_link(['openid'=>$item['last_reply_openid']], $item['last_reply_user']['nickname']).'</span>';
			$item['topic']	.= ' - <span class="topic-last_reply">最后回复来自 '.$wpjam_list_table->get_row_action('reply',['id'=>$item['id'], 'title'=>$item['last_reply_user']['nickname']]) .'</span>';
		}

		$item['topic']	.= '</p>';

		unset($item['row_actions']);
		

		return $item;
	}

	public static function get_actions(){
		return [
			'add'		=>['title'=>'发布',	'page_title'=>'发布新帖',	'capability'=>'read'],
			'edit'		=>['title'=>'编辑',	'page_title'=>'编辑贴子',	'capability'=>'read'],
			'reply'		=>['title'=>'回复',	'page_title'=>'贴子详情',	'capability'=>'read']
		];
	}

	public static function get_fields($action_key='', $id=0){
		if($action_key == ''){
			if(!empty($_POST['data'])){
				foreach (wp_parse_args($_POST['data']) as $key => $value) {
					$_REQUEST[$key]	= $value;
				}
			}

			$groups	= self::get_groups();
			$group	= $_REQUEST['group'] ?? 'all';

			if($group == 'all'){
				$group_name	= '讨论组';
			}else{
				$group_name	= $groups[$group] ?? '';
			}

			return [
				'topic'	=> ['title'=>$group_name,	'type'=>'view',	'show_admin_column'=>true]
			];
		}elseif(in_array($action_key, ['add', 'edit'])){
			$groups	= [''=>'']+self::get_groups();
			unset($groups['all']);

			$fields = [
				'group'		=> ['title'=>'分组',	'type'=>'select',	'options'=>$groups],
				'title'		=> ['title'=>'标题',	'type'=>'text'],
				'content'	=> ['title'=>'内容',	'type'=>'textarea',	'rows'=>10,'description'=>'不支持 HTML 标签，代码请放入[code][/code]中。<br />请详细描述问题，尽量输入相关的地址，否则无法分析和回答你的帖子。<br />不要凑字数，否则直接删帖。'],
				'images'	=> ['title'=>'相关图片',	'type'=>'mu-img',	'item_type'=>'url',	'description'=>'如果文字无法描述你的帖子，请添加截图。'],
			];

			if($action_key == 'edit2'){
				$fields['images']['item_type']	= 'url';
			}

			return $fields;
		}elseif($action_key == 'reply'){
			
			$fields	= [];
			$groups	= self::get_groups();

			$wpjam_topic = self::get($id);

			if(is_wp_error( $wpjam_topic )){
				wpjam_send_json($wpjam_topic);
			}

			$topic_title	= '<h2>'.convert_smilies($wpjam_topic['title']).'</h2>';

			if($wpjam_topic['modified']){
				$last_reply	= ' - <span class="topic-last_reply">最后编辑于'.wpjam_human_time_diff($wpjam_topic['modified']).'</span>';
			}else{
				$last_reply	= '';
			}

			$topic_avatar	= '<div class="topic-avatar"><img src="'.str_replace('/0', '/132', $wpjam_topic['user']['avatar']) .'" width="64" alt="'.$wpjam_topic['user']['nickname'].'" /></div>';

			$topic_meta		= '<div class="topic-meta">';
			$topic_meta		.= '<span class="topic-group">'.($groups[$wpjam_topic['group']] ?? $wpjam_topic['group']).'</span>';
			$topic_meta		.= ' - <span class="topic-author">'.$wpjam_topic['user']['nickname'].'</span>';
			$topic_meta		.= ' - <span class="topic-time">'.wpjam_human_time_diff($wpjam_topic['time']).'</span>';
			$topic_meta		.= '</div>';

			$topic_content	= wpautop(convert_smilies($wpjam_topic['content']));

			if($wpjam_topic['images'] && ($images = maybe_unserialize(wp_unslash($wpjam_topic['images'])))){
				$topic_content	.= '<p>';
				foreach ($images as $image ) {
					$topic_content	.= '<img srcset="'.$image.' 2x" src="'.$image.'" />'."\n";
				}
				$topic_content	.= '</p>';
			}

			$topic_content	= '<div class="topic-content">'.$topic_content.'</div>';

			$fields['topic']	= ['title'=>'',	'type'=>'view',	'value'=>$topic_avatar.$topic_title.$topic_meta.$topic_content];

			$topic_replies	= '';

			if($wpjam_topic['replies']){

				$reply_users	= wp_list_pluck($wpjam_topic['replies'], 'user', 'id');

				$topic_replies	.= '<div id="replies">';
				$topic_replies	.= '<h3><span id="reply_count" data-count="'.$wpjam_topic['reply_count'].'">'.$wpjam_topic['reply_count'].'</span>条回复'.'</h3>';
				$topic_replies	.= '<ul>';

				foreach ($wpjam_topic['replies'] as $wpjam_reply) { 

					// if(empty($wpjam_reply['user'])){
					// 	continue;
					// }

					$reply_avatar	= '<div class="reply-avatar"><img src="'. str_replace('/0', '/132', $wpjam_reply['user']['avatar']).'" width="50" alt="'.$wpjam_reply['user']['nickname'].'" /></div>';

					$reply_content	= $wpjam_reply['content'];

					if(!empty($wpjam_reply['reply_to'])){
						$reply_to	= $wpjam_reply['reply_to'];
						$reply_user	= $reply_users[$reply_to] ?? '';

						if($reply_user){
							$reply_content	= '<a class="reply_parent" data-reply_parent="'.$reply_to.'" href="javascript:;">@'.$reply_user['nickname'].'</a> '.$reply_content;
						}
					}

					$reply_content	= '<div class="reply-content">'.wpautop(convert_smilies($reply_content)).'</div>';

					$reply_meta		= '<span class="reply-author">'. $wpjam_reply['user']['nickname'].'</span>';
					$reply_meta		.= ' - <span class="reply-time">'. wpjam_human_time_diff($wpjam_reply['time']).'</span>';

					$reply_meta		= apply_filters('wpjam_topic_reply_meta', $reply_meta, $wpjam_reply['id']);

					$reply_meta		= '<div class="reply-meta">'.$reply_meta.'</div>';

					if(!$wpjam_topic['status']){

						$reply_to	= '<a class="reply_to dashicons dashicons-undo" title="回复给'.$wpjam_reply['user']['nickname'].'" data-user="'.$wpjam_reply['user']['nickname'].'" data-reply_id="'.$wpjam_reply['id'].'" href="javascript:;"></a>';
					}else{
						$reply_to	= '';
					}

					$alternate		= empty($alternate)?'alternate':'';

					$topic_replies .= '<li id="reply_'. $wpjam_reply['id'].'" class="'.$alternate.'">'.$reply_avatar.$reply_to.$reply_meta.$reply_content.'</li>';
				}

				$topic_replies	.= '</ul>';
				$topic_replies	.= '</div>';

				$fields['replies']	= ['title'=>'',	'type'=>'view',	'value'=>$topic_replies];
			}

			if($wpjam_topic['status']){
				$reply_title	= $topic_replies ? '<h3>帖子已关闭</h3>' : '';
				
				$reply_fields	= ['title'=>'',	'type'=>'view',		'value'=>$reply_title];
			}else{
				$reply_fields	= ['title'=>'',	'type'=>'fieldset',	'fields'=>[
					'title'		=> ['title'=>'',	'type'=>'view',		'value'=>'<h3 id="reply_title">我要回复</h3><p id="reply_subtitle" style="display:none;"></p>'],
					'reply'		=> ['title'=>'',	'type'=>'textarea',	'rows'=>6,'description'=>'不支持 HTML 标签，代码请放入[code][/code]中。'],
					// 'images'	=> ['title'=>'',	'type'=>'mu-img',	'description'=>''],
					'reply_to'	=> ['title'=>'',	'type'=>'hidden',	'value'=>''],
				]];
			}

			$fields['reply_set']	= $reply_fields;

			return $fields;
		}

		return [];
	}
}

add_action('admin_head', function(){
	?>
	<style type="text/css">
	/*.subsubsub{margin-bottom: 8px;}*/

	div.topic-avatar{float: left; margin: 2px 10px 2px 0;}
	div.topic-avatar a, .topic-avatar a img{display: block;}

	/*table tfoot{display: none;}*/

	table.widefat td p.topic-title{ margin: 4px 0 16px;}
	table.widefat td p.topic-meta{ margin: 0px 0 4px;}

	table.widefat td div.row-actions{float: right; display: none;}
	table.widefat td:hover div.row-actions{display: inline-block;}

	#TB_ajaxContent div.topic-avatar{float: right; margin: 0 0 10px 10px;}
	#TB_ajaxContent h2, #TB_ajaxContent h3, #TB_ajaxContent div.topic-meta{margin: 4px 0 20px 0;}

	div.topic-content pre, 
	div.reply-content pre{ background: #eaeaea; background: rgba(0,0,0,.07); white-space: pre-wrap; word-wrap: break-word; padding:8px; }
	
	div.reply-content code, 
	div.reply-content code{margin: 0; padding: 0; background: none; }

	ul.messages li, 
	div#replies li {padding:10px; margin:10px 0; background: #fff;}

	ul.messages li a,
	div#replies li a{text-decoration: none;}
	
	ul.messages li.alternate, 
	div#replies li.alternate{background: #f9f9f9;}

	div#replies li .reply_to{float:right; display: none;}
	div#replies li:hover .reply_to{display: block;}

	div.reply-meta,
	div.message-meta{margin: 2px 0 6px 0;}

	span.message-sender{font-weight: bold;}

	span.reply-delete a{color: #a00;}
	
	div.reply-content{margin-left: 66px;}

	div.message-content{margin-left: 78px;}

	div.sender-avatar, div.reply-avatar { float:left; margin:2px 12px 2px 2px; }

	#reply_subtitle{height:20px line-height:20px; padding-bottom: 0;}

	ul.messages{ max-width:640px; }

	</style>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('list_table_action_success', function(event, response){
			if(response.list_action == 'reply'){

				if($('#TB_ajaxContent #reply').length == 0){
					$('#TB_ajaxContent p.submit').remove();
				}

				if(response.list_action_type == 'submit'){
					$('#reply').val('').focus();
					$('div#replies li:last').animate({opacity: 0.1}, 500).animate({opacity: 1}, 500);
				}else{

					var reply_hash = window.location.hash;
					if(reply_hash){

						var reply = $(reply_hash);

						if(reply.length){
							var top = reply.offset().top - $('#list_table_action_form').offset().top;
							
							$('#TB_ajaxContent').animate({scrollTop:top}, 500, function(){
								reply.animate({opacity: 0.1}, 500).animate({opacity: 1}, 500);
							});
						}
							
					}
				}
			}
		});

		$('body').on('click', '.reply_to', function(){
			$('input[name=reply_to]').val($(this).data('reply_id'));
			$('#reply_subtitle').html('@'+$(this).data('user')+' <a class="unreply_to dashicons dashicons-no-alt"></a>').fadeIn(500);
			$('textarea#reply').focus();
		});

		$('body').on('click', '.unreply_to', function(){
			$('input[name=reply_to]').val(0);
			$('#reply_subtitle').fadeOut(500);
			$('textarea#reply').focus();
		});

		$('body').on('click', 'a.reply_parent', function(){
			var reply_parent = $('#reply_'+$(this).data('reply_parent'));
			var top = reply_parent.offset().top - $('#list_table_action_form').offset().top;
			
			$('#TB_ajaxContent').animate({scrollTop:top}, 500, function(){
				reply_parent.animate({opacity: 0.1}, 500).animate({opacity: 1}, 500);
			});
		});

		$('body').on('page_action_success', function(e, response){
			var action		= response.page_action;
			var action_type	= response.page_action_type;

			if(action == 'delete_weixin_user'){
				window.location.href='<?php echo admin_url('admin.php?page=wpjam-verify');?>';
			}
		});
	});

	</script>
	<?php
});