</div>
<footer class="footer-area">
    <div class="footer-socials">
        <div class="container">
		<?php 
			if ( is_home() && wpjam_theme_get_setting('foot_link') ) {
				echo '<ul class="link-body clearfix">';
				echo wp_list_bookmarks('title_li=&categorize=0');
				echo '</ul>';
			}
		?>
        </div>
    </div>
    <div class="container">
        <div class="xintheme-footer clearfix">
				<?php 
					echo 'Copyright '.date('Y').'.&nbsp;&nbsp;All Rights Reserved.&nbsp;&nbsp;Powered By&nbsp;&nbsp;<a href="http://www.xintheme.com" target="_blank">XinTheme</a>&nbsp;&nbsp;+&nbsp;&nbsp;<a href="https://blog.wpjam.com/" target="_blank">WordPress 果酱</a>';
					echo '<p class="footer-text"><a rel="nofollow" target="_blank" href="http://www.miibeian.gov.cn/">'.wpjam_theme_get_setting('footer_icp').'</a></p>';
				?>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>