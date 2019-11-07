<?php
function wpjam_qiniu_get_setting($setting_name){
	return wpjam_cdn_get_setting($setting_name);
}

add_filter('wpjam_thumbnail',function ($img_url, $args){
	return wpjam_get_qiniu_thumbnail($img_url, $args);
},10,2);



//使用七牛缩图 API 进行裁图
function wpjam_get_qiniu_thumbnail($img_url, $args=array()){
	extract(wp_parse_args($args, array(
		'crop'		=> 1,
		'width'		=> 0,
		'height'	=> 0,
		'mode'		=> null,
		'format'	=> '',
		'interlace'	=> 0,
		'quality'	=> 0,
	)));

	if($height > 10000){
		$height = 0;
	}

	if($width > 10000){
		$height = 0;
	}

	if($mode === null){
		$crop	= $crop && ($width && $height);	// 只有都设置了宽度和高度才裁剪
		$mode	= $mode?:($crop?1:2);
	}

	$format		= $format?:(wpjam_qiniu_get_setting('webp')?'webp':'');
	$interlace	= $interlace?:(wpjam_qiniu_get_setting('interlace')?1:0);
	$quality	= $quality?:(wpjam_qiniu_get_setting('quality'));

	// if($width || $height || $format || $interlace || $quality){
	if($width || $height){
		$arg	= 'imageView2/'.$mode;

		if($width)		$arg .= '/w/'.$width;
		if($height) 	$arg .= '/h/'.$height;
		if($format)		$arg .= '/format/'.$format;
		if($interlace)	$arg .= '/interlace/'.$interlace;
		if($quality)	$arg .= '/q/'.$quality;

		if(strpos($img_url, 'imageView2/')){
			$img_url	= preg_replace('/imageView2\/(.*?)#/', '', $img_url);
		}

		if(strpos($img_url, 'watermark/')){
			$img_url	= $img_url.'|'.$arg;
		}else{
			$img_url	= add_query_arg( array($arg => ''), $img_url );
		}

		if(!empty($args['content'])){
			$img_url	= wpjam_get_qiniu_watermark($img_url);
		}

		$img_url	= $img_url.'#';
	}

	return $img_url;
}

// 获取七牛水印
function wpjam_get_qiniu_watermark($img_url, $args=array()){
	extract(wp_parse_args($args, array(
		'watermark'	=> '',
		'dissolve'	=> '',
		'gravity'	=> '',
		'dx'		=> 0,
		'dy'		=> 0,
	)));

	$watermark	= $watermark?:wpjam_qiniu_get_setting('watermark');
	if($watermark){
		$watermark	= str_replace(array('+','/'),array('-','_'),base64_encode($watermark));
		$dissolve	= $dissolve?:(wpjam_qiniu_get_setting('dissolve')?:100);
		$gravity	= $gravity?:(wpjam_qiniu_get_setting('gravity')?:'SouthEast');
		$dx			= $dx?:(wpjam_qiniu_get_setting('dx')?:10);
		$dy			= $dy?:(wpjam_qiniu_get_setting('dy')?:10);

		$watermark	= 'watermark/1/image/'.$watermark.'/dissolve/'.$dissolve.'/gravity/'.$gravity.'/dx/'.$dx.'/dy/'.$dy;

		if(strpos($img_url, 'imageView2')){
			$img_url = $img_url.'|'.$watermark;
		}else{
			$img_url = add_query_arg(array($watermark=>''), $img_url);
		}
	}

	return $img_url;
}

function wpjam_get_qiuniu_timestamp($img_url){
	$t		= dechex(time()+HOUR_IN_SECONDS*6);	
	$key	= '';
	$path	= parse_url($img_url, PHP_URL_PATH);
	$sign	= strtolower(md5($key.$path.$t));

	return add_query_arg(array('sign' => $sign, 't'=>$t), $img_url);
}

function wpjam_get_qiniu_image_info($img_url){
	$img_url 	= add_query_arg(array('imageInfo'=>''),$img_url);
	
	$response	= wp_remote_get($img_url);
	if(is_wp_error($response)){
		return $response;
	}

	$response	= json_decode($response['body'], true);

	if(isset($response['error'])){
		return new WP_Error('error', $response['error']);
	}

	return $response;
}