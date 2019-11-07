<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-material.php');
include(WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-reply-setting.php');

add_action('wpjam_weixin_material_tabs', function($tabs){
	$material_count = weixin()->get_material_count();

	if(is_wp_error($material_count)){
		wp_die($material_count->get_error_message());
	}

	foreach (WEIXIN_Material::$types as $type=>$name) {
		$tabs[$type]	= [
			'title'		=> $name.' <small>('.$material_count[$type.'_count'].')</small>', 
			'function'	=> 'list'
		];
	}

	$tabs['fetch']	= [
		'title'		=> '一键转载<small></small>',
		'function'	=> 'weixin_material_fetch_page'
	];

	$tabs['combine']	= [
		'title'		=> '合并图文<small></small>',
		'function'	=> 'weixin_material_combine_page'
	];

	if(isset($_GET['tab']) && $_GET['tab']=='edit'){
		$tabs['edit']	= [
			'title'		=> '编辑图文<small></small>',
			'function'	=> 'weixin_material_edit_page'
		];
	}

	return $tabs;
});

add_filter('wpjam_weixin_material_list_table', function(){
	global $current_tab;

	$style	= 'th.column-update_time{width:90px;}';

	if($current_tab == 'news'){
		$per_page		= 10;
	}else{
		$per_page		= 20;
	}

	return [
		'title'				=> WEIXIN_Material::$types[$current_tab].'素材',
		'singular'			=> 'weixin-material',
		'plural'			=> 'weixin-materials',
		'primary_column'	=> 'media_id',
		'primary_key'		=> 'media_id',
		'model'				=> 'WEIXIN_Material',
		'style'				=> $style,
		'per_page'			=> $per_page,
		'ajax'				=> true
	];
});

function weixin_material_edit_page(){
	$media_id	= isset($_GET['media_id'])?$_GET['media_id']:'';
	$index		= isset($_GET['index'])?$_GET['index']:1;

	if(!$media_id || !$index){
		wp_die('media_id 或者 index 不能为空');
	}

	$material = weixin()->get_material($media_id, 'news', true);

	if(is_wp_error($material)){
		wp_die($media_id.'：'.$material->get_error_message());
	}

	if(empty($material[$index-1])){
		wp_die('第'.$index.'条图文不存在');
	}

	global $current_admin_url;

	$form_fields 	= [
		'title'					=> ['title'=>'标题',			'type'=>'text'],
		'content'				=> ['title'=>'内容',			'type'=>'editor',	'settings'=>['default_ediotr'=>'quicktags']],
		'author'				=> ['title'=>'作者',			'type'=>'text'],
		'digest'				=> ['title'=>'摘要',			'type'=>'textarea',	'style'=>'max-width:640px;'],
		'content_source_url'	=> ['title'=>'原文链接',		'type'=>'url',		'style'=>'max-width:640px;',	'class'=>'large-text'],
		'thumb_media_id'		=> ['title'=>'头图',			'type'=>'text',		'style'=>'max-width:640px;',	'class'=>'large-text'],
		'need_open_comment'		=> ['title'=>'打开评论',		'type'=>'radio',	'options'=>[0=>'不打开评论',1=>'打开评论']],
		'only_fans_can_comment'	=> ['title'=>'粉丝才可评论',	'type'=>'radio',	'options'=>[0=>'所有人可评论',1=>'粉丝才可评论']],
	];

	$nonce_action = 'weixin-edit-material';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data = wpjam_get_form_post($form_fields, $nonce_action);

		$data['content']	= str_replace("\n", "", wpautop($data['content']));

		// $news_item = $material[$index-1];
		// $articles 	= array_merge($news_item, $data);

		unset($data['url']);
		unset($data['thumb_url']);

		$data['need_open_comment']		= (int)$data['need_open_comment'];
		$data['only_fans_can_comment']	= (int)$data['only_fans_can_comment'];

		$response	= weixin()->update_news_material($media_id, $index-1, $data);

		if(is_wp_error($response)){
			wpjam_admin_add_error($media_id.'：'.$response->get_error_message(), 'error');			
		}else{
			wpjam_admin_add_error('更新成功');
		}

		wp_cache_delete($media_id, 'weixin_material');

		$material	= weixin()->get_material($media_id, 'news', true);
	}

	$news_item = $material[$index-1];

	$news_item['content']	= str_replace('</p>', "</p>\n\n", $news_item['content']);
	$news_item['content']	= str_replace("\n\n\n", "\n\n", $news_item['content']);

	foreach ($form_fields as $key=>$form_field) {
		$form_fields[$key]['value'] = $news_item[$key];
	}

	$form_url = $current_admin_url.'&action=edit&media_id='.$media_id.'&index='.$index;

	?>
	<h2>编辑图文</h2>

	<?php wpjam_form($form_fields, $form_url, $nonce_action, '编辑'); ?>

	<?php
}

function weixin_material_combine_page(){
	global $current_admin_url;

	$form_fields 	= [
		'media_ids'		=> ['title'=>'',	'type'=>'textarea',	'style'=>'max-width:640px;']
	];

	$nonce_action = 'weixin-combine-material';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data = wpjam_get_form_post($form_fields, $nonce_action, 'edit_weixin');
		if($media_ids = $data['media_ids']){
			$new_material	= [];

			$media_ids	= explode("\n", $media_ids);
			foreach ($media_ids as $media_id) {
				$media_id	= trim($media_id).'|';
				list($media_id, $n) = explode("|", $media_id);
				$material = weixin()->get_material($media_id, 'news', true);
				if(is_wp_error($material)){
					wpjam_admin_add_error($media_id.'：'.$material->get_error_message(), 'error');
					$new_material = [];
					break;
				}
				if($n){
					$new_material[]	= $material[$n-1];
				}else{
					$new_material	= array_merge($new_material, $material);
				}
			}

			if($new_material){
				$response	= weixin()->add_news_material($new_material);
				if(is_wp_error($response)){
					wpjam_admin_add_error($response->get_error_message(), 'error');
				}else{
					wpjam_admin_add_error('合并成功');
				}
			}
		}else{
			wpjam_admin_add_error('你没有输入任何素材ID！','error');
		}
	}

	?>
	<h2>合并图文</h2>

	<p>请按照格式输入要合并的图文：</p>

	<?php wpjam_form($form_fields, $current_admin_url, $nonce_action, '合并'); ?>

	<p>
	格式为：media_id|n，|后面为空则全部，比如：<br />
	<code>MHNViNjDYTcuCtmVYnmd8-MzQpTPLJjSmEhXbtik4pM|3</code> 为：MHNViNjDYTcuCtmVYnmd8-MzQpTPLJjSmEhXbtik4pM的第三条图文<br />
	<code>wpJr2hWr0dg_K9xETG7QM1-5vdNenyYerx3ddF9qulc</code> 为：wpJr2hWr0dg_K9xETG7QM1-5vdNenyYerx3ddF9qulc的所有图文
	</p>

	<?php
}

function weixin_material_fetch_page(){
	global $current_admin_url;

	$form_fields 	= [
		'mp_url'			=> ['title'=>'图文链接',			'type'=>'url',	'class'=>'large-text'],
		'thumb_media_id'	=> ['title'=>'头图 media_id',	'type'=>'text',	'description'=>'微信公众号开发模式只能上传5000张图片！如果已超限，请先选用一张<a href="'.admin_url('admin.php?page=weixin-robot-material&tab=image').'" target="_blank">已有图片的Media_id</a>代替，再到微信公众号后台替换！'],
	];

	$nonce_action = 'weixin-fetch-material';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data = wpjam_get_form_post($form_fields, $nonce_action, 'edit_weixin');

		$mp_url			= $data['mp_url'];
		$thumb_media_id	= $data['thumb_media_id'];

		if($mp_url){
			$response	= weixin_add_remote_article($mp_url,$thumb_media_id);
			if(is_wp_error($response)){
				wpjam_admin_add_error($response->get_error_message(), 'error');
			}else{
				$redirect_to = add_query_arg(['updated'=>'true'], admin_url('admin.php?page=weixin-robot-material'));	
				wp_redirect($redirect_to);
			}
		}else{
			wpjam_admin_add_error('你没有输入图文链接！','error');
		}
	}

	?>
	<h2>一键转载</h2>

	<?php wpjam_form($form_fields, $current_admin_url, $nonce_action, '转载'); ?>

	<p>*视频和投票无法转载</p>

	<?php
}

// 将远程图片新增到到素材，type = thumb 上传缩略图
function weixin_upload_remote_image_media($image_url, $type='image'){
	$media = weixin_download_remote_image($image_url);
	if(!is_wp_error($media)){
		$response = weixin()->upload_media($media, $type);

		unlink($media);
		return $response;
	}else{
		return false;
	}
}

function weixin_download_remote_image($image_url, $media=''){ 
	if(strpos($image_url, home_url()) === 0){
		return str_replace(home_url('/'), ABSPATH, $image_url);	// 本地图片就用本地路径
	}

	$media	= ($media)?$media:WEIXIN_ROBOT_PLUGIN_TEMP_DIR.'media/temp/'.md5($image_url).'.jpg';

	if(!file_exists($media)){
		$ua		= 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36';

		$response = wpjam_remote_request($image_url, ['method'=>'file', 'stream'=>true, 'filename'=>$media,'need_json_decode'=>false]);

		if(is_wp_error($response)){
			return $response;
		}
	}

	return $media;
}

function weixin_add_remote_article($mp_url,$thumb_media_id=''){
	$article = weixin_parse_mp_article($mp_url);
	if(is_wp_error($article)){
		return $article;
	}

	if(!$thumb_media_id){
		$thumb_url	= $article['thumb_url'];
		
		$media	= weixin_download_remote_image($thumb_url);
		if(is_wp_error($media)){
			return $media;
		}
		
		$response = weixin()->add_material($media);
		if(is_wp_error($response)){
			return $response;
		}
		unlink($media);
		
		$thumb_media_id	= $response['media_id'];

	}

	$article['thumb_media_id']	= $thumb_media_id;
	$article['content']			= strip_tags($article['content'],'<p><img><br><span><section><strong><iframe><blockquote>');
	
	unset($article['thumb_url']);

	$articles[]	= $article; 
	return weixin()->add_news_material($articles);
}

