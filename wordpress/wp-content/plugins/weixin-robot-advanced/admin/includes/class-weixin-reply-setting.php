<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-reply-setting.php');

class WEIXIN_AdminReplySetting extends WEIXIN_ReplySetting{
	public static function get($id){
		global $plugin_page, $current_tab;

		if($plugin_page == 'weixin-replies' && $current_tab == 'default'){
			$default_replies	= parent::get_default_replies();

			$keyword		= '['.$id.']';
			$data			= self::get_by_keyword($keyword);
			$data['id']		= $id;
			$data['title']	= $default_replies[$keyword]['title'];
		}else{
			$data = parent::get($id);
		}

		$type	= $data['type'] ?? '';

		if($type == 'img'){
			if($data['img']){
				$data['img']	= explode(',', $data['img']);
				$data['img']	= $data['img'][0];
			}
		}elseif($type == 'img2'){
			if($data['img2'] && !is_array($data['img2'])){
				$lines = explode("\n", $data['img2']);
				$data['img2']	= [];
				$data['img2']['title']			= $lines[0];
				$data['img2']['description']	= $lines[1];
				$data['img2']['pic_url']		= $lines[2];
				$data['img2']['url']			= $lines[3];
			}
		}

		return $data;
	}

	public static function insert($data){
		$data['time']	= time();
		$data['appid']	= static::get_appid();
		delete_transient('weixin_custom_replies');
		delete_transient('weixin_builtin_replies');

		$data = self::prepare($data);
		return parent::insert($data);
	}	

	public static function update($id, $data){
		delete_transient('weixin_custom_replies');
		delete_transient('weixin_builtin_replies');

		$data = self::prepare($data);
		return parent::update($id, $data);
	}

	public static function prepare($data){
		$type	= $data['type']??'text';

		$data['reply']	= maybe_serialize($data[$type]);

		foreach (self::get_descriptions() as $key => $type) {
			unset($data[$key]);
		}

		return $data;
	}

	public static function set(){
		$args_num	= func_num_args();
		$args		= func_get_args();

		if($args_num == 2){
			$data	= $args[1];
		}else{
			$data	= $args[0];
		}

		$id			= 0;
		$keyword	= $data['keyword']??'';
		if($keyword){
			$reply	= self::get_by_keyword($keyword);

			if($reply && $reply['type'] == $data['type'] && $reply[$reply['type']] == $data[$data['type']]){ // 没更新，就算了
				return true;
			}

			$id		= $reply['id']??0;
		}

		if($id){
			return self::update($id, $data);
		}else{
			return self::insert($data);
		}
	}

	public static function get_by_keyword($keyword){
		$custom_replies		= parent::get_custom_replies();
		$default_replies	= parent::get_default_replies();
		$builtin_replies	= parent::get_builtin_replies();

		$data = [];

		if($custom_replies  && isset($custom_replies[$keyword])) {
			$data		= $custom_replies[$keyword][0];
			$reply_type	= $data['type']??'text';

			$data[$reply_type]	= maybe_unserialize($data['reply']);
		}elseif($builtin_replies && isset($builtin_replies[$keyword])){
			$function			= $builtin_replies[$keyword]['function']??'';
			$reply_type			= $function?'function':'text';
			$data['keyword']	= $keyword; 
			$data['type']		= $reply_type; 
			$data['match']		= $builtin_replies[$keyword]['type']; 
			$data['status']		= 1;
			$data['reply']		= $data[$reply_type]	= $function;
		}elseif($default_replies && isset($default_replies[$keyword])){
			$data['keyword']	= $keyword; 
			$data['type']		= 'text';
			$data['match']		= 'full';
			$data['status']		= 1;
			$data['reply']		= $data['text']		= $default_replies[$keyword]['value'];
		}

		return $data;
	}

	public static function views(){
		global $current_tab, $current_admin_url, $wpjam_list_table;

		if($current_tab != 'custom'){
			return [];
		}

		$reply_types	= self::get_types();
		
		$type		= $_REQUEST['type'] ?? '';
		$status		= $_REQUEST['status'] ?? 1;

		$total 		= self::Query()->where('appid', static::get_appid())->get_var('count(*)');
		$status_0 	= self::Query()->where('appid', static::get_appid())->where('status', 0)->get_var('count(*)');
		$counts 	= self::Query()->where('appid', static::get_appid())->where('status', 1)->group_by('type')->order_by('count')->get_results('COUNT( * ) AS count, `type`');

		$views	= [];

		$class = (empty($type) && $status) ? 'current':'';
		$views['all'] = $wpjam_list_table->get_filter_link(['type'=>0,'status'=>1], '全部<span class="count">（'.$total.'）</span>', $class);

		foreach ($counts as $count) { 

			$class = ($type == $count['type']) ? 'current':'';
			$reply_type = $reply_types[$count['type']]??$count['type'];
			$views[$count['type']] = $wpjam_list_table->get_filter_link(['type'=>$count['type'],'status'=>1], $reply_type.'<span class="count">（'.$count['count'].'）', $class);
		}

		$class = empty($status) ? 'current':'';
		$views['status-0']	= $wpjam_list_table->get_filter_link(['type'=>0,'status'=>0], '未激活<span class="count">（'.$status_0.'）</span>', $class);

		return $views;
	}

	public static function list($limit, $offset){
		global $current_tab;

		if($current_tab == 'custom'){
			$_REQUEST['status']	= $_REQUEST['status'] ?? 1;
			$_REQUEST['type']	= $_REQUEST['type'] ?? null;
			$_REQUEST['type']	= $_REQUEST['type'] ?: null;
			// $default_replies	= parent::get_default_replies();
			// ->where_not_in('keyword', array_keys($default_replies))
			self::Query()->where('appid', static::get_appid());
			return parent::list($limit, $offset);
		}elseif($current_tab == 'default'){
			$items	= parent::get_default_replies();

			if(weixin_get_type() < 4){
				unset($items['[event-location]']);
			}

			array_walk($items, function(&$item, $key){
				$item['id']	= str_replace(['[',']'], '', $key);
			});
			
			$total = count($items);

			return compact('items', 'total');
		}elseif($current_tab == 'builtin'){
			$builtin_replies = parent::get_builtin_replies(); 
			$items = [];

			foreach($builtin_replies as $keyword => $builtin_reply){
				$function = ($builtin_reply['function'])??(!empty($builtin_reply['method'])?'WEIXIN_ReplySetting::'.$builtin_reply['method']:'');

				$keywords = isset($items[$function]['keywords'])?$items[$function]['keywords'].', ':'';

				$items[$function]['id']			= $function;
				$items[$function]['keywords']	= $keywords.$keyword;
				$items[$function]['type'] 		= $builtin_reply['type'];
				$items[$function]['reply'] 		= $builtin_reply['reply'];
				$items[$function]['function'] 	= $function;
			}

			$total = count($items);

			return compact('items', 'total');
		}
	}

	public static function item_callback($item){
		global $current_tab;

		if($current_tab == 'builtin'){
			return $item;
		}elseif($current_tab == 'default'){
			$data	= self::get_by_keyword('['.$item['id'].']');
			$item	= wp_parse_args($item, $data);
		}

		return self::parse($item);
	}

	public static function parse($item){
		$type			= $item['type'];
		
		if( $type == '3rd'){
			$weixin_setting	= weixin_get_setting();
			$item['reply']	= $weixin_setting['weixin_3rd_'.$item['reply']];
		}elseif($type == 'img'){
			$reply_post_ids	= explode(',', $item['reply']);
			$item['reply']	= '';

			$count			= count($reply_post_ids);
			$i				= 1;

			if($reply_post_ids){
				foreach ($reply_post_ids as $reply_post_id) {
					if($reply_post_id){

						$reply_post = get_post($reply_post_id);
						if($reply_post){

							$item_img	= ($i == 1)? wpjam_get_post_thumbnail_url($reply_post, array(640,320)):wpjam_get_post_thumbnail_url($reply_post, array(80,80));
							$item_div_class	= ($i == 1)? 'big':'small'; 
							$item_a_class	= ($i == $count)?'noborder':''; 
							$item_excerpt	= ($count == 1)?'<p>'.get_post_excerpt($reply_post).'</p>':'';
							$iframe_width	= ($i == 1)? '320':'40'; 
							$iframe_height	= ($i == 1)? '160':'40'; 

							if(!$weixin_url = get_post_meta( $reply_post_id, 'weixin_url', true )){
								$weixin_url = get_permalink( $reply_post_id);
							}

							if(strpos($item_img, 'https://mmbiz.') !== false || strpos($item_img, 'http://mmbiz.') !== false){
								$thumb_img		='<img class="weixin_img" src="'.$news_item['thumb_url'].'" width="'.$iframe_width.'" height="'.$iframe_height.'" data-url="'.$news_item['url'].'" />';

								$item['reply'] .= '
								<a class="'.$item_a_class.'" target="_blank" href="'.$weixin_url.'">
									<div class="img_container '.$item_div_class.'">
										<h3>'.$reply_post->post_title.'</h3>
										<img class="weixin_img" src="'.$item_img.'" width="'.$iframe_width.'" height="'.$iframe_height.'" data-url="'.$weixin_url.'" />
									</div>
									'.$item_excerpt.'
								</a>';
							}else{
								$item['reply'] .= '
								<a class="'.$item_a_class.'" target="_blank" href="'.$weixin_url.'">
									<div class="img_container '.$item_div_class.'" style="background-image:url('.$item_img.');">
										<h3>'.$reply_post->post_title.'</h3>
									</div>
									'.$item_excerpt.'
								</a>';
							}

							break;

							$i++;
						}
					}
				}
				$item['reply']	= '<div class="reply_item">'.$item['reply'].'</div>';
			}
		}elseif($type == 'img2'){		
			$raw_reply		= str_replace("\r\n", "\n", maybe_unserialize($item['reply']));
			if(is_array($raw_reply)){
				$item_title		= $raw_reply['title'] ?? '';
				$item_excerpt	= $raw_reply['description'] ?? '';
				$item_img		= $raw_reply['pic_url'] ?? '';
				$item_url		= $raw_reply['url'] ?? '';
			}else{
				$lines = explode("\n", $raw_reply);
	
				$item_title		= $lines[0] ?? '';
				$item_excerpt	= $lines[1] ?? '';
				$item_img		= $lines[2] ?? '';
				$item_url		= $lines[3] ?? '';
			}
			
			$item_div_class	= 'big'; 
			$item_a_class	= 'noborder';
			$iframe_width	= '360'; 
			$iframe_height	= '200'; 

			$item_a_class	= 'noborder';

			if(strpos($item_img, 'https://mmbiz.') !== false || strpos($item_img, 'http://mmbiz.') !== false){
				$item['reply'] = '
				<a class="'.$item_a_class.'" target="_blank" href="'.$item_url.'">
					<div class="img_container '.$item_div_class.'">
						<h3>'.$item_title.'</h3>
						<img class="weixin_img" src="'.$item_img.'" width="'.$iframe_width.'" height="'.$iframe_height.'" data-url="'.$item_url.'" />
					</div>
					<p>'.$item_excerpt.'</p>
				</a>';
			}else{
				$item['reply'] = '
				<a class="'.$item_a_class.'" target="_blank" href="'.$item_url.'">
					<div class="img_container '.$item_div_class.'" style="background-image:url('.$item_img.');">
						<h3>'.$item_title.'</h3>
					</div>
					<p>'.$item_excerpt.'</p>
				</a>';
			}

			$item['reply']	= '<div class="reply_item">'.$item['reply'].'</div>';
		}elseif($type == 'news'){
			if(weixin_get_type() >= 3){
				$material	= weixin()->get_material($item['reply'], 'news');
				if(is_wp_error($material)){
					if($material->get_error_code() == '40007'){
						self::update($item['id'], ['status'=>0]);	
					}
					
					$item['reply'] = $material->get_error_code().' '.$material->get_error_message();
				}else{
					$item['reply']	= '';
					$i 			= 1;
					$count		= 1;

					foreach ($material as $news_item) {

						$item_div_class	= ($i == 1)? 'big':'small'; 
						$item_a_class	= ($i == $count)?'noborder':''; 
						$item_excerpt	= ($count == 1)?'<p>'.$news_item['digest'].'</p>':'';
						$iframe_width	= ($i == 1)? '360':'40'; 
						$iframe_height	= ($i == 1)? '200':'40'; 

						// $thumb	= weixin()->get_material($news_item['thumb_media_id'], 'thumb');
						// $thumb	= is_wp_error($thumb)?'':$thumb;

						$item['reply']   .= '
						<a class="'.$item_a_class.'" target="_blank" href="'.$news_item['url'] .'">
						<!--<div class="img_container '.$item_div_class.'" style="background-image:url('.$news_item['url'].');">
							<h3>'.$news_item['title'].'</h3>
						</div>-->
						<div class="img_container '.$item_div_class.'">
							<h3>'.$news_item['title'].'</h3>
							<img class="weixin_img" src="'.$news_item['thumb_url'].'" width="'.$iframe_width.'" height="'.$iframe_height.'" data-url="'.$news_item['url'].'" />
						</div>
						'.$item_excerpt.'
						</a>';

						break;
						
						$i++;
					}
					$item['reply'] 	= '<div class="reply_item">'.$item['reply'].'</div>';
				}
			}
		}elseif($type == 'image'){
			if(weixin_get_type() >= 3){
				$image	= weixin()->get_material($item['reply'], 'image');
				if(!is_wp_error($image)){
					$item['reply']	= '<a href="'.$image.'" target="_blank"><img src="'.$image.'" style="max-width:200px;" /></a>';
				}
			}
		}else{
			$item['reply']	= wpautop($item['reply']);
		}

		return $item;
	}

	public static function get_filterable_fields(){
		global $current_tab;
		if($current_tab != 'custom'){
			return [];
		}else{
			return parent::get_filterable_fields();
		}
	}

	public static function get_types($all=false){
		$types = array(
			'text'		=> '文本',
			'img2'		=> '自定义图文',
			'img'		=> '文章图文',
		);

		if(weixin_get_type() >=3 || $all){
			$types['news']	= '素材图文';
			$types['image']	= '图片';
			$types['voice']	= '语音';
			// $types['video']	= '视频';
			$types['music']	= '音乐';

			$weixin_setting = weixin_get_setting();
			if(!empty($weixin_setting['weixin_dkf']) || $all){
				$types['dkf']	= '转到多客服';
			}
		}

		$types['3rd']		= '转到第三方';
		$types['function']	= '函数';

		return $types;
	}

	public static function get_fields($key='', $id=0){
		global $plugin_page, $current_tab;

		$type_key	= ($plugin_page == 'weixin-replies')?'type':'reply_type';

		$weixin_setting	= weixin_get_setting();
		$reply_types	= self::get_types();
		$reply_matches	= self::get_matches();

		if($current_tab == 'builtin'){
			return [
				'keywords'	=> ['title'=>'关键字',	'type'=>'text',		'show_admin_column'=>true],
				'type'		=> ['title'=>'匹配方式',	'type'=>'radio',	'show_admin_column'=>true,	'options'=>$reply_matches],
				'reply'		=> ['title'=>'描述',		'type'=>'textarea',	'show_admin_column'=>true],
				'function'	=> ['title'=>'处理函数',	'type'=>'text',		'show_admin_column'=>true]
			];
		}

		$third_options	= [];
		foreach ([1,2,3] as $i) {
			if(!empty($weixin_setting['weixin_3rd_'.$i]) && !empty($weixin_setting['weixin_3rd_url_'.$i])){
				$third_options[$i] = $weixin_setting['weixin_3rd_'.$i];
			}
		}

		if(!$third_options){
			unset($reply_types['3rd']);
		}

		$kf_options = [];
		if(weixin_get_type() >= 3 && !empty($weixin_setting['weixin_dkf'])){
			if($weixin_kf_list 	= weixin()->get_customservice_kf_list()){
				$kf_options	= [''=>' '];
				foreach ($weixin_kf_list as $weixin_kf_account) {
					$kf_options[$weixin_kf_account['kf_account']] = $weixin_kf_account['kf_nick'];
				}
			}
		}

		if(empty($weixin_setting['weixin_search'])){
			unset($reply_types['img']);
		}
		
		$fields	= [
			'keyword'	=> ['title'=>'关键字',		'type'=>'text',		'show_admin_column'=>true,	'value'=>'',	'description'=>'多个关键字请用<strong>英文逗号</strong>区分开，如：<code>七牛, qiniu, 七牛云存储, 七牛镜像存储</code>'],
			'match'		=> ['title'=>'匹配方式',		'type'=>'radio',	'show_admin_column'=>true,	'options'=>$reply_matches],
			$type_key	=> ['title'=>'回复类型',		'type'=>'select',	'show_admin_column'=>true,	'options'=>$reply_types],
			'reply'		=> ['title'=>'回复内容',		'type'=>'textarea',	'show_admin_column'=>'only'],
			'text'		=> ['title'=>'文本内容',		'type'=>'textarea'],
			'img2'		=> ['title'=>'自定义图文',	'type'=>'fieldset',	'fieldset_type'=>'array',	'fields'=>[
				'title'			=> ['title'=>'标题',	'type'=>'text'],
				'description'	=> ['title'=>'摘要',	'type'=>'textarea'],
				'pic_url'		=> ['title'=>'图片',	'type'=>'image'],
				'url'			=> ['title'=>'链接',	'type'=>'url'],
			]],
			'img'		=> ['title'=>'文章图文',		'type'=>'number'],
			'news'		=> ['title'=>'素材图文',		'type'=>'text',		'class'=>'large-text'],
			'image'		=> ['title'=>'图片',			'type'=>'text',		'class'=>'large-text'],
			'voice'		=> ['title'=>'语音',			'type'=>'text',		'class'=>'large-text'],
			'music'		=> ['title'=>'音乐',			'type'=>'textarea'],
			'dkf'		=> ['title'=>'转到多客服',	'type'=>'select',	'options'=>$kf_options],
			'3rd'		=> ['title'=>'转到第三方',	'type'=>'select',	'options'=>$third_options],
			'wxcard'	=> ['title'=>'微信卡券id',	'type'=>'text',		'class'=>'large-text'],
			'function'	=> ['title'=>'函数',			'type'=>'text',		'class'=>'large-text'],
			'status'	=> ['title'=>'状态',			'type'=>'checkbox',	'description'=>'激活',	'value'=>1]
		];

		if($current_tab == 'default'){
			$fields	= ['title'=>['title'=>'类型', 'type'=>'view', 'show_admin_column'=>true]]+$fields;

			$fields['keyword']['type']	= 'hidden';
			$fields['keyword']['show_admin_column']	= false;
		}

		return $fields;
	}

	
	public static function get_descriptions(){
		return array(
			'text'		=> '请输入要回复的文本，可以使用 a 标签。',
			'img'		=> '请输入文章ID。',
			'img2'		=> '请输入标题，摘要，图片链接，链接。',
			'news'		=> '请输入素材图文的 Media ID，Media ID 从素材管理获取。',
			'image'		=> '请输入图片的 Media ID，Media ID 从素材管理获取。',
			'voice'		=> '请输入语音的 Media ID，Media ID 从素材管理获取。',
			'video'		=> '请输入视频的 Media ID，标题，摘要，每个一行，Media ID 从素材管理获取。',
			'music'		=> '请输入音乐的标题，描述，链接，高清连接，缩略图的 Media ID，每个一行，Media ID 从素材管理获取。',
			'function'	=> '请输入函数名，该功能仅限于程序员测试使用。',
			'dkf'		=> '请选择客服或者留空，留空系统会随机选择一个客服。',
			'3rd'		=> '请选择相应的第三方。',
			'wxcard'	=> '请输入微信卡券ID。'
		);
	}

	public static function get_matches(){
		return array(
			'full'		=>'完全匹配',
			'prefix'	=>'前缀匹配',
			'fuzzy'		=>'模糊匹配'
		);
	}

	public static function before_list_page(){
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo WEIXIN_ROBOT_PLUGIN_URL.'/template/static/news-items.css'?>">
		<?php
	}
	
	public static function list_page(){
		global $plugin_page;
		$reply_descriptions	= self::get_descriptions();

		$type_key	= ($plugin_page == 'weixin-replies')?'type':'reply_type';

		$match_descriptions	= [
			'full'		=> ' ',
			'prefix'	=> '<br />前缀匹配方式只支持匹配前两个中文字或者字母。',
			'fuzzy'		=> '<br />模糊匹配效率比较低，如无必要请不要大量使用',
		];
		?>
		<script type="text/javascript">
		jQuery(function($){
			var reply_descriptions	= <?php echo wpjam_json_encode($reply_descriptions);?>;
			var match_descriptions	= <?php echo wpjam_json_encode($match_descriptions);?>;

			var type_selector		= 'select#<?php echo $type_key; ?>';

			$('body').on('change', type_selector, function(){
				['text', 'img2', 'img', 'news', 'image', 'voice', 'music', 'dkf', '3rd', 'function', 'wxcard'].forEach(function(reply_type){
					$('#tr_'+reply_type).hide();
				});

				var selected = $(this).val();

				$('#tr_'+selected).show();
				$('#tr_'+selected+' span').remove();
				$('<span><br />'+reply_descriptions[selected]+'</span>').appendTo('#tr_'+selected+' td');

				tb_position();
				
			});

			$('body').on('change', 'input[type=radio]', function(){
				var selected = $('input[type=radio]:checked').val();
				$('#match_options .description').html('');
				$('<span class="description">'+match_descriptions[selected]+'</span>').appendTo('#match_options');
			});

			$('body').on('list_table_action_success', function(response){
				$('body input[type=radio]').change();
				$('body '+type_selector).change();
			});
		
			$('img.weixin_img').each(function(index, element){
				// console.log($(this).attr('src'));
				console.log(element);
				console.log($(this).data('url'));
				console.log($(element).data('url'));
				$(element).before(show_wx_img($(element).attr('src'), $(element).attr('width'), $(element).attr('height'), $(element).data('url'))).remove();
			});

			$('body').on('list_table_loaded', function(e){
				$('img.weixin_img').each(function(index, element){
					$(this).before(show_wx_img($(this).attr('src'), $(this).attr('width'), $(this).attr('height'), $(this).data('url'))).remove();
				});
			});
		});
		</script>
		<?php
	}
}

