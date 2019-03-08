<?php
add_filter('wpjam_thumbnail',function ($img_url, $args){
	return wpjam_get_aliyun_oss_thumbnail($img_url, $args);
},10,2);


//使用七牛缩图 API 进行裁图
function wpjam_get_aliyun_oss_thumbnail($img_url, $args=array()){
	extract(wp_parse_args($args, array(
		'crop'		=> 1,
		'width'		=> 0,
		'height'	=> 0,
		'retina'	=> 1,
		'mode'		=> null,
		'format'	=> '',
		'interlace'	=> 0,
		'quality'	=> 0,
	)));

	if($mode === null){
		$crop	= $crop && ($width && $height);	// 只有都设置了宽度和高度才裁剪
		$mode	= $crop?',m_fill':'';
	}
	
	$width		= intval($width)*$retina;
	$height		= intval($height)*$retina;

	if($width || $height){
		$arg	= 'x-oss-process=image/resize'.$mode;

		if($width)		$arg .= ',w_'.$width;
		if($height) 	$arg .= ',h_'.$height;

		if(strpos($img_url, 'x-oss-process=image/resize')){
			$img_url	= preg_replace('/x-oss-process=image\/resize(.*?)#/', '', $img_url);
		}
		
		$img_url	= add_query_arg( array($arg => ''), $img_url );
		$img_url	= $img_url.'#';
	}

	return $img_url;
}