<?php
class WEIXIN_CustomService {
	static $online_status = array(
		'1'	=> 'Web在线',
		'2'	=> '手机在线',
		'3'	=> 'Web和手机同时在线'
		);

	public static function get($kf_account){
		$customservice_kf_list	= weixin()->get_customservice_kf_list();

		if(is_wp_error($customservice_kf_list)){
			wpjam_admin_add_error($customservice_kf_list->get_error_code().'：'. $customservice_kf_list->get_error_message(),'error');
			return;
		}

		$customservice_kf_list	= array_combine(array_column($customservice_kf_list, 'kf_account'), $customservice_kf_list);

		return ($customservice_kf_list[$kf_account])??array();
	}

	public static function insert($data){
		global $current_tab;

		$kf_account		= $data['kf_account'];
		$kf_nick		= $data['kf_nick'];
		$kf_headimgurl	= $data['kf_headimgurl'];
		$kf_wx			= $data['kf_wx'];

		if($kf_account && $kf_nick){
			$response	= weixin()->add_customservice_kf_account(compact('kf_account', 'kf_nick'));

			if(is_wp_error($response)){
				return $response;
			}
			
			$response	= self::upload_kf_headimgurl($kf_account, $kf_headimgurl);
			if(is_wp_error($response)){
				return $response;
			}

			$response	= self::invite_kf_worker($kf_account, $kf_wx);
			if(is_wp_error($response)){
				return $response;
			}

			return true;
		}else{
			return new WP_Error('empty_kf_account_or_nick','客服账号和客服昵称不能为空');
		}
	}

	public static function update($kf_account, $data){
		$kf_nick		= $data['kf_nick'];
		$kf_headimgurl	= $data['kf_headimgurl'];
		$kf_wx			= $data['kf_wx'];

		if($kf_account && $kf_nick){
			$response	= weixin()->update_customservice_kf_account(compact('kf_account', 'kf_nick'));if(is_wp_error($response)){
				return $response;
			}
			
			$response	= self::upload_kf_headimgurl($kf_account, $kf_headimgurl);
			if(is_wp_error($response)){
				return $response;
			}

			$response	= self::invite_kf_worker($kf_account, $kf_wx);
			if(is_wp_error($response)){
				return $response;
			}

			return true;
		}
	}

	public static function upload_kf_headimgurl($kf_account, $kf_headimgurl){
		if($kf_headimgurl && ((strpos($kf_headimgurl, 'http://p.qlogo.cn/dkfheadimg') === false) || (strpos($kf_headimgurl, 'qpic.cn/') === false))){
			$media		= weixin_robot_download_remote_image($kf_headimgurl);
			return weixin()->upload_customservice_kf_account_headimg($kf_account, $media);
		}

		return true;
	}

	public static function invite_kf_worker($kf_account, $kf_wx){
		if($kf_wx){
			$kf = self::get($kf_account);

			if($kf && $kf['kf_wx'] != $kf_wx){
				return weixin()->invite_customservice_kf_account_worker($kf_account, $kf_wx);
			}
		}
		return true;
	}

	public static function delete($kf_account){
		return weixin()->delete_customservice_kf_account($kf_account);
	}

	public static function list($limit, $offset){
		$customservice_kf_list	= weixin()->get_customservice_kf_list();

		if(is_wp_error($customservice_kf_list)){
			wpjam_admin_add_error($customservice_kf_list->get_error_code().'：'. $customservice_kf_list->get_error_message(),'error');
			return;
		}

		$items	= $customservice_kf_list; 
		$total	= count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		$item['kf_headimgurl']	= '<img src="'.$item['kf_headimgurl'].'"  width="50" />';

		if(isset($item['kf_wx'])){

		}elseif(isset($item['invite_wx'])){
			$item['kf_wx'] = 
			'已经邀请：'.$item['invite_wx'].'<br />'.
			'过期时间：'.get_date_from_gmt(date('Y-m-d H:i:s',$item['invite_expire_time'])).'<br />'.
			'邀请状态：'.$item['invite_status'];
		}else{
			$item['kf_wx']	= '';
		}

		$customservice_online_kf_list	= weixin()->get_customservice_online_kf_list();

		if(!is_wp_error($customservice_online_kf_list) && $customservice_online_kf_list){
			$customservice_online_kf_list	= array_combine(array_column($customservice_online_kf_list, 'kf_account'), $customservice_online_kf_list);
			$item['status']			= $customservice_online_kf_list[$item['kf_account']]['status'];
			$item['accepted_case']	= $customservice_online_kf_list[$item['kf_account']]['accepted_case'];
		}else{
			$item['status']			= '';
			$item['accepted_case']	= '';
		}

		return $item;
	}
}