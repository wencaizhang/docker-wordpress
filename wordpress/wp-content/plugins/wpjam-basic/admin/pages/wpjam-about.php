<?php
function wpjam_basic_about_page(){
	?>
	<h1>关于WPJAM</h1>

	<style type="text/css">
		.card a{text-decoration: none;}
		table#jam_plugins th{padding-left: 2em;}
		table#jam_plugins td{padding-right: 2em;}
		table#jam_plugins th p, table#jam_plugins td p {margin: 6px 0;}
	</style>

	<div style="max-width: 900px;">
		<?php
		$jam_plugins = get_transient('about_jam_plugins');

		if($jam_plugins === false){
			$api = 'http://jam.wpweixin.com/api/template/get.json?id=5644';

			$response	= wpjam_remote_request($api);

			if(!is_wp_error($response)){
				$jam_plugins	= $response['template']['table']['content'];
				set_transient('about_jam_plugins', $jam_plugins, DAY_IN_SECONDS );
			}
		}
		?>

		<table id="jam_plugins" class="widefat striped" style="margin-top:20px; width: 520px; float: left; margin-right: 20px;">
			<tbody>
			<tr>
				<th colspan="2">
					<h2>WPJAM 插件</h2>
					<p>加入<a href="http://97866.com/s/zsxq/">「WordPress果酱」知识星球</a>即可下载：</p>
				</th>
			</tr>
			<?php foreach($jam_plugins as $jam_plugin){ ?>
				<tr>
					<th style="width: 100px;"><p><strong><a href="<?php echo $jam_plugin['i2']; ?>"><?php echo $jam_plugin['i1']; ?></a></strong></p></th>
					<td><?php echo wpautop($jam_plugin['i3']); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
			

		<div class="card" style="max-width: 320px; float: left; margin-top:20px;">
			
			<h2>WPJAM Basic</h2>

			<p><strong><a href="http://blog.wpjam.com/project/wpjam-basic/">WPJAM Basic</a></strong> 是 <strong><a href="http://blog.wpjam.com/">我爱水煮鱼</a></strong> 的 Denis 开发的 WordPress 插件。</p>

			<p>WPJAM Basic 除了能够优化你的 WordPress ，也是 「WordPress 果酱」团队进行 WordPress 二次开发的基础。</p>

			<p>为了方便开发，WPJAM Basic 使用了最新的 PHP 7.2 语法，所以要使用该插件，需要你的服务器的 PHP 版本是 7.2 或者更高。</p>

			<p>我们开发所有插件都需要<strong>首先安装</strong> WPJAM Basic，其他功能组件将以扩展的模式整合到 WPJAM Basic 插件一并发布。</p>

		</div>

		<div class="card" style="max-width: 320px; float: left; margin-top:20px;">
			
			<h2>WPJAM 优化</h2>

			<p>网站优化首先依托于强劲的服务器支撑，这里强烈建议使用<a href="https://wpjam.com/go/aliyun/">阿里云</a>或<a href="https://wpjam.com/go/qcloud/">腾讯云</a>。</p>
			
			<p>更详细的 WordPress 优化请参考：<a href="https://blog.wpjam.com/article/wordpress-performance/">WordPress 性能优化：为什么我的博客比你的快</a>。</p>

			<p>我们也提供专业的 <a href="https://blog.wpjam.com/article/wordpress-optimization/">WordPress 性能优化服务</a>。</p>

		</div>

	</div>

	<?php 
}