<?php
class WPJAM_Role  {
	public static function get($role){
		global $wp_roles;
		$roles	= $wp_roles->roles;

		$arr	= $roles[$role] ?? [];

		$user_counts	= count_users();
		$user_counts	= $user_counts['avail_roles'];

		if($arr){
			$arr['role']				= $role;
			$arr['capabilities']		= array_keys($arr['capabilities']);
			$arr['capabilities_count']	= count($arr['capabilities']);
			$arr['user_count']			= isset($user_counts[$role])?('<a href="'.admin_url('users.php?role='.$role).'">'.$user_counts[$role].'</a>'):0;
		}

		return $arr;
	}

	public static function prepare($data){
		$capabilities	= [];

		if($data['capabilities']){
			foreach ($data['capabilities'] as $capability) {
				if($capability){
					$capabilities[$capability]	= 1;
				}	
			}
		}

		$data['capabilities']	= $capabilities;

		return $data;
	}

	public static function insert($data){

		$data	= self::prepare($data);

		$role			= $data['role'];
		$name			= $data['name'];
		$capabilities	= $data['capabilities'];

		$result	= add_role($role, $name, $capabilities);

		if($result == null){
			return new WP_Error('insert_error', '新增失败，可能重名或者其他原因。');
		}

		return $role;
	}

	public static function update($role, $data){
		$data	= self::prepare($data);

		$name			= $data['name'];
		$capabilities	= $data['capabilities'];

		remove_role($role);
		$result	= add_role($role, $name, $capabilities);

		if($result == null){
			return new WP_Error('insert_error', '修改失败，可能重名或者其他原因。');
		}

		return true;
	}

	public static function delete($role){
		if($role == 'administrator'){
			return  new WP_Error('delete_error', '不能超级管理员角色。');
		}

		return remove_role($role);
	}

	// 后台 list table 显示
	public static function list($limit, $offset){
		global $wp_roles;

		$roles 	= $wp_roles->roles;

		$items	= [];

		$user_counts	= count_users();
		$user_counts	= $user_counts['avail_roles'];

		foreach ($roles as $key => $role) {
			$role['role']				= $key;
			$role['name']				= translate_user_role($role['name']);
			$role['user_count']			= isset($user_counts[$key])?('<a href="'.admin_url('users.php?role='.$key).'">'.$user_counts[$key].'</a>'):0;
			$role['capabilities_count']	= count($role['capabilities']);

			$items[]	= $role;
		}

		$total = count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		if($item['role'] == 'administrator'){
			unset($item['row_actions']['delete']);
		}
		return $item;
	}

	public static function get_fields($action_key='', $id=0){
		$fields = [
			'role'				=> ['title'=>'角色',		'type'=>'text',		'show_admin_column'=>true],
			'name'				=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>true],
			'capabilities'		=> ['title'=>'权限',		'type'=>'mu-text'],
			'user_count'		=> ['title'=>'用户数',	'type'=>'view',		'show_admin_column'=>'only'],
			'capabilities_count'=> ['title'=>'权限',		'type'=>'view',		'show_admin_column'=>'only'],
		];

		if($action_key == 'edit'){
			$fields['role']['type']	= 'view';
		}

		return $fields;
	}
}

add_filter('wpjam_roles_list_table', function(){
	return [
		'title'				=> '用户角色',
		'singular'			=> 'wpjam-role',
		'plural'			=> 'wpjam-roles',
		'primary_column'	=> 'role',
		'primary_key'		=> 'role',
		'model'				=> 'WPJAM_Role',
		'ajax'				=> true,
	];
});

