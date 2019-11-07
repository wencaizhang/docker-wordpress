<?php
add_filter('manage_qrcode_posts_columns', function($columns){
	unset($columns['thumbnail']);
		
	return $columns;
},99);


add_filter('post_row_actions', function($actions, $post){
	
	if($post->post_status == 'publish'){
		wpjam_array_push($actions, ['qrcode'=>'<a href="http://qr.liantu.com/api.php?text='.urlencode(get_permalink($post->ID)).'">生成二维码</a>'], 'view');
	}

	return $actions;
},10,2);
