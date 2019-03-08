<?php
add_filter('wpjam_thumbnail',function ($img_url, $args){
	return wpjam_get_ucloud_thumbnail($img_url, $args);
},10,2);


//使用七牛缩图 API 进行裁图
function wpjam_get_ucloud_thumbnail($img_url, $args=array()){
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
	
	$width		= intval($width)*$retina;
	$height		= intval($height)*$retina;

	if($width || $height){
		$arg['iopcmd']	= 'thumbnail';

		if($width && $height){
			$arg['type']	= 13;
			$arg['height']	= $height;
			$arg['width']	= $width;
		}elseif($width){
			$arg['type']	= 4;
			$arg['width']	= $width;
		}elseif($height){
			$arg['type']	= 5;
			$arg['height']	= $height;
		}

		if(strpos($img_url, 'iopcmd=thumbnail') === false){
			$img_url	= add_query_arg($arg, $img_url );
			$img_url	= $img_url.'#';
		}
	}

	return $img_url;
}