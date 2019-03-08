<?php
global $post;

if(empty(CDN_NAME)){
	wp_die('你没开启云存储','你没开启云存储', ['response'=>404]);
}

$post 	= get_post(get_query_var('p'));
$remote	= get_query_var(CDN_NAME);

if(empty($remote)){
	wp_die('文件名不能为空','文件名不能为空', ['response'=>404]);
}

if(empty($post)){
	wp_die('文章不存在','文章不存在',array( 'response' => 404 ));
}

$img_info	= pathinfo($remote);
$filename	= $img_info['filename'];
$extension	= $img_info['extension'];

$url = '';
if (preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post->post_content), $matches)) {
	foreach ($matches[1] as $image_url) {
		if($filename == md5($image_url)){
			$url = $image_url;
			break;
		}
	}
}

if(!$url){
	wp_die('文章没有该图片','文章没有该图片',array( 'response' => 404 ));
}

if(isset($_GET['url']) && $_GET['url']){
	echo $url;
	exit;
}

switch ($extension) {
	case 'jpg':
		header('Content-Type: image/jpeg');
		//$img = imagecreatefromjpeg($url);
		$img = imagecreatefromstring(file_get_contents($url));
		imagejpeg($img,null,100);
		break;

	case 'png':
		header("Content-Type: image/png");
		//imagepng(imagecreatefromstring(get_url_contents('http://blog.wpjam.com/thumb/'.$_GET['p'].'.png')));
		$img = imagecreatefrompng($url);
		$background = imagecolorallocate($img, 0, 0, 0);
		imagecolortransparent($img, $background);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		imagepng($img);
		break;

	case 'gif':
		header('Content-Type: image/gif');
		$img = imagecreatefromgif($url);
		// $background = imagecolorallocate($img, 0, 0, 0);
		// imagecolortransparent($img, $background);
		imagegif($img);
		break;
	
	default:
		# code...
		break;		
}

// $image = wp_remote_get(trim($url));

// if(is_wp_error($image)){
// 	wp_die('原图不存在','原图不存在',array( 'response' => 404 ));
// }else{
// 	header("HTTP/1.1 200 OK");
// 	header("Content-Type: image/jpeg");
// 	imagejpeg(imagecreatefromstring($image['body']),NULL,100);
// }