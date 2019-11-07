<?php
function wpjam_grabmp_ajax_response(){
	$action	= $_POST['page_action'];
	$data	= wp_parse_args($_POST['data']);

	if($action == 'submit'){
		
		$mp_url	= $data['mp_url'];

		if($mp_url){
			$mp_url		= remove_query_arg(array('chksm','scene','srcid','mpshare'),$mp_url);
			$mp_url		= str_replace('%3D', '=', $mp_url);
			$article	= weixin_parse_mp_article($mp_url);

			if(is_wp_error($article)){
				wpjam_send_json($article);
			}else{
				$post_content		= wpjam_strip_invalid_text($article['content']);

				/* $post_content	= preg_replace('/<img .*? data-src="(.*?)" .*?>/i', '[wximg]\1[/wximg]', $article['content']); */
				$post_content		= preg_replace('/data-src="(.*?)"/i', 'src="\1"', $post_content); 

				$post_content_preg	= preg_replace('/<iframe class="video_iframe" .*? data-src=".*vid=(.*?)&.*".*?><\/iframe>/i', '[qqv]https://v.qq.com/iframe/preview.html?vid=\1[/qqv]', $post_content);

				$post_content	= (is_null($post_content_preg))?$post_content:$post_content_preg;

				$post_content_preg	= preg_replace('/<iframe class="video_iframe" .*? data-src=".*vid=(.*?)".*?><\/iframe>/i', '[qqv]https://v.qq.com/iframe/preview.html?vid=\1[/qqv]', $post_content);

				$post_content	= (is_null($post_content_preg))?$post_content:$post_content_preg;

				$post_id = wp_insert_post(array(
					'post_title'	=> $article['title'],
					'post_excerpt'	=> $article['digest'],
					'post_content'	=> $post_content,
					'post_status'	=> 'draft'
				));

				if(is_wp_error($post_id)){
					wpjam_send_json($post_id);
				}elseif(!$post_id){
					//
				}else{
					update_post_meta( $post_id, 'weixin_url', $mp_url);
					
					if($thumb_url = $article['thumb_url']){

						$file_array = array();
						$file_array['name'] 	= md5($thumb_url).'.jpg';
						$file_array['tmp_name']	= download_url($thumb_url);
						
						$thumbnail_id = media_handle_sideload($file_array, $post_id, $article['title']);

						if(!is_wp_error($thumbnail_id)){
							set_post_thumbnail($post_id, $thumbnail_id);
						}
					}

					wpjam_send_json([
						'errcode'	=> 0,
						'edit_url'	=> admin_url('post.php?post='.$post_id.'&action=edit')
					]);
				}

				wpjam_send_json(['errcode'=>0]);
			}
		}else{
			wpjam_send_json([
				'errcode'	=> 'empty_mp_url',
				'errmsg'	=> '公众号链接为空'
			]);
		}	
	}
}

function wpjam_grabmp_page(){
	?>

	<h2>抓取图文</h2>

	<p>请输入公众号的图文链接，然后点击抓取：</p>

	<?php 

	wpjam_ajax_form([
		'fields'		=> ['mp_url'=>['title'=>'',	'type'=>'textarea',		'style'=>'max-width:600px;',	'rows'=>4]], 
		'action'		=> 'submit',
		'submit_text'	=> '抓取'
	]);
}

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('page_action_success', function(e, response){
			if(response.page_action == 'submit'){
				window.location.replace(response.edit_url);
			}
		});
	});
	</script>
	<?php
});