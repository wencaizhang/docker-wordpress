<html lang="zh-cmn-Hans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <title><?php the_title(); ?></title>
    <style type="text/css">
    	body, div#qrcode{margin:0; padding:0;}
    	div#qrcode p{margin:20px;}
    	div#qrcode img{max-width:100%; margin-bottom: 20px;}
    </style>
</head>
<body>
	<div id="qrcode">
	<?php
	$post_id	= get_the_ID();
	$qrcodes 	= get_post_meta($post_id, 'qrcodes', true);
	$count		= count($qrcodes);

	$per_qrcode	= get_post_meta($post_id, 'per_qrcode', true) ?: 120;
	$post_views	= wpjam_get_post_views($post_id);

	if(isset($_COOKIE['weixin_group_'.$post_id]) && (!isset($_GET['debug']))){
		$no = $_COOKIE['weixin_group_'.$post_id];
	}else{
		$no		= intval($post_views/$per_qrcode);

		wpjam_set_cookie('weixin_group_'.$post_id, $no, time()+YEAR_IN_SECONDS);

		wpjam_update_post_views($post_id);
	}

	if($count > $no){
		$qrcode	= $qrcodes[$no];
	}else{
		$no_qrcode	= get_post_meta($post_id, 'no_qrcode', true) ?: 'rollback';
		if($no_qrcode == 'rollback'){
			$no	= $no % $count;

			$qrcode	= $qrcodes[$no];
		}
	}

	if($qrcode){
		echo '<img src="'.$qrcodes[$no].'">';

		if($excerpt = get_the_excerpt()){
			echo wpautop($excerpt);
		}
	}else{
		if($no_qrcode_text = get_post_meta($post_id, 'no_qrcode_text', true)){
			echo wpautop($no_qrcode_text);
		}
	}

	echo wpautop('已有'.$post_views.'人加群了！');
	// echo wpautop('$no'.$no);
	// echo wpautop('count'.$count);
	?>
	</div>
</body>
</html>

