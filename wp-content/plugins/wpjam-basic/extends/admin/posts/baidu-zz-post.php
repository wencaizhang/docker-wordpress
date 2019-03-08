<?php
// 直接新增
add_action('save_post', function($post_id, $post, $update){
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
		return;

	if(!current_user_can('edit_post', $post_id)) 
		return;

	if(!$update && $post->post_status == 'publish'){

		$baidu_zz	= $_POST['baidu_zz'] ?? false;

		if($baidu_zz){
			$post_link	= apply_filters('baiduz_zz_post_link', get_permalink($post_id), $post_id);

			$original	= $_POST['xzh_original'] ?? false;
			$update		= false;

			wpjam_notify_baidu_zz($post_link, $update);
			wpjam_notify_xzh($post_link, compact('update','original'));
		}
	}
}, 10, 3);

// 修改文章
add_action('post_updated', function($post_id, $post_after, $post_before){
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
		return;

	if(!current_user_can('edit_post', $post_id)) 
		return;

	if($post_after->post_status == 'publish'){
		$baidu_zz	= $_POST['baidu_zz'] ?? false;

		if($baidu_zz){
			$post_link	= apply_filters('baiduz_zz_post_link', get_permalink($post_id), $post_id);
		
			$original	= $_POST['xzh_original'] ?? false;
			$update		= false;

			// if($post_before->post_status == 'publish'){
			// 	$update	= true;
			// }

			wpjam_notify_baidu_zz($post_link, $update);
			wpjam_notify_xzh($post_link, compact('update','original'));
		}
	}
}, 10, 3);


add_action('post_submitbox_misc_actions', function (){ ?>
	<div class="misc-pub-section baidu-zz">

		<input type="checkbox" name="baidu_zz" id="baidu_zz" value="1">
		<label for="baidu_zz">提交到百度站长</label>

		<?php if(wpjam_get_setting('xzh', 'original')){ ?>
		<br />
		<span id="span_xzh_original" style="display: none;">
		<input type="checkbox" name="xzh_original" value="1">
		<label for="xzh_original">百度原创保护</label>
		</span>
		<?php } ?>

	</div>
<?php });

add_action('admin_head', function(){
	if(wpjam_get_setting('xzh', 'original')){ 
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('#baidu_zz').on('change', function(e) {
			if($(this).is(':checked')){
				$('#span_xzh_original').show();
			}else{
				$('#span_xzh_original').hide();
			}
		});
	});
	</script>
	<?php
	}
});