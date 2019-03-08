<?php
$action		= get_query_var('action');

if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
	ob_start('ob_gzhandler'); 
}else{
	ob_start(); 
}

$post_id	= get_query_var('p');
$views		= wpjam_get_post_total_views($post_id)+1;

if($action == 'feed'){
	wpjam_update_post_views($post_id, 'feed_views');
}elseif($action == 'post'){
	wpjam_update_post_views($post_id);
}

header("Content-Type: image/png");
$im = @imagecreate(120, 32) or die("Cannot Initialize new GD image stream");
$background	= imagecolorallocate($im, 0, 0, 0);
$text_color	= imagecolorallocate($im, 255, 0, 0);

if($views > 100000){
	$x	= 6;
}elseif($views > 10000){
	$x	= 10;
}elseif($views > 1000){
	$x	= 14;
}elseif($views > 100){
	$x	= 18;
}else{
	$x	= 18;
}

$font	= 5;
$y		= 8;

imagestring($im, $font, $x, $y,  $views.' views', $text_color);

imagepng($im);
imagedestroy($im);

exit;

	