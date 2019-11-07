<?php
add_filter('wpjam_baidu_zz_tabs', function(){
	return [
		'baidu-zz'	=>['title'=>'百度站长',	'function'=>'option',	'option_name'=>'baidu-zz'],
		'xzh'		=>['title'=>'移动专区',	'function'=>'option',	'option_name'=>'xzh'],
		'batch'		=>['title'=>'批量提交',	'function'=>'wpjam_baidu_zz_batch_page'],
	];
});

add_filter('wpjam_baidu_zz_setting', function(){
	return	[
		'title'		=>'', 
		'summary'	=>'<p>百度推送扩展由 WordPress 果酱和 纵横SEO 联合推出，<a href="https://www.baidufree.com">纵横SEO</a> 助你从百度获取更多免费流量。</p>',
		'fields'	=>[
			'site'	=>['title'=>'站点 (site)',	'type'=>'text',	'class'=>'all-options'],
			'token'	=>['title'=>'密钥 (token)',	'type'=>'password'],
			'mip'	=>['title'=>'MIP',			'type'=>'checkbox', 'description'=>'博客已支持MIP'],
			'no_js'	=>['title'=>'不加载推送JS',	'type'=>'checkbox', 'description'=>'插件已支持主动推送，不加载百度推送JS'],
		]	
	];
});


add_filter('wpjam_xzh_setting', function(){
	return	[
		'title'		=>'', 
		'fields'	=>[
			'appid'		=>['title'=>'AppID',		'type'=>'text',	'class'=>'all-options'],
			'token'		=>['title'=>'密钥 (token)',	'type'=>'password'],
			// 'original'	=>['title'=>'原创',			'type'=>'checkbox', 'description'=>'站点已经获得原创保护'],
		]	
	];
});

function wpjam_baidu_zz_ajax_response(){
	$action	= $_POST['page_action'];
	$data	= wp_parse_args($_POST['data']);

	if($action == 'submit'){
		$offset	= $data['n']?:0;
		$offset	= intval($offset);
		$type	= $data['type']?:'post';

		if($type=='post'){
			$_query	= new WP_Query([
				'post_type'			=>'any',
				'post_status'		=>'publish',
				'posts_per_page'	=>100,
				'offset'			=>$offset	
			]);

			if($_query->have_posts()){
				$count	= count($_query->posts);
				$number	= $offset+$count;	

				$urls	= '';
				while($_query->have_posts()){
					$_query->the_post();

					if(wp_cache_get(get_the_ID(), 'wpjam_baidu_zz_notified') === false){
						wp_cache_set(get_the_ID(), true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
						$urls	.= apply_filters('baiduz_zz_post_link', get_permalink())."\n";
					}
				}

				wpjam_notify_xzh($urls, true);
				wpjam_notify_baidu_zz($urls, true);

				wpjam_send_json(['number'=>$number, 'type'=>$type, 'errmsg'=>'批量提交中，请勿关闭浏览器，已提交了'.$number.'篇文章。']);
			}else{
				wpjam_send_json(['number'=>0, 'type'=>'next', 'errmsg'=>'所有文章提交完成。']);
			}	
		}else{
			if($type == 'next'){
				wpjam_send_json(['errcode'=>0]);
			}else{
				do_action('wpjam_baidu_zz_batch_submit', $type, $offset);
			}
		}
	}
}

function wpjam_baidu_zz_batch_page(){
	?>
	<h2>批量提交</h2>
	<p>使用百度站长更新内容接口和移动专区周级收录接口批量将博客中的所有内容都提交给百度搜索资源平台。</p>
	<?php 

	$types	= apply_filters('wpjam_baidu_zz_batch_submit_types', ['post']);

	$fields	= [
		'n'		=> ['title'=>'',	'type'=>'hidden',	'value'=>0],
		'type'	=> ['title'=>'',	'type'=>'hidden',	'value'=>'post']
	];

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'action'		=> 'submit', 
		'submit_text'	=> '批量提交'
	]);

	?>

	<script type="text/javascript">
	jQuery(function($){
		var types = <?php echo wpjam_json_encode($types); ?>;
		$('body').on('page_action_success', function(e, response){
			var action	= response.page_action;

			if(action == 'submit'){
				if(response.errmsg){
					var response_type = response.type;

					if(response_type == 'next'){
						var current_type 	= $('#type').val();
						var type_index		= types.indexOf(current_type);
						if(type_index+1 < types.length){
							response_type = types[type_index+1];
						}
					}

					$('#n').val(response.number);
					$('#type').val(response_type);

					setTimeout(function(){
						$('#wpjam_form').submit();
					}, 400);
				}else{
					$('#n').val(0);
					$('#type').val('post');
				}		
			}
		});
	});
	</script>
	<?php
}