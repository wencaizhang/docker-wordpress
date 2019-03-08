<?php
add_filter('wpjam_cdn_setting', function(){
	$coupon_div = '
	<div id="qiniu_coupon" style="display:none;">
	<p>简单说使用<strong>WordPress插件用户专属的优惠码</strong>“<strong style="color:red;">d706b222</strong>”充值，一次性充值2000元及以内99折，2000元以上则95折</strong>，建议至少充值2001元。详细使用流程：</p>
	<p>1. 登陆<a href="http://wpjam.com/go/qiniu" target="_blank">七牛开发者平台</a></p>
	<p>2. 然后点击“充值”，进入充值页面</p>
	<p><img srcset="'.WPJAM_BASIC_PLUGIN_URL.'/static/qiniu-coupon.png 2x" src="'. WPJAM_BASIC_PLUGIN_URL.'/static/qiniu-coupon.png" alt="使用七牛优惠码" /></p>
	<p>3. 点击“使用优惠码”，并输入优惠码“<strong><span style="color:red;">d706b222</span></strong>”，点击“使用”。</p>
	<p>4. 输入计划充值的金额，点击“马上充值”，进入支付宝页面，完成支付。</p>
	<p>5. 完成支付后，可至财务->>财务概况->>账户余额 查看实际到账金额。</p>
	</div>';

	$detail = '
	<p>UCloud 用户：每月20GB对象存储下载流量，注册<a href="http://wpjam.com/go/ucloud/" target="_blank">UCloud</a>账号7天内，使用优惠券「<span style="color:red; font-weight:bold;">PW17JAM</span>」可以免费兑换100GBCDN流量，UCloud UFile<strong><a href="https://blog.wpjam.com/m/ucloud-ufile-cdn/" target="_blank">详细使用指南</a></strong>。</p>
	<p>七牛云存储用户：充值可以使用插件用户专属的优惠码：「<span style="color:red; font-weight:bold;">d706b222</span>」（<strong><a title="如何使用七牛云存储的优惠码" class="thickbox" href="#TB_inline?width=600&height=480&inlineId=qiniu_coupon">详细使用指南</a></strong>），七牛云存储<strong><a href="https://blog.wpjam.com/m/qiniu-cdn/" target="_blank">详细使用指南</a></strong>。</p>
	<p>阿里云OSS用户：请点击这里注册和申请<a href="http://wpjam.com/go/aliyun/" target="_blank">阿里云</a>可获得代金券，阿里云OSS<strong><a href="https://blog.wpjam.com/m/aliyun-oss-cdn/" target="_blank">详细使用指南</a></strong>。</p>
	<p>腾讯云COS用户：请点击这里注册和申请<a href="http://wpjam.com/go/qcloud/" target="_blank">腾讯云</a>可获得优惠券。</p>
	'
	.$coupon_div;

	$cdn_fields		= [
		'cdn_name'	=> ['title'=>'云存储',	'type'=>'select',	'options'=>[''=>' ','ucloud'=>'UCloud UFile','qiniu'=>'七牛云存储','aliyun_oss'=>'阿里云OSS','qcloud_cos'=>'腾讯云COS']],
		'host'		=> ['title'=>'CDN域名',	'type'=>'url',		'description'=>'设置为CDN云存储提供的测试域名或者在云存储绑定的域名。<strong>注意要域名前面要加上 http://或https://</strong>。'],
		'detail'	=> ['title'=>'其他说明',	'type'=>'view',		'value'=>$detail],
	];

	$local_fields = [		
		'exts'		=> ['title'=>'扩展名',	'type'=>'text',		'value'=>'png|jpg|jpeg|gif|ico',	'description'=>'设置要缓存静态文件的扩展名，请使用 | 分隔开，|前后都不要留空格。'],
		'dirs'		=> ['title'=>'目录',		'type'=>'text',		'value'=>'wp-content|wp-includes',	'description'=>'设置要缓存静态文件所在的目录，请使用 | 分隔开，|前后都不要留空格。'],
		'local'		=> ['title'=>'本地域名',	'type'=>'url',		'value'=>home_url(),				'description'=>'将该域名也填入CDN的镜像源中。'],
		'locals'	=> ['title'=>'其他域名',	'type'=>'mu-text',	'item_type'=>'url'],
	];

	global $wp_rewrite;

	$remote_fields	= [];

	if($wp_rewrite->using_mod_rewrite_permalinks() && extension_loaded('gd')){
		$remote_fields = [
			'remote'	=> ['title'=>'保存远程图片',	'type'=>'checkbox',	'description'=>'自动将远程图片镜像到云存储。'],
			'exceptions'=> ['title'=>'例外',			'type'=>'textarea',	'class'=>'regular-text',	'description'=>'如果远程图片的链接中包含以上字符串或者域名，就不会被保存并镜像到云存储。'],
		];
	}elseif(!got_url_rewrite()){
		$remote_fields = [
			'remote'	=> ['title'=>'保存远程图片',	'type'=>'view',	'value'=>'你服务器不支持伪静态']
		];
	}elseif(!$wp_rewrite->using_permalinks()){
		$remote_fields = [
			'remote'	=> ['title'=>'保存远程图片',	'type'=>'view',	'value'=>'你服务器没有开启固定链接']
		];
	}elseif($wp_rewrite->using_index_permalinks()){
		$remote_fields = [
			'remote'	=> ['title'=>'保存远程图片',	'type'=>'view',	'value'=>'你的固定链接中有 index.php']
		];
	}elseif(!extension_loaded('gd')){
		$remote_fields = [
			'remote'	=> ['title'=>'保存远程图片',	'type'=>'view',	'value'=>'PHP没有加载GD扩展库']
		];
	}

	$image_fields	= [
		'interlace'	=> ['title'=>'渐进显示',	'type'=>'checkbox',	'description'=>'是否JPEG格式图片渐进显示。'],
		'quality'	=> ['title'=>'图片质量',	'type'=>'number',	'class'=>'all-options',	'description'=>'<br />1-100之间图片质量，七牛默认为75。','mim'=>0,'max'=>100]
	];

	$watermark_options = [
		'SouthEast'	=> '右下角',
		'SouthWest'	=> '左下角',
		'NorthEast'	=> '右上角',
		'NorthWest'	=> '左上角',
		'Center'	=> '正中间',
		'West'		=> '左中间',
		'East'		=> '右中间',
		'North'		=> '上中间',
		'South'		=> '下中间',
	];

	$watermark_fields = [
		'watermark'	=> ['title'=>'水印图片',	'type'=>'image',	'description'=>''],
		'disslove'	=> ['title'=>'透明度',	'type'=>'number',	'class'=>'all-options',	'description'=>'<br />透明度，取值范围1-100，缺省值为100（完全不透明）','min'=>0,	'max'=>100],
		'gravity'	=> ['title'=>'水印位置',	'type'=>'select',	'options'=>$watermark_options],
		'dx'		=> ['title'=>'横轴边距',	'type'=>'number',	'class'=>'all-options',	'description'=>'<br />横轴边距，单位:像素(px)，缺省值为10'],
		'dy'		=> ['title'=>'纵轴边距',	'type'=>'number',	'class'=>'all-options',	'description'=>'<br />纵轴边距，单位:像素(px)，缺省值为10'],
	];

	$sections = [];
	
	$sections['cdn']		= ['title'=>'CDN设置',		'fields'=>$cdn_fields,		'summary'=>'<p>*使用之前，请一定认真阅读 WPJAM Basic 的<a href="https://blog.wpjam.com/m/wpjam-basic-cdn/" target="_blank">CDN 加速的使用说明</a>，这里几乎可以解决你所有的问题。</p>'];
	$sections['local']		= ['title'=>'本地设置',		'fields'=>$local_fields];

	

	
	$sections['remote']		= ['title'=>'远程图片设置',	'fields'=>$remote_fields,	'summary'=>'<p>*自动将远程图片镜像到云存储需要你的博客支持固定链接。<br />*如果前面设置的静态文件域名和博客域名不一致，该功能也可能出问题。<br />*远程 GIF 图片保存到云存储将失去动画效果，所以目前不支持 GIF 图片。</p>'];
	
	$sections['image']		= ['title'=>'图片设置',		'fields'=>$image_fields];
	$sections['watermark']	= ['title'=>'水印设置',		'fields'=>$watermark_fields];
	

	if(is_network_admin()){
		unset($sections['local']['fields']['local']);
		unset($sections['watermark']['fields']['watermark']);
	}

	$field_validate	= function($value){
		flush_rewrite_rules();
		return $value;
	};

	return compact('sections', 'field_validate');
});


add_action('admin_head',function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		function wpjam_cdn_switched(){
			var sections 	= ['image','watermark','remote'];
			var cdn_name	= $('select#cdn_name').val();
			
			$.each(sections, function(index,section){
				$('#tab_title_'+section).hide();
			});

			if(cdn_name){
				$('#tab_title_remote').show();

				if(cdn_name == 'qiniu'){
					$('#tab_title_image').show();
					$('#tab_title_watermark').show();
				}
			}
		}

		wpjam_cdn_switched();

		$('select#cdn_name').change(function(){
			wpjam_cdn_switched();
		});
	
		// $('body').on('option_action_success', function(e, response){
		// 	wpjam_cdn_switched();
		// });
	});
	</script>
	<?php
});