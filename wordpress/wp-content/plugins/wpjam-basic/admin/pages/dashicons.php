<?php

function wpjam_dashicons_page(){
	?>
	<h1>Dashicons</h1>

	<p>在 WordPress 后台<a href="https://blog.wpjam.com/m/using-dashicons-in-wordpress-admin/" target="_blank">如何使用 Dashicons</a>。</p>

	<style type="text/css">
	h2{
		max-width: 800px;
		margin:40px 0 20px 0;
		padding-bottom: 20px;
		clear: both;
		border-bottom: 1px solid #ccc;
	}

	div.wpjam-dashicons{
		max-width: 800px;
		float: left;
	}

	div.wpjam-dashicons p{
		float: left;
		margin:0px 10px 10px 0;
		padding: 10px;
		width:70px;
		height:70px;
		text-align: center;
		cursor: pointer;
	}

	div.wpjam-dashicons .dashicons-before:before{
		font-size:32px;
		width: 32px;
		height: 32px;
	}

	div#TB_ajaxContent p{
		font-size:20px;
		float: left;
	}

	div#TB_ajaxContent .dashicons{
		font-size:100px;
		width: 100px;
		height: 100px;
	}
	</style>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('click', 'div.wpjam-dashicons p', function(){
			var dashicon = $(this).data('dashicon');
			var dashicon_html = '&lt;span class="dashicons '+dashicon+'"&gt;&lt;/span&gt;';
			$('#tb_modal').html('<p><span class="dashicons '+dashicon+'"></span></p><p style="margin-left:20px;">'+dashicon+'<br /><br />HTML：<br /><code>'+dashicon_html+'</code></p>');
			tb_show(dashicon, '#TB_inline?inlineId=tb_modal&width=700&height=200');
			tb_position();
		});
	});
	</script>
	<?php
	$dashicon_css_file	= fopen(ABSPATH.'/'.WPINC.'/css/dashicons.css','r') or die("Unable to open file!");

	$i	= 0;

	$dashicons_html = '';

	while(!feof($dashicon_css_file)) {
		$line	= fgets($dashicon_css_file);
		$i++;
		
		if($i < 32) continue;

		if($line){
			if (preg_match_all('/.dashicons-(.*?):before/i', $line, $matches)) {
				$dashicons_html .= '<p data-dashicon="dashicons-'.$matches[1][0].'"><span class="dashicons-before dashicons-'.$matches[1][0].'"></span> <br />'.$matches[1][0].'</p>'."\n";
			}elseif(preg_match_all('/\/\* (.*?) \*\//i', $line, $matches)){
				if($dashicons_html){
					echo '<div class="wpjam-dashicons">'.$dashicons_html.'</div>'.'<div class="clear"></div>';
				}
				echo '<h2>'.$matches[1][0].'</h2>'."\n";
				$dashicons_html = '';
			}
		}

		// echo  $line. "<br>";
	}

	if($dashicons_html){
		echo '<div class="wpjam-dashicons">'.$dashicons_html.'</div>'.'<div class="clear"></div>';
	}

	fclose($dashicon_css_file);
}