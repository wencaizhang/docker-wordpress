<?php
add_filter('wpjam_crons_list_table', function(){
	return [
		'title'		=> '定时作业',
		'plural'	=> 'crons',
		'singular' 	=> 'cron',
		'fixed'		=> false,
		'ajax'		=> true,
		'model'		=> 'WPJAM_Cron'
	];
});

class WPJAM_Cron{
	public static function get_primary_key(){
		return 'cron_id';
	}

	public static function get($id){
		list($timestamp, $hook, $key)	= explode('--', $id);

		$wp_crons = _get_cron_array();

		if(isset($wp_crons[$timestamp][$hook][$key])){
			$data	= $wp_crons[$timestamp][$hook][$key];
			$data['hook']		= $hook;	
			$data['timestamp']	= $timestamp;
			$data['time']		= get_date_from_gmt(date('Y-m-d H:i:s', $timestamp));
			$data['cron_id']	= $id;
			$data['_args']		= $data['args']?implode(',', $data['args']):'';
			$data['interval']	= isset($data['interval'])?$data['schedule'].'（'.$data['interval'].'）':'';
			return $data;
		}else{
			return new WP_Error('cron_not_exist', '该定时作业不存在');
		}
	}

	public static function insert($data){

		if(!has_filter($data['hook'])){
			return new WP_Error('invalid_hook', '非法 hook');
		}

		$timestamp	= strtotime(get_gmt_from_date($data['time']));

		if($data['interval']){
			wp_schedule_event($timestamp, $data['interval'], $data['hook'], $data['_args']);
		}else{
			wp_schedule_single_event($timestamp, $data['hook'], $data['_args']);
		}

		return true;
	}

	public static function do($id){
		$data = self::get($id);

		if(is_wp_error($data)){
			return $data;
		}else{
			// wp_unschedule_event($data['timestamp'], $data['hook'], $data['args']);
			do_action_ref_array($data['hook'], $data['args']);
			return true;
		}
	}

	public static function delete($id){
		$data = self::get($id);
		
		if(is_wp_error($data)){
			return $data;
		}else{
			wp_unschedule_event($data['timestamp'], $data['hook'], $data['args']);
			return true;
		}
	}

	// 后台 list table 显示
	public static function list($limit, $offset){
		$items	= array();

		foreach (_get_cron_array() as $timestamp => $wp_cron) {
			foreach ($wp_cron as $hook => $dings) {
				foreach( $dings as $key=>$data ) {
					if(!has_filter($hook)){
						wp_unschedule_event($timestamp, $hook, $data['args']);	// 系统不存在的定时作业，自动清理
						continue;
					}

					$schedule	= $schedules[$data['schedule']] ?? $data['interval']??'';
					
					$items[] = array(
						'cron_id'	=> $timestamp.'--'.$hook.'--'.$key,
						'time'		=> get_date_from_gmt( date('Y-m-d H:i:s', $timestamp) ),
						'hook'		=> $hook,
						'_args'		=> $data['args']?implode(',', $data['args']):'',
						'interval'	=> $data['interval'] ?? 0
					);
				}
			}
		}

		$total = count($items);

		return compact('items', 'total');
	}

	public static function get_actions(){
		return [
			'add'		=> ['title'=>'新建',		'response'=>'list'],
			'do'		=> ['title'=>'立即执行',	'direct'=>true,	'response'=>'list'],
			'delete'	=> ['title'=>'删除',		'direct'=>true,	'response'=>'list']
		];
	}

	public static function get_fields($action_key='', $id=0){
		$schedule_options	= [0=>'只执行一次']+wp_list_pluck(wp_get_schedules(), 'display', 'interval');
		
		return [
			'hook'		=> ['title'=>'Hook',	'type'=>'text',		'show_admin_column'=>true],
			'_args'		=> ['title'=>'参数',		'type'=>'mu-text',	'show_admin_column'=>true],
			'time'		=> ['title'=>'运行时间',	'type'=>'text',		'show_admin_column'=>true,	'value'=>current_time('mysql')],
			'interval'	=> ['title'=>'频率',		'type'=>'select',	'show_admin_column'=>true,	'options'=>$schedule_options],
		];
	}
}