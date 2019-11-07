<?php
trait WPJAM_WeappPageTrait{
	public static function validate_data($data){
		if($data['weapp_page'] == 'mini_program' || $data['weapp_page'] == 'web_view' || $data['weapp_page'] == ''){
			return $data;
		}

		$data['path']	= apply_filters('weapp_page_generate_path', $data['weapp_page'], $data);

		if(is_wp_error($data['path'])){
			return $data['path'];
		}

		return $data;
	}

	public static function get_weapp_page_fields($type=''){
		return apply_filters('weapp_page_fields', [], $type);
	}

	public static function parse_item($item){
		$item['weapp_page'] = $item['weapp_page']??'';
		
		if($item['weapp_page'] == 'mini_program'){
			$item['path']	= '跳转小程序：<br />'.'AppID：'.$item['appid'].'<br />路径：'.$item['path'];
		}elseif($item['weapp_page'] == 'web_view'){
			$item['path']	= '公众号文章：<br /><a href="'.$item['src'].'">'.$item['src'].'</a>';
		}elseif($item['weapp_page'] == ''){
			$item['path']	= '只展示不跳转';
		}else{
			$item['path']	= '小程序内跳转：<br/>'.$item['path'];
		}
		
		return $item;
	}

	public static function weapp_page_change(){
		?>
		<script type="text/javascript">
		jQuery(function($){
			$('body select#weapp_page').change();
		});
		</script>
		<?php
	}

	public static function weapp_page_script(){
		do_action('weapp_page_script');
	}

	public static function generate_qrcode_html($data, $vendor='', $appid=''){
		$data	= self::validate_data($data);

		if(is_wp_error($data)){
			return $data;
		}

		$data['width']	= $data['width']??430;
		$data['type']	= $data['type']??'wxacode';

		if($vendor){
			$data['path']	= add_query_arg(['vendor'=>$vendor], $data['path']);
		}

		$qrcode_url		= weapp_create_qrcode($data, 'url', $appid);

		if(is_wp_error($qrcode_url)){
			return $qrcode_url;
		}

		$qrcode_url	= wpjam_get_thumbnail($qrcode_url);

		$width		= intval($data['width'] / 2);
		$width		= ($width > 520)?520:$width;
		$min_height	= $width + 80;

		return '
		<div style="min-height:'.$min_height.'px;">
			<h2>小程序路径</h2>
			<p><strong>'.$data['path'].'</strong></p><p><img src="'.$qrcode_url.'" style="max-width:520px; width:'.$width.'px;" /></p>
		</div>
		';
	}
}