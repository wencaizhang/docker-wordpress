<?php

add_shortcode('wximg', function ($atts, $content=''){
	extract( shortcode_atts( array( 
		'title' => get_the_title(),
		'url'	=> '',
		'align'	=> 'left'
	), $atts ) );

	if($url){
		return '<p class="wx_img" style="text-align: '.$align.'"><a href="'.$url.'"><img src="'.$content.'" alt="'.$title.'" /></a><script type="text/javascript">show_wx_img(\''.$content.'\',\''.$url.'\');</script></p>';
	}else{
		return '<p class="wx_img" style="text-align: '.$align.'"><img src="'.$content.'" alt="'.$title.'" /><script type="text/javascript">show_wx_img(\''.$content.'\');</script></p>';
	}
});


add_action('wp_head', function (){ ?>
	<script type="text/javascript">
	function show_wx_img() {
		var src = arguments[0] ? arguments[0] : '';
		var url = arguments[1] ? arguments[1] : '';
		var frameid = 'frameimg' + Math.random();
		if(url){
			window.img = '<a href="'+url+'" target="_blank"><img id="img"  style="max-width:100%;" src=\'' + src + '?' + Math.random() + '\' /></a><script>window.onload = function() { parent.document.getElementById(\'' + frameid + '\').height = document.getElementById(\'img\').height+\'px\'; parent.document.getElementById(\'' + frameid + '\').width = document.getElementById(\'img\').width+\'px\'; }<' + '/script>';
		}else{
			window.img = '<img id="img"  style="max-width:100%;" src=\'' + src + '?' + Math.random() + '\' /><script>window.onload = function() { parent.document.getElementById(\'' + frameid + '\').height = document.getElementById(\'img\').height+\'px\'; parent.document.getElementById(\'' + frameid + '\').width = document.getElementById(\'img\').width+\'px\'; }<' + '/script>';
		}
		
		document.write('<iframe id="' + frameid + '" src="javascript:parent.img;" width="100%" frameBorder="0" scrolling="no"></iframe>');
	}
	</script>
	<style type="text/css">
	p.wx_img img {display: none; }
	</style>
	<?php 
});