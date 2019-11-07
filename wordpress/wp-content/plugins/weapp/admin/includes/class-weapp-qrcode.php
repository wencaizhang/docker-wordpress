<?php
Class WEAPP_AdminQrcode extends WEAPP_Qrcode{
	public static function list($limit, $offset){
		self::Query()->where('appid', self::get_appid());

		return parent::list($limit, $offset);
	}

	public static function item_callback($item){
		$item['type']	= $item['type']?:'wxacode';

 		$media_id	= weapp()->create_qrcode(self::parse_args($item), $item['type']);
		$qrcode_url = weapp()->get_media_url($media_id, $item['type']);

		$item['qrcode']	= '<a class="thickbox" href="'.wpjam_get_thumbnail($qrcode_url).'" title="'.$item['name'].'"><img src="'.wpjam_get_thumbnail($qrcode_url,['width'=>160,'height'=>160,'mode'=>0]).'" width="80"></a>';

		if($item['type'] == 'wxacode'){
			$item['color']	= ($item['color'])?'<span style="color:'.$item['color'].'">'.$item['color'].'</span>':'自动配色';
		}else{
			$item['color']	= '';
		}

		return $item;
	}

	public static function get_fields($action_key='', $id=''){
		if($action_key == 'edit'){
			$fields 	= [
				'name'		=> ['title'=>'名称',		'type'=>'text'],
			];
		}else{
			$fields 	= [
				'name'		=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>true],
				'type'		=> ['title'=>'类型',		'type'=>'select',	'show_admin_column'=>true,	'options'=>['wxacode'=>'小程序码', 'qrcode'=>'二维码']],
				'path'		=> ['title'=>'路径',		'type'=>'text',		'show_admin_column'=>true],
				'width'		=> ['title'=>'宽度',		'type'=>'number',	'show_admin_column'=>true, 	'value'=>430],
				'color'		=> ['title'=>'线条颜色',	'type'=>'color',	'show_admin_column'=>true],
				'qrcode'	=> ['title'=>'二维码',	'type'=>'view',		'show_admin_column'=>'only'],
				'appid'		=> ['title'=>'appid',	'type'=>'hidden',	'value'=>weapp_get_appid()],
			];
		}

		return $fields;
	}

	public static function list_page(){
		?>
		<script type="text/javascript">
		jQuery(function ($) {
			$('body').on('change', 'select#type', function () {
				var selected = $(this).val();
				if (selected == 'wxacode') {
					$('#tr_color').show();
				} else if (selected == 'qrcode') {
					$('#tr_color').hide();
				}
			});

			$('body select#type').change();
		});
		</script>
		<?php
	}
}