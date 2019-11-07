<?php
class WEIXIN_Material {
	use WEIXIN_Trait;

	static $types = array(
		'news'	=> '图文',
		'image'	=> '图片',
		'voice'	=> '语音',
		'video'	=> '视频',
	);

	public static function get($media_id){
		global $current_tab;
		$item = weixin()->get_material($media_id, $current_tab);

		if(is_wp_error($item)){
			return $item;
		}

		if($current_tab == 'news'){
			return [
				'update_time'	=> time(),
				'media_id'		=> $media_id,
				'content'		=> ['news_item'=>$item]
			];
		}

		return $item;
	}

	public static function insert($data){
		global $current_tab;

		if($current_tab == 'news'){
			return weixin()->add_news_material($data);
		}else{
			return weixin()->add_material($data, $current_tab);
		}
	}

	public static function update($id, $data){
		return weixin()->update_news_material($media_id, $data['index'], $data['articles']);
	}

	public static function delete($media_id){
		return weixin()->del_material($media_id);
	}

	public static function delete_multi($media_ids){
		if(empty($media_ids)){
			return;
		}

		foreach ($media_ids as $media_id) {
			self::delete($media_id);
		}
	}

	public static function bulk_combine($media_ids){
		if(empty($media_ids)){
			return;
		}

		$new_material	= [];

		foreach ($media_ids as $media_id) {
			$material = weixin()->get_material($media_id, 'news', true);
			if(is_wp_error($material)){
				return $material;
			}

			$new_material	= array_merge($new_material, $material);
		}
		
		
		$result	= weixin()->add_news_material($new_material);	

		if(is_wp_error($result)){
			return $result;
		}

		return $result['media_id'];		
	}

	public static function reply($media_id, $data){
		global $current_tab;

		$reply_type		= $current_tab;
		$reply			= maybe_serialize($data[$reply_type]);

		$reply_data			= [
			'keyword'	=> $data['keyword'],
			'match'		=> $data['match']??'full',
			'type'		=> $reply_type,
			$reply_type	=> $reply,
			'status'	=> 1
		];

		return WEIXIN_AdminReplySetting::set($reply_data);
	}

	// 后台 list table 显示
	public static function list($limit, $offset){
		global $current_tab;		

		$material = weixin()->batch_get_material($current_tab, $offset, $limit);

		if(is_wp_error($material)){
			wpjam_admin_add_error($material->get_error_code().'：'. $material->get_error_message(),'error');
			$items	= array();
			$total	= 0;
		}else{
			if(isset($material['item'])){
				$items	= $material['item'];
				$total	= $material['total_count'];
			}else{
				$items	= array();
				$total	= 0;
			}
		}	

		return compact('items', 'total');
	}

	public static function item_callback($item){
		global $current_tab, $current_admin_url;

		global $weixin_list_table,$current_admin_url,$current_tab;
		$item['update_time'] = get_date_from_gmt(date('Y-m-d H:i:s',$item['update_time']));
		
		if($current_tab == 'news' ){
			if(is_array( $item['content']['news_item'] ) ){
				$content	= '';
				$i 			= 1;
				$count		= count($item['content']['news_item']);

				foreach ($item['content']['news_item'] as $news_item) {

					$item_div_class	= ($i == 1)? 'big':'small'; 
					$item_a_class	= ($i == $count)?'noborder':''; 
					$item_excerpt	= ($count == 1)?'<p>'.$news_item['digest'].'</p>':'';
					$iframe_width	= ($i == 1)? '360':'40';
					$iframe_height	= ($i == 1)? '200':'40';

					// $thumb_img		= wp_doing_ajax()?'<img class="weixin_img" src="'.$news_item['thumb_url'].'" width="'.$iframe_width.'" height="'.$iframe_height.'" />' : '<script type="text/javascript">show_wx_img(\''.str_replace('/0?','/640?',$news_item['thumb_url']).'\',\''.$iframe_width.'\',\''.$iframe_height.'\',\''.$news_item['url'].'\');</script>';
					$thumb_img		='<img class="weixin_img" src="'.$news_item['thumb_url'].'" width="'.$iframe_width.'" height="'.$iframe_height.'" data-url="'.$news_item['url'].'" />';

					$content   .= '
					<a class="'.$item_a_class.'" target="_blank" href="'.$news_item['url'] .'">
					<!--<div class="img_container '.$item_div_class.'" data-src="'.$news_item['thumb_url'].'" style="background-image:url('.$news_item['thumb_url'].');">
						<h3>'.$news_item['title'].'</h3>
					</div>-->
					<div class="img_container '.$item_div_class.'">
						<h3>'.$news_item['title'].'</h3>
						'.$thumb_img.'
					</div>
					'.$item_excerpt.'
					</a>';
					
					$i++;
				}
				$item['content'] 	= '<div class="reply_item">'.$content.'</div>';
			}
		}elseif($current_tab == 'image' ){
			if(!empty($item['url'])){
				// $item['name']	= '<div style="max-width:200px;"><script type="text/javascript">show_wx_img(\''.str_replace('/0?','/640?',$item['url']).'\');</script><a href="'.$item['url'].'" target="_blank">'.$item['name'].'</a></div>';
				$item['name']	= '<div style="max-width:200px;"><img class="weixin_img" src="'.$item['url'].'" /><a href="'.$item['url'].'" target="_blank">'.$item['name'].'</a></div>';
			}
		}

		$item['id']	= $item['media_id'];

		if(isset($item['row_actions'])){
			if($current_tab != 'video'){
				$row_actions	= array(
					// 'masssend'	=> '<a href="'.admin_url('admin.php?page=weixin-robot-masssend&content='.$item['media_id'].'&msgtype='.$current_tab).'&TB_iframe=true&width=780&height=500" title="群发消息" class="thickbox">群发消息</a>',
					// 'reply'		=> '<a href="'.admin_url('admin.php?page=weixin-robot-replies&action=add&'.$current_tab.'='.$item['media_id'].'&type='.$current_tab).'&TB_iframe=true&width=780&height=500" title="新增自定义回复" class="thickbox">添加到自定义回复</a>'
					);

				$item['row_actions']	= array_merge($row_actions, $item['row_actions']);	
			}

			if($current_tab == 'news'){
				unset($item['row_actions']['combine']);

				// if(current_user_can('manage_sites')){
				// 	$item['row_actions']['retina']		= '<a href="'.esc_url(wp_nonce_url($current_admin_url.'&action=retina&id='.$item['media_id'], 'retina-'.$weixin_list_table->get_singular().'-'.$item['media_id'])).'">一键高清图片</a>';
				// }
				// $item['row_actions']['recache']		= '<a href="'.esc_url(wp_nonce_url($current_admin_url.'&action=recache&id='.$item['media_id'], 'recache-'.$weixin_list_table->get_singular().'-'.$item['media_id'])).'">更新缓存</a>';
				// $item['row_actions']['duplicate']	= '<a href="'.esc_url(wp_nonce_url($current_admin_url.'&action=duplicate&id='.$item['media_id'], 'duplicate-'.$weixin_list_table->get_singular().'-'.$item['media_id'])).'">复制</a>';
			}
		}

		// $item['row_actions']['delete']		= '<a href="'.esc_url(wp_nonce_url($current_admin_url.'&action=delete&id='.$item['media_id'], 'delete-'.$weixin_list_table->get_singular().'-'.$item['media_id'])).'">删除</a>';
		
		return $item;
	}

	public static function recache($media_id){
		wp_cache_delete($media_id, 'weixin_material');
	}

	public static function duplicate($media_id){
		$articles	= weixin()->get_material($media_id, 'news',  true);
			
		$result	= weixin()->add_news_material($articles);

		if(is_wp_error($result)){
			return $result;
		}

		return $result['media_id'];
	}

	public static function retina($media_id){
		$articles	= weixin()->get_material($media_id, 'news', true);
			
		foreach ($articles as $index => $news_item) {
			$news_item['content'] = preg_replace_callback('/<img.*?data-src=[\'"](.*?)[\'"].*?>/i',function($matches){
			$img_url 	= trim($matches[1]);

			if(empty($img_url)) return;

			$img_url	= str_replace('/640?', '/0?', $img_url);

			if(!preg_match('|<img.*?srcset=[\'"](.*?)[\'"].*?>|i',$matches[0],$srcset_matches)){
				return str_replace('data-src', ' data-srcset="'.$img_url.' 2x"  data-src', $matches[0]);
			}

			return $matches[0];
		},$news_item['content']);
			weixin()->update_news_material($media_id, $index, $news_item);
		}
	}

	public static function get_actions(){
		global $current_tab;

		if($current_tab == 'news'){
			return [
				'reply'		=> ['title'=>'添加到自定义回复',	'update'=>false],
				'combine'	=> ['title'=>'合并',		'direct'=>true, 'bulk'=>true,	'response'=>'add'],
				'duplicate'	=> ['title'=>'复制',		'direct'=>true],
				'recache'	=> ['title'=>'更新缓存',	'direct'=>true],
				'delete'	=> ['title'=>'删除',		'direct'=>true,	'confirm'=>true,	'bulk'=>true],
			];
		}else{
			return	[
				'reply'		=> ['title'=>'添加到自定义回复',	'update'=>false],
				'delete'	=> ['title'=>'删除',		'direct'=>true,	'confirm'=>true,	'bulk'=>true],
			];
		}
	}

	public static function get_fields($action_key='', $media_id=''){
		global $current_tab;

		if($action_key == 'reply'){
			$fields		= WEIXIN_AdminReplySetting::get_fields();
			$reply_type	= $current_tab;

			$fields['reply_type']['value']	= $reply_type;
			$fields['reply_type']['type']	= 'view';
			$fields[$reply_type]['value']	= $media_id;

			foreach (WEIXIN_AdminReplySetting::get_descriptions() as $key => $type) {
				if($key != $reply_type){
					unset($fields[$key]);
				}
			}

			unset($fields['status']);
		}else{
			if($current_tab == 'news'){
				$fields	= [
					'content'		=> ['title'=>'内容',			'type'=>'text',	'show_admin_column'=>true],
					'media_id'		=> ['title'=>'Media ID',	'type'=>'text',	'show_admin_column'=>true],
					'update_time'	=> ['title'=>'最后更新时间',	'type'=>'text',	'show_admin_column'=>true]
				];

				
			}else{
				$fields	= [
					'name'			=> ['title'=>'内容',			'type'=>'text',	'show_admin_column'=>true],
					'media_id'		=> ['title'=>'Media ID',	'type'=>'text',	'show_admin_column'=>true],
					'update_time'	=> ['title'=>'最后更新时间',	'type'=>'text',	'show_admin_column'=>true]
				];
			}
		}

		return $fields;
	}

	public static function before_list_page(){
		WEIXIN_AdminReplySetting::before_list_page();

		global $current_tab;

		if($current_tab == 'image'){ 
		if(isset($_FILES['file'])){
			$result	= wp_handle_upload($_FILES['file'], array('test_form' => false));
			if(empty($result['error'])){
				$media		= $result['file'];
				$form_data	= array(
					'filename'		=>basename($result['url']),
					'content-type'	=>$result['type'], 
					'filelength'	=>filesize($media) 
				);

				$response	= weixin()->add_material($media);
				unlink($media);
				if(is_wp_error($response)){
					wpjam_admin_add_error($response->get_error_code().'：'.$response->get_error_message());
				}else{
					wpjam_admin_add_error('图片新增成功');
				}
			}
		}
		?>
		<form action="#" method="post" enctype="multipart/form-data" name="new-image" id="new-image" style="display:inline-block;position:relative;margin-top:10px;">
			<input id="file" type="file" name="file" style="filter:alpha(opacity=0);position:absolute;opacity:0;width:80px;height:34px; margin:-5px 0;" hidefocus>  
			<a href="#" class="page-title-action button-primary" style="position:static;">上传图片</a>
		</form>
		<script type="text/javascript">
		jQuery(function($){
			$('body').on('change', '#file', function(){
				if($('#file').val()){
					$('#new-image').submit();
				}
			});
		});
		</script>
		<?php }
	}
	
	public static function list_page(){
		WEIXIN_AdminReplySetting::list_page();
	}
}