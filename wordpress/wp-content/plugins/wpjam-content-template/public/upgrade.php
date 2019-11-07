<?php
function wpjam_content_template_upgrade(){
	$current	= 2;
	$version	= wpjam_get_setting('wpjam-content-template', 'version') ?: 0;

	if($version >= $current){
		return;
	}
	
	wpjam_update_setting('wpjam-content-template', 'version', $current);
	
	if($version < 2){
		$_query = new WP_Query([
			'posts_per_page'	=> -1,
			'post_type'			=> 'template',
			'post_status'		=> ['publish', 'pending', 'draft', 'future', 'trash'],
		]);

		if($_query->posts){
			foreach($_query->posts as $post){
				$post_id		= $post->ID;
				$template_type	= get_post_meta($post_id, '_template_type', true);
				if($template_type == 'table'){
					$table_content	= get_post_meta($post_id, '_table_content', true);
					$post_content	= $post->post_content;

					$post_attr	= [
						'ID'			=> $post_id,
						'post_excerpt'	=> $post_content,
						'post_content'	=> $table_content ? maybe_serialize($table_content) : '',
					];

					wp_update_post($post_attr);
				}
			}
		}

		wp_reset_postdata();
	}
}

wpjam_content_template_upgrade();