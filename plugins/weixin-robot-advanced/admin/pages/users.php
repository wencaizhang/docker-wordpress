<?php
function weixin_robot_users_tabs($tabs){
	$tabs = array();
	$action	= isset($_GET['action'])?$_GET['action']:'';
	if($action == 'bulk-edit'){
		$tabs['list']	= array('title'=>'用户列表', 	'function'=>'weixin_robot_user_bulk_edit_page');
	}else{
		// if(get_current_blog_id() == 26){
			$tabs['list']	= array('title'=>'用户列表', 	'function'=>'list');
		// }else{
		// 	$tabs['list']	= array('title'=>'用户列表', 	'function'=>'weixin_robot_user_list_page');
		// }
	}
	$tabs['tags']		= array('title'=>'标签管理', 	'function'=>'list');
	// $tabs['tags']		= array('title'=>'标签管理',	'function'=>'weixin_robot_user_tags_page');
	// $tabs['tag-import']	= array('title'=>'批量标签',	'function'=>'weixin_robot_user_tag_import_page');
	// $tabs['tag-merge']	= array('title'=>'合并标签',	'function'=>'weixin_robot_user_tag_merge_page');
	// $tabs['tag2group']	= array('title'=>'批量群组', 	'function'=>'weixin_robot_user_group_import_page');
	
	return $tabs;
}

// 微信用户列表
function weixin_robot_user_list_page(){
	global $wpdb, $current_admin_url, $weixin_list_table;

	$weixin_user_tags = weixin()->get_tags();

	$action = isset($_GET['action'])?$_GET['action']:'';

	// if($action == 'tags'){
	// 	weixin_robot_get_tag_user_list();
	// }
	
	$title	= "";

	$search_term	= isset($_GET['s'])?$_GET['s']:'';

	$tagid			= isset($_GET['tagid'])?$_GET['tagid']:'';

	$country		= isset($_GET['country'])?$_GET['country']:'';
	$province		= isset($_GET['province'])?$_GET['province']:'';
	$city			= isset($_GET['city'])?$_GET['city']:'';

	$ip_country		= isset($_GET['ip_country'])?$_GET['ip_country']:'';
	$ip_region		= isset($_GET['ip_region'])?$_GET['ip_region']:'';
	$ip_city		= isset($_GET['ip_city'])?$_GET['ip_city']:'';

	$sex			= isset($_GET['sex'])?$_GET['sex']:'';
	$language		= isset($_GET['language'])?$_GET['language']:'';

	$subscribe		= isset($_GET['subscribe'])?(is_numeric($_GET['subscribe'])?(int)$_GET['subscribe']:$_GET['subscribe']):'';
	$scan 			= isset($_GET['scan'])?$_GET['scan']:'';

	$os				= isset($_GET['os'])?$_GET['os']:'';
	$os_ver			= isset($_GET['os_ver'])?$_GET['os_ver']:'';

	$device			= isset($_GET['device'])?$_GET['device']:'';
	$brand			= isset($_GET['brand'])?$_GET['brand']:'';
	$size			= isset($_GET['size'])?$_GET['size']:'';

	$orderby		= isset($_GET['orderby'])?$_GET['orderby']:'';
	$order 			= isset($_GET['order'])?$_GET['order']:'desc';

	$sql_orderby	= '';
	if($orderby){
		$sql_orderby	= "{$orderby} {$order}";
	}

	$where	= "wut.openid != '' ";

	if($subscribe === 0){
		$where	.= "AND subscribe = 0 AND subscribe_time !=''";
	}else{
		$where	.= "AND subscribe = 1";
	}
	
	if($search_term){
		$where	.= " AND (openid like '%{$search_term}%' OR nickname LIKE '%{$search_term}%')";
		$title	.= '含关键字 '.$search_term. '';
	}

	// if($groupid !== ''){
	// 	$where 	.= " AND groupid = {$groupid}";
	// 	$title	.= '分组  “'.$weixin_user_groups[$groupid]['name']. '” 下';
	// }
	
	if($tagid){
		$where	.= " AND FIND_IN_SET ('{$tagid}', tagid_list)";
		$tag	= $weixin_user_tags[$tagid];
		$title	.= '标签  “'.$tag['name']. '” 下';
	}
	// }elseif($tagid !== ''){
	// 	$title	.= '未打标签';
	// }

	if($country){
		$where 	.= " AND wut.country = '{$country}'";
		$title	.= '国家和地区为 “'.$country.'” ';
	}

	if($province){
		$where	.= " AND wut.province = '{$province}'";
		$title	.= '省份为 “'.$province.'” ';
	}

	if($city){
		$where	.= " AND wut.city = '{$city}'";
		$title	.= '城市为 “'.$city.'” ';
	}

	if($ip_country){
		$where 	.= " AND wut.ip_country = '{$ip_country}'";
		$title	.= '国家和地区为 “'.$ip_country.'” ';
	}

	if($ip_region){
		$where	.= " AND wut.ip_region = '{$ip_region}'";
		$title	.= '省份为 “'.$ip_region.'” ';
	}

	if($ip_city){
		$where	.= " AND wut.ip_city = '{$ip_city}'";
		$title	.= '城市为 “'.$ip_city.'” ';
	}

	if($sex !== ''){
		$where	.= " AND wut.sex = {$sex}";
		$sex	= ($sex == 1)?'男': (($sex == 2)?'女':'未知');
		$title	.= '性别为 “'.$sex.'” ';
	}

	if($language){
		$where	.= " AND wut.language = '{$language}'";
		$title	.= '语言为 “'.$language.'” ';
	}

	if($os){
		$where	.= " AND wut.os = '{$os}'";
		$title	.= '手机系统为 “'.$os.'” ';
	}

	if($os_ver){
		$where	.= " AND wut.os_ver = '{$os_ver}'";
		$title	.= '系统版本为 “'.$os_ver.'” ';
	}

	if($device){
		$where .= " AND wut.device = '{$device}'";
		
		$device_array	= wpjam_get_device($device);
		if($device_array){
			$title 	.= '手机型号为 “'.$device_array['name'].'” ';
		}else{
			$title 	.= '手机型号为 “'.$device.'” ';
		}
	}

	if($brand){
		$where	.= " AND wdt.brand = '{$brand}'";
		$title	.= '手机品牌为 “'.$brand.'” ';
	}

	if($size){
		$where	.= " AND wdt.size = '{$size}'";
		$title	.= '手机屏幕尺寸为 “'.$size.'” ';
	}

	if(empty($sql_orderby)){
		if($subscribe || $scan){
			// $sql_orderby = "CreateTime DESC";
			$sql_orderby = "wst.time DESC";
		}elseif($subscribe === 0){
			$sql_orderby = "unsubscribe_time DESC";
		}else{
			$sql_orderby = "subscribe_time DESC";
		}
	}

	$where = apply_filters('weixin_crm_users_where', $where );
	//$title = apply_filters('weixin_crm_users_where', $title );
	$weixin_crm_users_join	= apply_filters('weixin_crm_users_join', '' );
	$weixin_crm_user_fileds	= apply_filters('weixin_crm_user_fileds', '' );

	if($weixin_crm_users_join){
		$weixin_crm_users_join = "LEFT JOIN $wpdb->weixin_crm_users wcut ON wut.openid=wcut.weixin_openid";
	}

	$limit	= $weixin_list_table->get_limit();

	if($subscribe){
		$subscribe		= str_replace('qrscene_', '', $subscribe);
		$weixin_qrcode	= WEIXIN_Qrcode::get_qrcode($subscribe);

		$where	.= " AND wst.type='subscribe' AND wst.scene='{$subscribe}'";

		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT wut.* {$weixin_crm_user_fileds} FROM $wpdb->weixin_users wut {$weixin_crm_users_join} INNER JOIN $wpdb->weixin_subscribes wst ON wut.openid = wst.openid WHERE {$where}  ORDER BY {$sql_orderby} ) t GROUP BY openid LIMIT {$limit}";

		$title .= '通过带参数二维码 '.$weixin_qrcode['name'].' 关注用户';
	}elseif ($scan) {
		$weixin_qrcode = WEIXIN_Qrcode::get_qrcode($scan);

		$where	.= " AND wst.type='scan' AND wst.scene='{$scan}'";

		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT wut.* {$weixin_crm_user_fileds} FROM $wpdb->weixin_users wut {$weixin_crm_users_join} INNER JOIN $wpdb->weixin_subscribes wst ON wut.openid = wst.openid WHERE {$where} ORDER BY {$sql_orderby}) t GROUP BY openid  LIMIT {$limit}";
		
		$title .= '已关注用户扫描带参数二维码 '.$weixin_qrcode['name'];
	}elseif($brand || $size){
		$sql = "SELECT SQL_CALC_FOUND_ROWS wut.* {$weixin_crm_user_fileds} FROM $wpdb->weixin_users wut {$weixin_crm_users_join} LEFT JOIN $wpdb->devices wdt ON trim(wut.device) = wdt.device WHERE {$where} ORDER BY {$sql_orderby} LIMIT {$limit}";
	// }elseif($tagid){
		// $sql = "SELECT SQL_CALC_FOUND_ROWS wut.* {$weixin_crm_user_fileds} FROM $wpdb->weixin_users wut {$weixin_crm_users_join} INNER JOIN $wpdb->weixin_user_tag_relationships wutrt ON wut.openid = wutrt.weixin_openid WHERE {$where} ORDER BY {$sql_orderby} LIMIT {$limit}";
	}else{
		$sql = "SELECT SQL_CALC_FOUND_ROWS wut.* {$weixin_crm_user_fileds} FROM $wpdb->weixin_users wut {$weixin_crm_users_join} WHERE {$where} ORDER BY {$sql_orderby} LIMIT {$limit}";
	}

	$sql = apply_filters('weixin_user_admin_sql',$sql);
	
	$weixin_users	= $wpdb->get_results($sql, ARRAY_A);
	$total			= $wpdb->get_var("SELECT FOUND_ROWS();");

	$title		= ($title)? $title.'的用户' : '微信用户列表';

	echo '<h2>'.$title.'</h2>';
	 /* <p><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&tab='.$current_tab.'&action=sync');?>" class="button action" >同步微信用户到本地</a></p> */

	$weixin_list_table->prepare_items($weixin_users, $total);
	$weixin_list_table->display();
	?>	

	<script type="text/javascript">
		jQuery(function($){
			var ids = '';

			$('.tablenav .bulkactions').append('<a href="#" title="批量编辑用户" class="thickbox edit-action button action" style="display:none;">应用</a>');
			$('.tablenav .bulkactions').append('<a href="#" title="群发消息" class="thickbox masssend-action button action" style="display:none;">应用</a>');

			$('tbody input[type=checkbox]').change(function(){
				ids = '';

				$('tbody input[type=checkbox]:checked').each(function(){
					ids += '&ids[]='+$(this).val();
				});
				$('.edit-action').attr('href','<?php echo admin_url('admin.php?page=weixin-robot-users&action=bulk-edit')?>'+ids+'&TB_iframe=true&width=780&height=240');
				$('.masssend-action').attr('href','<?php echo admin_url('admin.php?page=weixin-robot-masssend&type=ids')?>'+ids+'&TB_iframe=true&width=780&height=450');
			});

			$('#cb input[type=checkbox]').change(function(){
				ids = '';

				if($('#cb input[type=checkbox]').is(':checked')){
					$('tbody input[type=checkbox]').each(function(){
						ids += '&ids[]='+$(this).val();
					});
				}
				
				$('.edit-action').attr('href','<?php echo admin_url('admin.php?page=weixin-robot-users&action=bulk-edit')?>'+ids+'&TB_iframe=true&width=780&height=240');
				$('.masssend-action').attr('href','<?php echo admin_url('admin.php?page=weixin-robot-masssend&type=ids')?>'+ids+'&TB_iframe=true&width=780&height=450');
			});

			$("select").change(function(){
				if($("select").val() == 'edit'){
					$('#doaction').hide();
					$('#doaction2').hide();
					$('.edit-action').show();
					$('.masssend-action').hide();
				}else if($("select").val() == 'masssend'){
					$('#doaction').hide();
					$('#doaction2').hide();
					$('.edit-action').hide();
					$('.masssend-action').show();
				}else{
					$('#doaction').show();
					$('.edit-action').hide();
					$('.masssend-action').hide();
				}
			});
		});
	</script>
					
	<?php 
}

// 批量编辑页面
function weixin_robot_user_bulk_edit_page(){
	global $wpdb, $plugin_page, $current_admin_url;

	$ids 	= isset($_GET['ids'])?$_GET['ids']:'';

	if(!$ids){
		wp_die('至少要选择一个用户来编辑');
	}

	$tags	= '';

	$form_fields = array(); 
	$weixin_user_tags = weixin()->get_tags();
	if(is_wp_error($weixin_user_tags)){
		//
	}else{
		$weixin_user_tags_options = array();
		foreach ($weixin_user_tags as $current_tagid => $weixin_user_tag) {
			$weixin_user_tags_options[$current_tagid] = $weixin_user_tag['name'];
		}
		$form_fields['tags']	= array('title'=>'标签',	'type'=>'checkbox',	'value'=>'',	'options'=> $weixin_user_tags_options);
	}

	$nonce_ation	= 'weixin-user-bulk-edit';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		$data	= wpjam_get_form_post($form_fields, $nonce_ation);
		$error	= 0;
		if($data['tags']){
			foreach ($data['tags'] as $tagid) {
				$openid_list	= $ids;
				do{
					$updated_openid_list	= array_slice($openid_list, 0, 50);
					$response				= weixin()->batch_tagging($updated_openid_list, $tagid);
					if(is_wp_error($response)){
						$error = 1;
						wpjam_admin_add_error($response->get_error_message(),'error');
					}else{
						// weixin_robot_get_tag_user_list($tagid);
					}
					$openid_list			= array_slice($openid_list, 50);
				}while(count($openid_list) > 50);
			}
		}

		if($error == 0){
			wpjam_admin_add_error('批量编辑成功');
		}

		$form_fields['tags']['value']	= $data['tags'];
	}

	echo '<h1>批量编辑以下用户：</h1>';

	echo '<p>';
	$ids_str = '';
	foreach ($ids as $weixin_openid) {
		$ids_str		.= '&ids[]='.$weixin_openid;
		$weixin_user	= WEIXIN_AdminUser::parse_user(WEIXIN_User::get($weixin_openid));
		echo '<img src="'.$weixin_user['headimgurl'].'" alt="'.$weixin_user['nickname'].'" width="32" />';
	}
	echo '</p>';

	$form_url = $current_admin_url.'&action=bulk-edit'.$ids_str;

	wpjam_form($form_fields, $form_url, $nonce_ation, '编辑');
	?>
	<script type="text/javascript">
	jQuery(function($){
		$(".form-table input:checkbox").click(function(){
			if($("input:checkbox:checked").length>3){ 
				alert('亲，最多只能选三个哟~'); 
				return false; //另刚才勾选的取消 
			}
		})
	});
	</script>
	<?php
}

