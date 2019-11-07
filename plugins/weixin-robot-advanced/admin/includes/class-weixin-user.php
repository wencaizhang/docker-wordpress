<?php
class WEIXIN_AdminUser extends WEIXIN_User {
	public static function get($openid = '', $force = false){
		$user	= parent::get($openid,$force);
		if($user['tagid_list']){
			$user['tagid_list']	= explode(',', $user['tagid_list']);
		}else{
			$user['tagid_list']	= [];
		}

		return $user;
	}
	public static function parse_user($user){
		if(!$user )	return [];

		if(!$user['subscribe']){
			if(empty($user['subscribe_time'])){
				return [];
			}
			
			$user['nickname'] = '<span style="color:red; text-decoration:line-through; transform: rotate(1deg);">'.$user['nickname'].'</span>';
		}

		$user_sexs	= ['1'=>'男','2'=>'女','0'=>'未知'];
		$user_sex	= $user_sexs[$user['sex']]??'未知';;

		$user['username'] = $user['nickname']??'';
		if(isset($user['headimgurl'])){
			$user['headimgurl'] = str_replace('/0', '/64', $user['headimgurl']);
			$user['username'] = '<img src="'.$user['headimgurl'].'" width="32" class="alignleft" style="margin-right:10px;" />'.$user['username'].'（'.$user_sex.'）';
		}

		$user['subscribe_time']	= get_date_from_gmt(date('Y-m-d H:i:s',$user['subscribe_time']));

		$user['address']	= $user['country'].' '.$user['province'].' '.$user['city'];

		if(!empty($user['unsubscribe_time'])){
			$user['unsubscribe_time']	= get_date_from_gmt(date('Y-m-d H:i:s',$user['unsubscribe_time']));
		}else{
			$user['unsubscribe_time']	= '';
		}

		return $user;
	}

	public static function reply($openid, $data){
		return WEIXIN_AdminMessage::reply($data);
	}

	public static function black($openid){
		return weixin()->batch_blacklist($openid);
	}

	public static function unblack($openid){
		return weixin()->batch_unblacklist($openid);
	}

	public static function bulk_black($openids){
		return weixin()->batch_blacklist($openids);
	}

	public static function bulk_unblack($openids){
		return weixin()->batch_unblacklist($openids);
	}

	public static function tag($openid, $data){
		$user = self::get($openid);

		$current_tags	= $user['tagid_list'];
		$new_tags		= $data['tagid_list']??[];

		$untagging_tags	= array_diff($current_tags, $new_tags);
		$tagging_tags 	= array_diff($new_tags, $current_tags);

		$openid_list	= [$openid];

		if($untagging_tags){
			foreach ($untagging_tags as $tagid) {
				$result = weixin()->batch_untagging($openid_list, $tagid);
				if(is_wp_error($result)){
					return $result;
				}
			}
		}

		if($tagging_tags){
			foreach ($tagging_tags as $tagid) {
				$result = weixin()->batch_tagging($openid_list, $tagid);
				if(is_wp_error($result)){
					return $result;
				}
			}
		}

		return self::get($openid, $force=true);
	}

	public static function bulk_tag($openids, $data){
		$tagid_list	= $data['tagid_list'];
		if(!$tagid_list){
			return new WP_Error('empty_tagid_list', '没有选择任何标签');
		}

		foreach ($tagid_list as $tagid) {
			$result = weixin()->batch_tagging($openids, $tagid);
			if(is_wp_error($result)){
				return $result;
			}
		}

		return self::batch_get_user_info($openids, true);
	}

	public static function remark($openid, $data){	
		$result = weixin()->update_user_remark($openid, $data['remark']);
		if(is_wp_error($result)){
			return $result;
		}

		return self::get($openid, $force=true);
	}

	public static function list($limit, $offset){
		global $weixin_user_tag_options, $weixin_user_blacklist;
		$weixin_user_tag_options	= wp_list_pluck(weixin()->get_tags(), 'name', 'id');
		$weixin_user_blacklist		= weixin()->get_blacklist();

		if(empty($_GET['orderby'])){
			self::Query()->order_by('subscribe_time');
		}

		if(!empty($_GET['blacklist'])){
			if(is_wp_error($weixin_user_blacklist)){
				wp_die($response);
			}
			
			if($weixin_user_blacklist){
				$total 	= count($weixin_user_blacklist);
				$items	= self::batch_get_user_info($weixin_user_blacklist);
			}else{
				$total 	= 0;
				$items	= [];
			}
				
		}elseif(!empty($_GET['tagid'])){
			$response = weixin()->get_tag_users($_GET['tagid']);
			if(is_wp_error($response)){
				wp_die($response);
			}

			$openids	= $response['data']['openid'];
			$total 		= $response['count'];

			$items		= self::batch_get_user_info($openids);
		}elseif(isset($_GET['scan'])){
			$openids	= WEIXIN_UserSubscribe::Query()->where('type','scan')->where('scene',$_GET['scan'])->order_by('time')->limit($limit)->offset($offset)->get_col('openid');
			$total 		= WEIXIN_UserSubscribe::Query()->find_total();
			$items		= self::batch_get_user_info($openids);
		}else{
			$subscribe = $_GET['subscribe'] ?? 1;

			if(is_numeric($subscribe)){
				if(empty($_REQUEST['s'])){
					self::Query()->where('subscribe', $subscribe);

					extract(parent::list($limit, $offset));

					if($items){
						$openids 	= array_column($items, 'openid');
						$items		= self::batch_get_user_info($openids);
					}
				}else{
					extract(parent::list($limit, $offset));
				}
			}else{
				$subscribe	= str_replace('qrscene_', '', $subscribe);

				$openids	= WEIXIN_UserSubscribe::Query()->where('type','subscribe')->where('scene',$subscribe)->order_by('time')->limit($limit)->offset($offset)->get_col('openid');
				$total 		= WEIXIN_UserSubscribe::Query()->find_total();
				$items		= self::batch_get_user_info($openids);
			}
		}

		if(is_wp_error($items)){
			return $items;
		}

		$items	= array_filter($items, function($item){
			return !empty($item['subscribe_time']);	// 至少曾经订阅过
		});

		return compact('items', 'total');
	}

	public static function item_callback($item){
		global $current_admin_url, $weixin_user_tag_options, $weixin_user_blacklist, $wpjam_list_table;

		if(empty($weixin_user_tag_options)){
			$weixin_user_tag_options	= wp_list_pluck(weixin()->get_tags(), 'name', 'id');
		}

		if(empty($weixin_user_blacklist)){
			$weixin_user_blacklist		= weixin()->get_blacklist();
		}

		if($weixin_user_blacklist && in_array($item['openid'], $weixin_user_blacklist)){
			unset($item['row_actions']['black']);
		}else{
			unset($item['row_actions']['unblack']);
		}

		$item	= self::parse_user($item);

		$item['OpenID']	= 'OpenID：'.$item['openid']; 
		if($item['unionid']){
			$item['OpenID']	.= '<br />UnionID：'.$item['unionid'];
		}

		if(!empty($item['user_id'])){
			$item['OpenID']	.= '<br />UserID：'.$item['user_id'];
		}

		$item['address']	= $wpjam_list_table->get_filter_link(['country' => $item['country']], $item['country']).' '.
							  $wpjam_list_table->get_filter_link(['province' => $item['province']], $item['province']).' '.
							  $wpjam_list_table->get_filter_link(['city' => $item['city']], $item['city']);

		if($item['tagid_list']){
			$tagid_list	= [];

			$item['tagid_list'] = is_array($item['tagid_list'])?$item['tagid_list']:explode(',', $item['tagid_list']);

			foreach ($item['tagid_list'] as $tagid) {
				if(isset($weixin_user_tag_options[$tagid])){
					$tagid_list[]	= '<a href="'.$current_admin_url.'&tagid='.$tagid.'">'.$weixin_user_tag_options[$tagid].'</a>';
				}
			}

			$item['tagid_list']	=  implode(',', array_values($tagid_list));	
		}

		$item['time']	= '订阅时间：'.$item['subscribe_time'];

		if($item['unsubscribe_time']){
			$item['time']	= '<br />取消订阅：'.$item['unsubscribe_time'];
		}

		return $item;
	}

	public static function views(){
		global $current_admin_url;

		$weixin_user_blacklist	= weixin()->get_blacklist();

		$tagid		= $_GET['tagid'] ?? '';
		$blacklist	= $_GET['blacklist'] ?? '';
		$subscribe	= isset($_GET['subscribe'])?(is_numeric($_GET['subscribe'])?(int)$_GET['subscribe']:$_GET['subscribe']):'';

		$subscribe_count	= static::Query()->where('subscribe', 1)->order_by('')->get_var('count(*)'); 
		// $unsubscribe_count	= static::Query()->where('subscribe', 0)->get_var('count(*)');

		$views	= [];

		$class = (empty($tagid) && empty($blacklist) && $subscribe !== 0) ? 'class="current"':'';
		$views['subscribe'] = '<a href="'.$current_admin_url.'" '.$class.'>订阅用户<span class="count">（'.$subscribe_count.'）</span></a>';

		$user_tags = weixin()->get_tags();

		if(!is_wp_error($user_tags)){
			$user_tags = array_filter($user_tags, function($tag){
				return $tag['count'];
			});

			foreach ($user_tags as $current_tagid => $tag) {	
				$class = ($current_tagid !== '' && $current_tagid == $tagid) ? 'class="current"':'';
				$views[$current_tagid] = '<a href="'.$current_admin_url.'&tagid='.$current_tagid.'" '.$class.'>'.$tag['name'].'<span class="count">（'.$tag['count'].'）</span></a>';
			}
		}

		$class = $blacklist ? 'class="current"':'';

		if($weixin_user_blacklist){
			$views['blacklist'] = '<a href="'.$current_admin_url.'&blacklist=1" '.$class.'>黑名单<span class="count">（'.count($weixin_user_blacklist).'）</span></a>';
		}

		return $views;
	}

	public static function get_actions(){
		return [
			'tag'		=> ['title'=>'标签',		'page_title'=>'设置标签',	'submit_text'=>'设置标签',	'bulk'=>true],
			'remark'	=> ['title'=>'备注',		'page_title'=>'备注'],
			'black'		=> ['title'=>'拉黑',		'direct'=>true,	'comfirm'=>true,	'bulk'=>true],
			'unblack'	=> ['title'=>'取消拉黑',	'direct'=>true,	'comfirm'=>true,	'bulk'=>true],
			'reply'		=> ['title'=>'回复',		'page_title'=>'回复客服消息'],
		];
	}

	public static function get_fields($action_key='', $openid=0){
		if($action_key==''){

			$subscribe_scene_options	=[
				'ADD_SCENE_SEARCH'				=> '公众号搜索',
				'ADD_SCENE_ACCOUNT_MIGRATION'	=> '公众号迁移',
				'ADD_SCENE_PROFILE_CARD'		=> '名片分享',
				'ADD_SCENE_QR_CODE'				=> '扫描二维码',
				'ADD_SCENE_PROFILE_LINK'		=> '图文页内名称点击',
				'ADD_SCENE_PROFILE_ITEM'		=> '图文页右上角菜单',
				'ADD_SCENE_PAID'				=> '支付后关注',
				'ADD_SCENE_OTHERS'				=> '其他'
			];

			return [
				'username'			=> ['title'=>'用户',		'type'=>'view',	'show_admin_column'=>'only'],
				'address'			=> ['title'=>'地区',		'type'=>'view',	'show_admin_column'=>'only'],
				'subscribe_scene'	=> ['title'=>'来源',		'type'=>'view',	'show_admin_column'=>'only',	'options'=>$subscribe_scene_options],
				'time'				=> ['title'=>'时间',		'type'=>'view',	'show_admin_column'=>'only',	'sortable_columns'=>true],
				'tagid_list'		=> ['title'=>'标签',		'type'=>'view',	'show_admin_column'=>'only'],
				'OpenID'			=> ['title'=>'OpenID',	'type'=>'view',	'show_admin_column'=>'only'],
				// 'sex'				=> ['title'=>'性别',		'type'=>'view',	'show_admin_column'=>'only',	'options'=>['1'=>'男','2'=>'女','0'=>'未知']],
				
			];
		}elseif($action_key == 'reply'){
			$fields	= WEIXIN_AdminMessage::get_reply_fields();
			$fields['FromUserName']['value']	= $openid;

			return $fields;
		}elseif($action_key == 'tag'){
			$user_tags 	= weixin()->get_tags();

			return [
				'tagid_list'	=> ['title'=>'',	'type'=>'checkbox',	'show_admin_column'=>true,	'options'=>wp_list_pluck($user_tags, 'name', 'id')]
			];
		}elseif($action_key == 'remark'){
			return [
				'remark'	=> ['title'=>'',	'type'=>'textarea']
			];
		}elseif($action_key == 'edit'){
			return [
				'user_id'	=> ['title'=>'WP_USER ID',	'type'=>'number']
			];
		}
	}

	public static function list_page(){
		WEIXIN_AdminMessage::list_page();
		?>
		<script type="text/javascript">
		jQuery(function($){
			$("#tr_tagid_list input:checkbox").click(function(){
				if($(this).is(':checked')){
					if($("#tr_tagid_list input:checkbox:checked").length>20){ 
						alert('一个用户最多只能设置20个标签！'); 
						return false; //令刚才勾选的取消 
					}
				}
			})
		});
		</script>
		<?php
	}
}