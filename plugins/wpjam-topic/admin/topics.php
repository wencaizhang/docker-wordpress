<?php
add_filter('wpjam_topics_list_table', function(){
	return [
		'title'			=>'讨论组',
		'plural'		=>'wpjam-topics',
		'singular'		=>'wpjam-topic',
		'model'			=>'WPJAM_Topic',
		'fixed'			=>false,
		'capability'	=>'read',
		'search'		=>true,
		'ajax'			=>true,
	];
});

add_action('admin_head', function(){
	?>
	<style type="text/css">
	.subsubsub{margin-bottom: 8px;}
	.tablenav.top{display: none;}
	
	table.wpjam-topics{border-collapse:collapse;}
	table.wpjam-topics thead, table.wpjam-topics tfoot{display: none;}

	table.wpjam-topics tr{border-bottom: 1px solid #e5e5e5;}
	table.wpjam-topics tr:last-child{border-bottom:none;}

	.topic-avatar{float: left; margin: 2px 10px 2px 0;}
	.topic-avatar a, .topic-avatar a img{display: block;}

	.widefat td p.topic-title{ margin: 4px 0 16px;}
	.widefat td p.topic-meta{ margin: 16px 0 4px;}
	
	.topic-meta span{ margin-right: 8px; padding-right: 8px; border-right: solid 1px #999; }
	.topic-meta span:last-child{ border-right: none; }
	.topic-delete a{color: #a00;}

	#TB_ajaxContent .topic-avatar{float: right; margin: 0 0 10px 10px;}
	#TB_ajaxContent h2, #TB_ajaxContent h3, #TB_ajaxContent div.topic-meta{margin: 4px 0 20px 0;}

	#TB_ajaxContent div#comments li { padding:10px; margin:10px 0; background: #fff;}
	#TB_ajaxContent div#comments li.alternate{background: #f9f9f9;}

	#TB_ajaxContent div#comments li .reply{float:right; display: none;}
	#TB_ajaxContent div#comments li:hover .reply{display: block;}

	#TB_ajaxContent div.comment-meta{margin: 2px 0 6px 0;}
	#TB_ajaxContent div.comment-meta span{ margin-right: 8px; padding-right: 8px; border-right: solid 1px #999; }
	#TB_ajaxContent div.comment-meta span:last-child{ border-right: none; }
	#TB_ajaxContent div.comment-meta span a, #TB_ajaxContent .comment_parent{ text-decoration: none; }
	#TB_ajaxContent span.comment-delete a{color: #a00;}
	#TB_ajaxContent div.comment-content{margin-left: 66px;}
	#TB_ajaxContent div.comment-meta .dashicons{width:18px; height:18px; font-size:14px; line-height: 18px;}
	#TB_ajaxContent div.comment-avatar { float:left; margin:2px 12px 2px 2px; }

	#TB_ajaxContent #comment_subtitle{height:20px line-height:20px; padding-bottom: 0;}

	</style>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('list_table_action_success', function(event, response){
			if(response.list_action == 'comment'){
				if($('#TB_ajaxContent #comment').length == 0){
					$('#TB_ajaxContent p.submit').remove();
				}

				if(response.list_action_type == 'submit'){
					$('#comment').val('').focus();
					$('div#comments li:last').animate({opacity: 0.1}, 500).animate({opacity: 1}, 500);
				}else{
					$('body').on('click', '.reply', function(){
						$('input[name=parent]').val($(this).data('comment_id'));
						$('#comment_subtitle').html('@'+$(this).data('user')+' <a class="unreply dashicons dashicons-no-alt"></a>').fadeIn(500);
						$('textarea#comment').focus();
					});

					$('body').on('click', '.unreply', function(){
						$('input[name=parent]').val(0);
						$('#comment_subtitle').fadeOut(500);
						$('textarea#comment').focus();
					});

					$('body').on('click', 'a.comment_parent', function(){
						var comment_parent = $('#comment_'+$(this).data('parent'));
						var top = comment_parent.offset().top - $('#list_table_action_form').offset().top;
						
						$('#TB_ajaxContent').animate({scrollTop:top}, 500, function(){
							comment_parent.animate({opacity: 0.1}, 500).animate({opacity: 1}, 500);
						});
					});
				}
			}
		});	

		$('body').on('page_action_success', function(e, response){
			var action		= response.page_action;
			var action_type	= response.page_action_type;

			if(action == 'delete_comment'){
				var count = $('#comment_count').data('count');
				if(count == 1){
					$('#comments').animate({opacity: 0.1}, 500, function(){ $(this).remove() });
				}else{
					$('#comment_count').data('count', count-1);
					$('#comment_count').html(count-1);

					var comment_id	= response.comment_id;
					$('#comment_'+comment_id).animate({opacity: 0.1}, 500, function(){ $(this).remove();});; 
				}
			}
		});
	});

	</script>
	<?php
});

function wpjam_topics_ajax_response(){
	$action			= $_POST['page_action'];
	$action_type	= $_POST['page_action_type'];

	$data	= wp_parse_args($_POST['data']);

	if($action == 'delete_comment'){

		$post_id	= $data['post_id'];
		$comment_id	= $data['comment_id'];

		$result	= WPJAM_Topic::delete_comment($post_id, $comment_id);

		if(is_wp_error($result)){
			wpjam_send_json($result);
		}else{
			wpjam_send_json(['errcode'=>0,'comment_id'=>$comment_id]);
		}
	}
}

class WPJAM_Topic extends WPJAM_PostType{
	public static function views(){
		global $wpjam_list_table;

		$switched	= wpjam_topic_switch_to_blog();

		$groups	= wpjam_get_terms([
			'taxonomy'		=> 'group', 
			'hide_empty'	=> false, 
			'meta_key'		=> 'order', 
			'orderby'		=> 'meta_value_num',
			'order'			=> 'DESC'
		]);

		if($switched){
			restore_current_blog();
		}

		$group_options	= wp_list_pluck($groups, 'name', 'slug');
		$group_options	= ['all'=>'所有'] + $group_options;

		$group	= $_REQUEST['group'] ?? 'all';
		$views	= [];

		foreach ($group_options as $key=>$value) {
			$class	= ($group == $key) ? 'current' : '';
			$views['group_'.$key]	= $wpjam_list_table->get_filter_link(['group'=>$key], $value, $class);
		}

		return $views;
	}

	public static function list($limit, $offset){

		$switched	= wpjam_topic_switch_to_blog();

		$view		= $_REQUEST['view'] ?? '';

		$query_args	= [
			'post_type'			=> 'topic',
			'post_status'		=> 'publish',
			'posts_per_page'	=> $limit,
			'offset'			=> $offset,
			'orderby'			=> 'last_comment_time'
		];

		if(isset($_REQUEST['group']) && $_REQUEST['group'] != 'all'){
			$query_args['group']	= $_REQUEST['group'];
		}

		if(!empty($_REQUEST['user_id'])){
			$query_args['author']	= $_REQUEST['user_id'];
		}

		if(!empty($_REQUEST['s'])){
			$query_args['s']		= $_REQUEST['s'];
		}

		add_action('parse_query', function($wp_query){
			if(empty($wp_query->query['group']) && empty($wp_query->query['author'])){
				$wp_query->is_home	= true;
			}
		});

		$topic_query	= new WP_Query($query_args);

		$items		= array_map(function($post){ return wpjam_get_post($post->ID); }, $topic_query->posts);
		$total		= $topic_query->found_posts;

		if($switched){
			restore_current_blog();
		}

		return compact('items', 'total');
	}

	public static function item_callback($item){
		global $wpjam_list_table;

		$switched	= wpjam_topic_switch_to_blog();

		$user_id	= $item['author']['id'];
		$group		= $item['group'] ? $item['group'][0] : [];	
		$userdata	= get_userdata($user_id);

		$item['topic']	= '<div class="topic-avatar">'.$wpjam_list_table->get_filter_link(['user_id'=>$user_id], get_avatar($user_id, 60)).'</div>';

		$item['topic']	.= '<p class="topic-title">';

		if(is_sticky($item['id'])){
			$item['topic']	.= '<span style="color:#0073aa; width:16px; height:16px; font-size:16px; line-height:18px;" class="dashicons dashicons-sticky"></span>';
		}

		$item['topic']	.= $wpjam_list_table->get_row_action('comment', ['id'=>$item['id'], 'title'=>$item['title']]);

		if($item['comment_count']){
			$item['topic']	.= '<span class="topic-comments">（'.$item['comment_count'].'）</span>';
		}

		$item['topic']	.= '</p>';

		$item['topic']	.= '<p class="topic-meta">';
		$item['topic']	.= $group ? '<span class="topic-group">'.$wpjam_list_table->get_filter_link(['group'=>$group['slug']], $group['name']).'</span>' : '';	
		$item['topic']	.= '<span class="topic-user">'.$wpjam_list_table->get_filter_link(['user_id'=>$user_id], $item['author']['name']).'</span>';
		$item['topic']	.= '<span class="topic-time">'.$item['time'].'</span>';

		if($item['comment_count']){
			$last_comment_user	= get_post($item['id'])->last_comment_user;
			$last_comment_user	= get_userdata($last_comment_user);

			if($last_comment_user){
				$item['topic']	.= '<span class="topic-last_reply">最后回复来自 '.$wpjam_list_table->get_filter_link(['user_id'=>$last_comment_user->ID], $last_comment_user->display_name).'</span>';	
			}
		}

		$can = self::can($item['id'], 'edit');
		if(!is_wp_error($can)){
			$item['topic']	.= '<span class="topic-edit">'.$wpjam_list_table->get_row_action('edit', ['id'=>$item['id']]).'</span>';
		}

		$capability	= is_multisite() ? 'manage_sites' : 'manage_options';
		if(current_user_can($capability)){
			if($item['comment_status'] == 'closed'){
				$item['topic']	.= '<span class="topic-open">'.$wpjam_list_table->get_row_action('open', ['id'=>$item['id']]).'</span>';
			}else{
				$item['topic']	.= '<span class="topic-close">'.$wpjam_list_table->get_row_action('close', ['id'=>$item['id']]).'</span>';
			}

			if(is_sticky($item['id'])){
				$item['topic']	.= '<span class="topic-close">'.$wpjam_list_table->get_row_action('unstick', ['id'=>$item['id']]).'</span>';
			}else{
				$item['topic']	.= '<span class="topic-close">'.$wpjam_list_table->get_row_action('stick', ['id'=>$item['id']]).'</span>';
			}

			$item['topic']	.= '<span class="topic-sink">'.$wpjam_list_table->get_row_action('sink', ['id'=>$item['id']]).'</span>';
			$item['topic']	.= '<span class="topic-delete">'.$wpjam_list_table->get_row_action('delete', ['id'=>$item['id']]).'</span>';
			
		}

		unset($item['row_actions']);

		$item['topic']	.= '</p>';

		if($switched){
			restore_current_blog();
		}

		return $item;
	}

	public static function get($post_id){
		$switched	= wpjam_topic_switch_to_blog();

		$topic		= parent::get($post_id);

		// wpjam_print_r($post_id);
		// wpjam_print_r($topic);

		if($topic){
			$topic['images']	= get_post_meta($post_id, 'images', true) ?: [];
		}

		$topic['group_id']	= $topic['group'] ? $topic['group'][0]['id'] : '';

		if($switched){
			restore_current_blog();
		}

		return $topic;
	}

	public static function insert($data){
		$switched	= wpjam_topic_switch_to_blog();

		$data['post_status']		= 'publish';
		$data['post_type']			= 'topic';
		$data['comment_status']		= 'open';
		$data['last_comment_time']	= time();

		$data	= self::validate_data($data);

		if(is_wp_error($data)){
			$result	= $data;
		}else{
			$result	= parent::insert($data);

			if(!is_wp_error($result)){
				do_action('wpjam_topic_added');
			}
		}	

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function update($post_id, $data){
		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id, 'edit');
		if(is_wp_error($can)){
			return $can;
		}

		$data	= self::validate_data($data);

		if(is_wp_error($data)){
			$result	= $data;
		}else{
			$result	= parent::update($post_id, $data);
		}

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function validate_data($data){
		$data['post_title']		= wp_strip_all_tags($data['title']);
		$data['post_content']	= wp_strip_all_tags($data['raw_content']);

		if(wpjam_blacklist_check($data['post_title'])){
			return new WP_Error('illegal_topic_title', '标题中有非法字符');
		}

		if(wpjam_blacklist_check($data['post_content'])){
			return new WP_Error('illegal_topic_content', '内容中有非法字符');
		}

		if(isset($data['group_id'])){
			$data['tax_input']	= ['group'=>[$data['group_id']]];
		}

		if(!empty($data['images'])){
			$data['meta_input']	= ['images'=>array_slice($data['images'], 0, 3)];
		}

		return $data;	
	}

	public static function open($post_id){
		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id);
		if(is_wp_error($can)){
			return $can;
		}

		$result	= parent::update($post_id, ['comment_status'=>'open']);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function close($post_id){
		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id);
		if(is_wp_error($can)){
			return $can;
		}

		$result	= parent::update($post_id, ['comment_status'=>'closed']);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function sink($post_id){
		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id);
		if(is_wp_error($can)){
			return $can;
		}

		$result	= parent::update($post_id, ['last_comment_time'=>get_post($post_id)->last_comment_time - MONTH_IN_SECONDS]);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function comment($post_id, $data){
		$switched	= wpjam_topic_switch_to_blog();

		$comment_data	= [
			'post_id'	=> $post_id,
			'user_id'	=> get_current_user_id(),
			'type'		=> '',
			'comment'	=> $data['comment'] ?? '',
			'parent'	=> $data['parent'] ?? 0
		];

		$comment_id	= WPJAM_Comment::insert($comment_data);

		if(!is_wp_error($comment_id)){
			parent::update($post_id, ['last_comment_user'=>get_current_user_id(), 'last_comment_time'=>time()]);

			$message	= [
				'sender'		=> $comment_data['user_id'],
				'receiver'		=> 0,
				'type'			=> '',
				'post_id'		=> $post_id,
				'comment_id'	=> $comment_id,
				'blog_id'		=> get_current_blog_id(),
				'content'		=> $comment_data['comment']
			];

			$parent		= $comment_data['parent'];
			if($parent){
				$comment_parent	= get_comment($parent);
				if($comment_parent){
					$message['receiver']	= $comment_parent->user_id;
					$message['type']		= 'topic_reply';
				}
			}else{
				$post	= get_post($post_id);
				
				$message['receiver']	= $post->post_author;
				$message['type']		= 'topic_comment';
			}

			if($message['type'] && $message['receiver']){
				wpjam_send_user_message($message);

				wpjam_add_user_notice($message['receiver'], [
					'type'		=> 'info',
					'key'		=> 'message',
					'notice'	=> '你有<strong>'.WPJAM_Message::get_unread_count($message['receiver']).'</strong>条未读站内消息',
					'admin_url'	=> 'wp-admin/admin.php?page=wpjam-topic-messages'
				]);
			}
		}

		if($switched){
			restore_current_blog();
		}

		return $comment_id;
	}

	public static function delete($post_id){
		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id);
		if(is_wp_error($can)){
			return $can;
		}

		$result		= parent::delete($post_id);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function delete_comment($post_id, $comment_id){
		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id);
		if(is_wp_error($can)){
			return $can;
		}

		$result	= WPJAM_Comment::delete($comment_id, $force_delete=false);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function stick($post_id){

		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id);
		if(is_wp_error($can)){
			return $can;
		}

		$result	= stick_post($post_id);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function unstick($post_id){

		$switched	= wpjam_topic_switch_to_blog();

		$can	= self::can($post_id);
		if(is_wp_error($can)){
			return $can;
		}

		$result	= unstick_post($post_id);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function get_actions(){
		return [
			'add'				=>['title'=>'发布',		'page_title'=>'发布新帖',		'capability'=>'read'],
			'edit'				=>['title'=>'编辑',		'page_title'=>'编辑贴子',		'capability'=>'read'],
			'comment'			=>['title'=>'回复',		'page_title'=>'贴子详情',		'capability'=>'read'],
			'delete'			=>['title'=>'删除',		'page_title'=>'删除帖子',		'direct'=>true,	'confirm'=>true],
			'open'				=>['title'=>'开启',		'page_title'=>'开启回复',		'direct'=>true,	'confirm'=>true],
			'close'				=>['title'=>'关闭',		'page_title'=>'关闭回复',		'direct'=>true,	'confirm'=>true],
			'sink'				=>['title'=>'沉贴',		'page_title'=>'沉贴',		'direct'=>true,	'confirm'=>true],
			'stick'				=>['title'=>'置顶',		'page_title'=>'置顶帖子',		'direct'=>true,	'confirm'=>true],
			'unstick'			=>['title'=>'取消置顶',	'page_title'=>'取消置顶帖子',	'direct'=>true,	'confirm'=>true],
		];
	}

	public static function get_fields($action_key='', $post_id=0){
		if($action_key == ''){
			return [
				'topic'		=> ['title'=>'帖子',		'type'=>'view',	'show_admin_column'=>true]
			];
		}elseif(in_array($action_key, ['add', 'edit'])){

			$switched	= wpjam_topic_switch_to_blog();

			if($action_key == 'edit'){
				$can = self::can($post_id, 'edit');
				if(is_wp_error($can)){
					wpjam_send_json($can);
				}
			}

			$groups	= wpjam_get_terms([
				'taxonomy'		=> 'group', 
				'hide_empty'	=> false, 
				'meta_key'		=> 'order', 
				'orderby'		=> 'meta_value_num',
				'order'			=> 'DESC'
			]);

			if($switched){
				restore_current_blog();
			}

			$group_options	= wp_list_pluck($groups, 'name', 'id');

			$fields = [
				'group_id'		=> ['title'=>'分组',		'type'=>'select',	'options'=>$group_options],
				'title'			=> ['title'=>'标题',		'type'=>'text'],
				'raw_content'	=> ['title'=>'内容',		'type'=>'textarea',	'rows'=>6,	'description'=>'如提问，请尽量上传截图，否则无法回答你的问题。'],
				'images'		=> ['title'=>'相关图片',	'type'=>'mu-img',	'item_type'=>'url',	'description'=>'如果文字无法描述你的帖子，请添加截图（最多3张）。'],
			];

			return $fields;
		}elseif($action_key == 'comment'){
			global $wpjam_list_table;

			$switched	= wpjam_topic_switch_to_blog();

			self::update_views($post_id);

			$fields	= [];
			$topic	= self::get($post_id);

			$user_id	= $topic['author']['id'];
			$userdata	= get_userdata($user_id);

			$group		= $topic['group'] ? $topic['group'][0] : [];

			$topic_avatar	= '<div class="topic-avatar">'.get_avatar($user_id, 80).'</div>';

			$topic_title	= '<h2>';
			$topic_title	.= $group ? '<span class="topic-group">'.$group['name'].'</span> | ' : '';
			$topic_title	.= $topic['title'];
			$topic_title	.= '</h2>';

			$topic_meta		= '<div class="topic-meta">';
			$topic_meta		.= '<span class="topic-author">'.$topic['author']['name'].'</span>';
			$topic_meta		.= '<span class="topic-time">'.$topic['time'].'</span>';
			$topic_meta		.= '<span class="topic-views">'.$topic['views'].' 次查看</span>';
			$topic_meta		.= '</div>';

			$topic_content	= '<div class="topic-content">';

			$topic_content	.= make_clickable($topic['content']);

			// if($topic['modified'] && $topic['timestamp_modified'] != $topic['timestamp']){
			// 	$topic_content	.= '<p>最后编辑于'.$topic['modified'].'</p>';
			// }

			if($topic['images'] && ($images = maybe_unserialize(wp_unslash($topic['images'])))){
				$topic_content	.= '<p>';
				foreach ($images as $image ) {
					$topic_content	.= '<img srcset="'.$image.' 2x" src="'.$image.'" />'."\n";
				}
				$topic_content	.= '</p>';
			}

			$topic_content	.= '</div>';

			$fields['topic']	= ['title'=>'',	'type'=>'view',	'value'=>$topic_avatar.$topic_title.$topic_meta.$topic_content];

			$topic_comments	= '';

			$comments	= WPJAM_Comment::get_comments(['post_id'=>$post_id]);

			if($comments){
				$topic_comments	.= '<div id="comments">';
				$topic_comments	.= '<h3><span id="comment_count" data-count="'.$topic['comment_count'].'">'.$topic['comment_count'].'</span>条回复'.'</h3>';
				$topic_comments	.= '<ul>';

				foreach ($comments as $comment) {
					$user_id	= $comment['user_id'];

					if(!$comment['approved'] && $user_id != get_current_user_id()){
						continue;
					}

					$alternate	= empty($alternate)?'alternate':'';
					$capability	= is_multisite() ? 'manage_sites' : 'manage_options';

					$comment_avatar		= '<div class="comment-avatar">'.get_avatar($comment['user_id'], 50).'</div>';

					$comment_content	= make_clickable(wpautop(convert_smilies($comment['content'])));
					if($comment['parent']){
						$comment_parent		= $comment['reply_to'] ? '<a class="comment_parent" data-parent="'.$comment['parent'].'" href="javascript:;">@'.$comment['reply_to'].'</a> ' : '';
						$comment_content	= $comment_parent.$comment_content;
					}
					$comment_content	= '<div class="comment-content">'.$comment_content.'</div>';

					$comment_meta	= '<div class="comment-meta">';
					$comment_meta	.= '<span class="comment-author">'. $comment['author']['nickname'].'</span>';
					$comment_meta	.= '<span class="comment-time">'.$comment['time'].'</span>';
					$comment_meta	.= current_user_can($capability) ? '<span class="comment-delete">'.wpjam_get_ajax_button(['action'=>'delete_comment','button_text'=>'删除','class'=>'','data'=>['post_id'=>$post_id, 'comment_id'=>$comment['id']],'direct'=>true,'confirm'=>true])
					.'</span>' : '';
					$comment_meta	.= '</div>';

					$comment_reply	= '<a class="reply dashicons dashicons-undo" title="回复给'.$comment['author']['nickname'].'" data-user="'.$comment['author']['nickname'].'" data-comment_id="'.$comment['id'].'" href="javascript:;"></a>';

					$topic_comments	.= '<li id="comment_'. $comment['id'].'" class="'.$alternate.'">'.$comment_avatar.$comment_reply.$comment_meta.$comment_content.'</li>';
				}

				$topic_comments	.= '</ul>';
				$topic_comments	.= '</div>';

				$fields['comments']	= ['title'=>'',	'type'=>'view',	'value'=>$topic_comments];
			}

			if($topic['comment_status'] == 'closed'){
				$comment_fields	= ['title'=>'',	'type'=>'view',		'value'=>'<h3>帖子已关闭</h3>'];
			}else{
				$comment_fields	= ['title'=>'',	'type'=>'fieldset',	'fields'=>[
					'title'		=> ['title'=>'',	'type'=>'view',		'value'=>'<h3 id="comment_title">我要回复</h3><p id="comment_subtitle" style="display:none;"></p>'],
					'comment'	=> ['title'=>'',	'type'=>'textarea',	'rows'=>6,	'description'=>' '],
					// 'images'		=> ['title'=>'',	'type'=>'mu-img',	'description'=>''],
					'parent'	=> ['title'=>'',	'type'=>'hidden',	'value'=>''],
				]];
			}

			$fields['comment_set']	= $comment_fields;

			if($switched){
				restore_current_blog();
			}

			return $fields;
		}
		return [];
	}

	public static function can($post_id, $action=''){
		$capability	= is_multisite() ? 'manage_sites' : 'manage_options';

		if($action == 'edit'){
			$topic	= self::get($post_id);
			$result	= current_user_can($capability) || (get_current_user_id() == $topic['author']['id'] && time() - $topic['timestamp'] < MINUTE_IN_SECONDS * 10);
		}else{
			$result	= current_user_can($capability);
		}

		if($result){
			return true;
		}else{
			return new WP_Error('bad_authentication', '无权限');
		}
	} 
}
