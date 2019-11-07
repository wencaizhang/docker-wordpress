<div class="footer">
	<div class="container w-container">
		<div class="footer-text">
			<?php
			if( $copyright = wpjam_theme_get_setting('foot_copyright') ){
				echo $copyright;
			}else{ 
			
			echo 'Copyright'. date('Y').'. All Rights Reserved.&nbsp;';

			}?>
			
			<?php if( wpjam_theme_get_setting('foot_timer') ) {?>页面生成时间：<?php timer_stop(1);?> 秒.<?php } ?>
			
			<?php if( $icp = wpjam_theme_get_setting('footer_icp') ) {?>

				&nbsp;<a rel="nofollow" target="_blank" href="http://www.beian.miit.gov.cn/"><?php echo $icp;?></a>

			<?php } ?>
		</div>
		
		<div class="_2 footer-text">
			Powered by
			<a href="http://www.xintheme.com" target="_blank">XinTheme</a> + 
			<a href="https://blog.wpjam.com/" target="_blank">WordPress 果酱</a>
		</div>
		<?php wp_footer(); ?>
	</div>
</div>
<div class="gotop-wrapper">
	<a href="javascript:;" class="fixed-gotop gotop"></a>
</div>
</body>
</html>