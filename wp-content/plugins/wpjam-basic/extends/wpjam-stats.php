<?php
/*
Plugin Name: 统计代码
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 自动添加百度统计和 Google 分析代码。
Version: 1.0
*/
add_action('wp_head', function (){

	if(is_preview()) return;

	$remove_query_args	= array('from','isappinstalled','weixin_access_token','weixin_refer');
	$stats_page_url		= remove_query_arg($remove_query_args,$_SERVER["REQUEST_URI"]);
	$stats_page_url		= (is_404())?'/404'.$stats_page_url:$stats_page_url;
	$stats_page_url		= ($stats_page_url == $_SERVER["REQUEST_URI"])?'':$stats_page_url;
	$stats_page_url 	= apply_filters('wpjam_stats_page_url', $stats_page_url);
	?>
	<?php if($google_analytics_id = wpjam_basic_get_setting('google_analytics_id')){ ?>
	<!-- Google Analytics Begin-->
	<?php if(wpjam_basic_get_setting('google_universal')){ ?>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', '<?php echo $google_analytics_id;?>', 'auto');
	ga('require', 'displayfeatures');
	<?php if($stats_page_url){?>
	ga('send', 'pageview', '<?php echo $stats_page_url; ?>');
	<?php }else{?>
	ga('send', 'pageview');
	<?php } ?>
	<?php if(!empty($_GET['from']) && isset($_GET['isappinstalled'])){ ?>
	ga('send', 'event', 'weixin', 'from', '<?php echo $_GET['from'];?>');
	<?php } ?>
	</script>
	<?php } else { ?>
	<script type="text/javascript">
	var _gaq = _gaq || [];
	var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
	_gaq.push(['_require', 'inpage_linkid', pluginUrl]);
	_gaq.push(['_setAccount', '<?php echo $google_analytics_id;?>']);
	<?php if($stats_page_url){?>
	_gaq.push(['_trackPageview', '<?php echo $stats_page_url; ?>']);
	<?php }else{?>
	_gaq.push(['_trackPageview']);
	<?php } ?>
	_gaq.push(['_trackPageLoadTime']);
	(function() {
		var ga = document.createElement('script');
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		ga.setAttribute('async', 'true');
		document.getElementsByTagName('head')[0].appendChild(ga);
	})();
	</script>
	<?php } ?>
	<!-- Google Analytics End -->
	<?php } ?>

	<?php if($baidu_tongji_id = wpjam_basic_get_setting('baidu_tongji_id')){ ?>
	<!-- Baidu Tongji Start -->
	<script type="text/javascript">
	var _hmt = _hmt || [];
	<?php if($stats_page_url){?>
	_hmt.push(['_setAutoPageview', false]);
	_hmt.push(['_trackPageview', '<?php echo $stats_page_url; ?>']);
	<?php }else{?>
	_hmt.push(['_trackPageview']);
	<?php } ?>
	<?php if(!empty($_GET['from']) && isset($_GET['isappinstalled'])){ ?>
	_hmt.push(['_trackEvent', 'weixin', 'from', '<?php echo $_GET['from'];?>']);
	<?php } ?>
	(function() {
	var hm = document.createElement("script");
	hm.src = "//hm.baidu.com/hm.js?<?php echo $baidu_tongji_id;?>";
	hm.setAttribute('async', 'true');
	document.getElementsByTagName('head')[0].appendChild(hm);
	})();
	</script>
	<!-- Baidu Tongji  End -->
	<?php } 

}, 11);