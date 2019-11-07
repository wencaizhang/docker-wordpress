<?php
Class WEAPP_AdminMasssendJob extends WEAPP_MasssendJob{

	public static function get_status_list(){
		return [
			self::STATUS_CREATED	=> '等待群发',
			self::STATUS_SENDING	=> '正在群发',
			self::STATUS_FINISHED	=> '发送完成',
			self::STATUS_CANCELED	=> '发送取消',
		];
	}

	public static function prepare($data){
		$template_id	= weapp_get_template_id($data['template_key'], self::get_appid());

		if(!$template_id || is_wp_error($template_id)){
			return $template_id;
		}

		$data['appid']	= self::get_appid();

		if($data['template_key'] == 'service_status'){

			foreach (['keyword1', 'keyword2', 'keyword3'] as $keyword) {
				if(wpjam_blacklist_check($data[$keyword])){
	                return new WP_Error('invalid_word','含有非法字符');
	            }
			}

			$keyword1 	= array('value'=>wpjam_mb_strimwidth($data['keyword1'], 0, 18), 'color'=>'#173177');	
			$keyword2 	= array('value'=>$data['keyword2']);
			$keyword3 	= array('value'=>$data['keyword3']);

			$template_data['data']				= compact('keyword1','keyword2','keyword3','keyword4');
			$template_data['emphasis_keyword']	= 'keyword1.DATA';
			$template_data['template_id']		= $template_id;
			$template_data['page']				= $data['path'];

			unset($data['keyword1'],$data['keyword2'],$data['keyword3'],$data['path']);

			$data['template_data']	= wpjam_json_encode($template_data);
		}

		return $data;
	}

	public static function insert($data){
		$data	= self::prepare($data);
		if(!$data || is_wp_error($data)){
			return $data;
		}

		return parent::insert($data);	
	}

	public static function update($id, $data){
		if(isset($data['template_key'])){
			$data	= self::prepare($data);
			if(!$data || is_wp_error($data)){
				return $data;
			}
		}

		parent::update($id, $data);
	}

	public static function get($id){
		$data	= parent::get($id);

		$data['template_data']	= wpjam_json_decode($data['template_data']);
		$data['path']			= $data['template_data']['page'];

		if($data['template_key'] == 'service_status'){
			for ($i=1; $i<4; $i++) { 
				$data['keyword'.$i]	= $data['template_data']['data']['keyword'.$i]['value'];
			}
		}

		$data['tag_ids']	= $data['tag_ids']??'';
		if($data['tag_ids']){
			$data['tag_ids']	= json_decode($data['tag_ids'], true);
		}else{
			$data['tag_ids']	= [-1];
		}

		return $data;
	}

	public static function can_send(){
		static $can_send;

		if(isset($can_send)){
			return $can_send;
		}

		$jobs	= self::Query()->where('appid', self::get_appid())->where_not('status', self::STATUS_CREATED)->where_gt('start_time', time()-WEEK_IN_SECONDS)->get_results();

		if($jobs){
			$can_send	= false;
		}else{
			$can_send	= true;
		}

		return $can_send;
	}

	public static function send($job_id){

		if(!self::can_send()){
			return new WP_Error('over_queta', '你本周的群发次数已用完！');
		}

		$job		= self::get($job_id);

		if($job['tag_ids'] != [-1]){
			$openids	= WPJAM_ShopUserTagRelationship::Query()
						->where('blog_id', get_current_blog_id())
						->where('appid', weapp_get_appid())
						->where('tag_id', $job['tag_ids'])
						->group_by('openid')
						->get_col('openid');

			if(count($openids) > 0){
				$form_ids	= weapp_get_form_ids($openids, true, $job['appid']);
			}else{
				$form_ids	= false;
			}
		}else{
			$form_ids	= weapp_get_form_ids(null, true, $job['appid']);
		}		

		if($form_ids){
			$datas	= array_map(function($data) use($job_id){ unset($data['id']); $data['job_id'] = $job_id; return $data; }, $form_ids);

			WEAPP_MasssendLog::insert_multi($datas);

			wp_schedule_single_event(time()+5,'weapp_scheduled_send_template_job',array($job_id));

			$count		= count($form_ids);
			$status		= self::STATUS_SENDING;
			$start_time	= time();
			return self::update($job_id, compact('status', 'count', 'start_time'));
		}else{
			return self::finish($job_id);	// 无用户可发，就直接完成
		}
	}

	public static function cancel($job_id){
		WEAPP_MasssendLog::cancel($job_id);

		$data['status']		= self::STATUS_CANCELED;
		$data['end_time']	= time();
		return self::update($job_id, $data);
	}

	public static function list($limit, $offset){
		if(!self::can_send()){
			echo '<p>你本周群发次数已用完！</p>';
		}
		self::Query()->where('appid', self::get_appid());
		return parent::list($limit, $offset);
	}

	public static function item_callback($item){
		if(empty($item['keyword1'])){
			$template_data	= wpjam_json_decode($item['template_data']);
			$item['title']	= $template_data['data']['keyword1']['value'];
		}else{
			$item['title']	= $item['keyword1'];
		}

		if($item['status'] == self::STATUS_CREATED){
			$item['success']	= $item['failed']	= '';

			unset($item['row_actions']['cancel']);
			if(!self::can_send()){
				unset($item['row_actions']['send']);
			}
		}elseif($item['status'] == self::STATUS_SENDING){
			$item['success']	= $item['failed']	= '';
			unset($item['row_actions']['send']);
			unset($item['row_actions']['edit']);
			unset($item['row_actions']['delete']);
		}elseif($item['status'] == self::STATUS_FINISHED){
			unset($item['row_actions']['edit']);
			unset($item['row_actions']['send']);
			unset($item['row_actions']['cancel']);
			unset($item['row_actions']['delete']);
		}elseif($item['status'] == self::STATUS_CANCELED){
			unset($item['row_actions']['edit']);
			unset($item['row_actions']['send']);
			unset($item['row_actions']['cancel']);
			unset($item['row_actions']['delete']);
		}

		$item['start_time']	= $item['start_time']?get_date_from_gmt(date('Y-m-d H:i:s',$item['start_time'])):'';
		$item['end_time']	= $item['end_time']?get_date_from_gmt(date('Y-m-d H:i:s',$item['end_time'])):'';

		return $item;
	}
}