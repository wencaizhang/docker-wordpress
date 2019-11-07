<?php
function wpjam_edit_user_avatar_profile($profileuser){
	$avatarurl	= get_user_meta($profileuser->ID, 'avatarurl', true);
	$field		= ['key'=>'avatarurl', 'title'=>'自定义头像', 'type'=>'img', 'item_type'=>'url', 'size'=>'200x200', 'value'=>$avatarurl];
	$field_html	= wpjam_get_field_html($field);
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('tr.user-profile-picture td').html('<?php echo $field_html; ?>');
		$('tr.user-profile-picture th').html('自定义头像');
	});
	</script>
	<?php 
}
add_action('show_user_profile','wpjam_edit_user_avatar_profile',1);
add_action('edit_user_profile','wpjam_edit_user_avatar_profile',1);


function wpjam_edit_user_avatar_profile_update($user_id){
	if(current_user_can('edit_users') || get_current_user_id() == $user_id){

		$avatarurl	= $_POST['avatarurl'] ?: '';

		if($avatarurl){
			update_user_meta($user_id, 'avatarurl', $avatarurl);
		}else{
			delete_user_meta($user_id, 'avatarurl');
		}
	}
}
add_action('personal_options_update','wpjam_edit_user_avatar_profile_update');
add_action('edit_user_profile_update','wpjam_edit_user_avatar_profile_update');


add_filter('user_profile_picture_description', '__return_empty_string');

	



