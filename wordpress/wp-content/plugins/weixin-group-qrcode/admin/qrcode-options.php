<?php

wpjam_register_post_option('basic_box',	[
	'title'		=> '设置',
	'post_type'	=> 'qrcode',
	'fields'	=> [
		'qrcodes'		=> ['title'=>'二维码图',	'type'=>'mu-img',	'item_type'=>'url'],
		'per_qrcode'	=> ['title'=>'展示次数',	'type'=>'number',	'value'=>120,	'class'=>'all-options',	'description'=>'每张二维码展示次数。<br />一般来说 120 次浏览可以让 70-90 人加群，你可以根据自己的用户调性调整。<br />*注意每次修改是从头开始计算。'],
		'no_qrcode_set'	=> ['title'=>'用完之后',	'type'=>'fieldset',	'fields'=>[
			'no_qrcode'		=> ['title'=>'',	'type'=>'select',	'options'=>['rollback'=>'从头开始显示二维码',	'error'=>'显示一段说明文本']],
			'no_qrcode_text'=> ['title'=>'',	'type'=>'textarea',	'value'=>'加群已完成！']
		]],
	]
]);

add_action('admin_head', function(){
?>
<script type="text/javascript">
	jQuery(function($){
		$('textarea#excerpt').attr('placeholder', '请输入群说明，不填则不显示');

		$('div#postexcerpt h2 span').html('群说明');

		$('select#no_qrcode').change(function () {
			if($(this).val() == 'error'){
				$('#div_no_qrcode_text').show();
			}else{
				$('#div_no_qrcode_text').hide();
			}
		});

		$('select#no_qrcode').change();
	});
	</script>
<?php
});