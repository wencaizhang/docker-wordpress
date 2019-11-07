<?php
remove_action('check_comment_flood', 'check_comment_flood_db', 10, 4);

add_filter('pre_wp_update_comment_count_now',	['WPJAM_Comment', 'filter_pre_wp_update_comment_count_now'], 10, 3);
add_filter('wp_is_comment_flood', 				['WPJAM_Comment', 'filter_is_comment_flood'], 10, 4);
add_filter('wpjam_post_json',					['WPJAM_Comment', 'filter_post_json'], 10, 2);
add_filter('get_avatar_comment_types', 			['WPJAM_Comment', 'filter_get_avatar_comment_types']);

class WPJAM_Comment{
	public static function filter_get_avatar_comment_types($comment_types){
		return array_keys(self::get_types());
	}	

	public static function filter_pre_wp_update_comment_count_now($count, $old, $post_id){
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type = ''", $post_id));
	}

	public static function filter_is_comment_flood($is_flood, $ip, $email, $date){
		global $wpdb;
		
		if(current_user_can('manage_options') || current_user_can('moderate_comments')){
			return false;
		}
		
		$lasttime	= gmdate('Y-m-d H:i:s', time() - 15);

		if(is_user_logged_in()){
			$user			= get_current_user_id();
			$check_column	= '`user_id`';
		}else{
			$user			= $ip;
			$check_column	= '`comment_author_IP`';
		}

		$sql	= $wpdb->prepare("SELECT `comment_date_gmt` FROM `$wpdb->comments` WHERE `comment_type` = '' AND `comment_date_gmt` >= %s AND ( $check_column = %s OR `comment_author_email` = %s ) ORDER BY `comment_date_gmt` DESC LIMIT 1", $lasttime, $user, $email);

		// trigger_error($sql);

		if($wpdb->get_var($sql)) {
			return true;
		}

		return false;
	}

	public static function filter_post_json($post_json, $post_id){
		$post		= get_post($post_id);
		$post_type	= $post->post_type;

		if(post_type_supports($post_type, 'comments')){
			$post_json['comment_count']		= intval($post->comment_count);
			$post_json['comment_status']	= $post->comment_status;
		}

		$actions	= ['like', 'fav'];

		foreach($actions as $action){
			if(post_type_supports($post_type, $action) || post_type_supports($post_type, $action.'s')){
				$post_json[$action.'_count']	= intval(get_post_meta($post_id, $action.'s', true));
			}
		}

		if(is_singular($post_type)){

			if(post_type_supports($post_type, 'comments')){
				$post_json['comments']		= WPJAM_Comment::get_comments(['post_id'=>$post_id]);
			}

			$user_id	= wpjam_get_current_user_id(false);

			foreach($actions as $action){
				if(!post_type_supports($post_type, $action.'s')){
					continue;
				}

				if($action == 'like'){
					$likes		= WPJAM_Comment::get_comments(['post_id'=>$post_id,	'type'=>'like']);
					$is_liked	= 0;

					if($user_id && $likes){
						$user_ids	= wp_list_pluck($likes, 'user_id');
						if($user_ids && in_array($user_id, $user_ids)){
							$is_liked	= 1;
						}	
					}
					
					$post_json['is_liked']	= $is_liked;
					$post_json['likes']		= $likes;
				}elseif($action == 'fav'){
					$favs		= WPJAM_Comment::get_comments(['post_id'=>$post_id,	'type'=>'fav']);
					$is_faved	= 0;

					if($user_id && $favs){
						$user_ids	= wp_list_pluck($favs, 'user_id');
						if($user_ids && in_array($user_id, $user_ids)){
							$is_faved	= 1;
						}
					}
					
					$post_json['is_faved']	= $is_faved;
				}
			}
		}

		return $post_json;
	}

	public static function get($comment_id){
		return get_comment($comment_id, ARRAY_A);
	}

	public static function insert($comment_data){
		$comment_type		= $comment_data['type'] ?? '';

		$comment_post_ID	= absint($comment_data['post_id']);
		if(empty($comment_post_ID)){
			return new WP_Error('empty_post_id', 'post_id不能为空');
		}

		$post	= get_post($comment_post_ID);
		if(empty($post)){
			return new WP_Error('invalid_post_id', '非法 post_id');
		}

		if($comment_type == 'comment' || $comment_type == ''){
			if($post->comment_status == 'closed'){
				return new WP_Error('comment_closed', '已关闭留言');
			}
		}

		if('publish' != $post->post_status){
			return new WP_Error( 'invalid_post_status', '文章未发布，不能评论。' );
		}

		$user_id	= absint($comment_data['user_id']);
		if(empty($user_id)){
			return new WP_Error('empty_user_id', 'user_id不能为空');
		}

		$user	= get_userdata($user_id);
		if(empty($user)){
			return new WP_Error('invalid_user_id', '非法 user_id');
		}

		if(empty($user->display_name)) {
			$comment_author		= $user->user_login;
		}else{
			$comment_author		= $user->display_name;
		}
		
		$comment_author_email	= $user->user_email;
		$comment_author_url		= $user->user_url;

		if($comment_type == 'comment' || $comment_type == ''){
			$comment_content	= $comment_data['comment'] ?? '';
			$comment_content	= trim(wp_strip_all_tags($comment_content));
			if(empty($comment_content)){
				return new WP_Error('require_valid_comment', '评论内容不能为空。');
			}
		}else{
			$comment_content	= '';
		}

		if(isset($comment_data['parent'])){
			$comment_parent = absint($comment_data['parent']);
		}else{
			$comment_parent		= 0;
		}

		$comment_author_IP	= preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);

		$comment_agent		= $_SERVER['HTTP_USER_AGENT'] ?? '';
		$comment_agent		= substr($comment_agent, 0, 254);

		$comment_date		= current_time('mysql');
		$comment_date_gmt	= current_time('mysql', 1);
		
		$comment_data = compact(
			'comment_post_ID',
			'comment_author',
			'comment_author_email',
			'comment_author_url',
			'comment_content',
			'comment_type',
			'comment_parent',
			'comment_author_IP',
			'comment_agent',
			'comment_date',
			'comment_date_gmt',
			'user_id'
			// 'comment_meta',
		);

		$comment_data	= wp_slash($comment_data);
		$comment_data	= wp_filter_comment($comment_data);

		$comment_approved	= 1;
		if($comment_type == 'comment' || $comment_type == ''){
			$comment_approved = wp_allow_comment($comment_data, $avoid_die=true);
			if(is_wp_error($comment_approved)) {
				return $comment_approved;
			}
		}

		$comment_data['comment_approved']	= $comment_approved;

		$comment_id = wp_insert_comment($comment_data);
		if(!$comment_id) {
			global $wpdb;

			$fields = ['comment_author', 'comment_author_email', 'comment_author_url', 'comment_content'];

			foreach($fields as $field){
				$comment_data[$field]	= $wpdb->strip_invalid_text_for_column($wpdb->comments, $field, $comment_data[$field]);
			}

			$comment_id	= wp_insert_comment($comment_data);
		}

		if(!$comment_id){
			return new WP_Error( 'comment_save_error', '评论保存失败，请稍后重试！', 500 );
		}

		do_action( 'comment_post', $comment_id, $comment_data['comment_approved'], $comment_data );

		return $comment_id;
	}

	public static function update($comment_id, $data){
		$comment_data	= [];

		$comment_data	= self::get($comment_id);

		if(isset($data['comment'])){
			$comment_data['comment_content']		= $data['comment'];
		}

		if(isset($data['approved'])){
			$comment_data['comment_approved']	= $data['approved'];
		}

		$result	= wp_update_comment($comment_data);

		if(!$result){
			return new WP_Error('comment_update_failed', '评论更新失败！');
		}

		return $result;
	}

	public static function delete($comment_id, $force_delete=false){
		return wp_delete_comment($comment_id, $force_delete);
	}

	public static function action($user_id, $post_id, $action='like'){
		if(in_array($action, ['unlike', 'unfav'])){
			$type	= str_replace('un', '', $action);
			$status	= -1;
		}else{
			$type	= $action;
			$status	= 1;
		}

		$types		= self::get_types();
		$type_label	= $types[$type] ?? '';
		if(empty($type_label)){
			return new WP_Error('invalid_action_type', '非法的动作类型'); 
		}

		$comments	= get_comments(compact('user_id', 'post_id', 'type'));
		if($comments){
			if($status == 1){
				$result	= new WP_Error('duplicate_'.$type, '不能重复'.$type_label);
			}else{
				$comment_id	= current($comments)->comment_ID;
				$result	= self::delete($comment_id, $force_delete=true);
			}
		}else{
			if($status == 1){
				$result	= self::insert(compact('user_id', 'post_id', 'type'));
			}else{
				$result	= new WP_Error('empty_'.$type, '你都没有'.$type_label.'过。');
			}
		}

		if(!is_wp_error($result)){
			$comments	= get_comments(compact('post_id', 'type'));

			update_post_meta($post_id, $type.'s', count($comments));
		}

		return $result;
	}

	public static function get_comments($args=[], $thread=false){
		$args	= wp_parse_args($args, [
			'post_id'	=> 0,
			'order'		=> 'ASC',
			'type'		=> 'comment',			
		]);

		if(empty($args['user_id'])){
			$args['author__not_in']	= [0];
		}

		$comments	= get_comments($args);

		if(empty($comments)){
			return [];
		}

		$comments_json	= [];

		if($args['type'] == 'comment' || $args['type'] == ''){
			// $top_level_comments	= [];
			$comment_authors	= wp_list_pluck($comments, 'comment_author', 'comment_ID');
		}else{
			$comment_authors	= [];
		}

		foreach($comments as $comment){
			$comments_json[]	= self::parse_for_json($comment, $comment_authors);
		}
		
		return $comments_json;
	}

	public static function parse_for_json($comment, $comment_authors=[]){
		$comment	= get_comment($comment);
		$timestamp	= strtotime($comment->comment_date_gmt);
		$author		= self::get_author($comment);

		$base		= [
			'id'		=> intval($comment->comment_ID),
			'post_id'	=> intval($comment->comment_post_ID),
			'timestamp'	=> $timestamp,
			'time'		=> wpjam_human_time_diff($timestamp),
		];

		$comment_type	= $comment->comment_type;

		if($comment_type == 'like' || $comment_type == 'fav'){
			$comment_json	= array_merge($base, $author);
		}else{
			$reply_to	= '';
			$parent		= intval($comment->comment_parent);

			if($parent && ($comment_type == 'comment' || $comment_type == '')){
				if($comment_authors){
					$reply_to		= $comment_authors[$parent] ?? '';	
				}else{
					$parent_comment	= get_comment($parent);
					$reply_to		= $parent_comment ? $parent_comment->comment_author : '';
				}
			}

			$comment_json	= [
				'parent'	=> $parent,
				'approved'	=> intval($comment->comment_approved),
				'content'	=> wp_strip_all_tags($comment->comment_content),
				'reply_to'	=> $reply_to,
				'user_id'	=> intval($comment->user_id),
				'author'	=> $author,
			];

			$comment_json	= array_merge($base, $comment_json);
		}

		return apply_filters('wpjam_comment_json', $comment_json, $comment->comment_ID, $comment);
	}

	public static function get_author($comment_or_user_id){
		$author	= [];

		if(is_object($comment_or_user_id)){
			$comment	= $comment_or_user_id;
			$user_id	= $comment_or_user_id->user_id;
		}else{
			$comment	= null;
			$user_id	= $comment_or_user_id;
		}

		if($user_id){
			if($userdata = get_userdata($user_id)){
				$author	= [
					'author'	=> $comment ? $comment->comment_author : '',
					'user_id'	=> intval($user_id),
					'nickname'	=> $userdata->display_name,
					'avatar'	=> get_avatar_url($user_id, 200),
				];
			}
		}

		return $author;
	}

	public static function get_types(){
		return ['comment'=>'评论','like'=>'喜欢','fav'=>'收藏'];
	}
}