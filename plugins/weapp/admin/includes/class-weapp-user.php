<?php
Class WEAPP_AdminUser extends WEAPP_User{
	public static function list($limit, $offset){
		if(empty($_GET['orderby'])){
			self::get_handler()->order_by('time');
		}
		return parent::list($limit, $offset);
	}

	public static function item_callback($item){
		global $current_admin_url;

		$item = self::prepare($item);

		$item['address']	= '<a href="'.$current_admin_url.'&province='.$item['province'].'">'.$item['province'] . '</a>'. ' ' . '<a href="'.$current_admin_url.'&city='.$item['city'].'">'.$item['city'];

		$item['time']		= get_date_from_gmt(date('Y-m-d H:i:s',$item['time']));
		$item['modified']	= $item['modified']?get_date_from_gmt(date('Y-m-d H:i:s',$item['modified'])):'';

		$item['openid']	= 'Openid：'.$item['openid'];
		if($item['unionid']){
			$item['openid']	= $item['openid'].'<br />UnionID：'.$item['unionid'];
		}

		if(!empty($item['user_id'])){
			$item['openid']	= $item['openid'].'<br />USER_ID：'.$item['user_id'];
		}

		return $item;
	}

	public static function get_actions(){
		return [];
	}

	public static function get_fields($action_key='', $id=''){
		$genders	= [0=>'未知', 1=>'男', 2=>'女'];

		return [
			'username'	=> ['title'=>'用户',	'type'=>'view',	'show_admin_column'=>true],
			'gender'	=> ['title'=>'性别',	'type'=>'view',	'show_admin_column'=>true,	'options'=>$genders],
			'address'	=> ['title'=>'地区',	'type'=>'view',	'show_admin_column'=>true],
			// 'province'	=> ['title'=>'省份',	'type'=>'view',	'show_admin_column'=>true],
			// 'city'		=> ['title'=>'城市',	'type'=>'view',	'show_admin_column'=>true],
			'openid'	=> ['title'=>'OpenID',	'type'=>'view',	'show_admin_column'=>true],
			'time'		=> ['title'=>'注册时间',	'type'=>'view',	'show_admin_column'=>'only'],
			'modified'	=> ['title'=>'最近访问',	'type'=>'view',	'show_admin_column'=>'only'],
		];
	}
}