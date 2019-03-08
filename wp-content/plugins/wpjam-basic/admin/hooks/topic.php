<?php
add_action( 'admin_notices', function () {
	global $plugin_page;

	if(WPJAM_Verify::get_openid()){
		$topic_messages = wpjam_get_topic_messages();

		$unread_count	= $topic_messages['unread_count'];

		if($plugin_page != 'wpjam-topic-messages' && $unread_count){
			echo '<div class="updated"><p>你发布的帖子有<strong>'.$unread_count.'</strong>条回复了，请<a href="'.admin_url('admin.php?page=wpjam-topics&tab=message').'">点击查看</a>！</p></div>';
		}
	}
} );

function wpjam_get_topic_messages(){
	$current_user_id	= get_current_user_id();

	$topic_messages = get_transient('wpjam_topic_messages_'.$current_user_id);

	if($topic_messages === false){

		$topic_messages = wpjam_remote_request('http://jam.wpweixin.com/api/get_topic_messages.json',[
			'method'	=> 'POST',
			'headers'	=> ['openid'=>WPJAM_Verify::get_openid()]
		]);

		if(is_wp_error($topic_messages)){
			$topic_messages = array('unread_count'=>0, 'messages'=>array());
		}
		set_transient('wpjam_topic_messages_'.$current_user_id, $topic_messages, 15*60);
	}

	return $topic_messages;
}