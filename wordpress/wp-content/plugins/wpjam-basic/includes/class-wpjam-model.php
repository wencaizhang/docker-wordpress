<?php
abstract class WPJAM_Model{
	// protected $data;

	// public function __construct(array $data=[]){
	// 	$this->data = $data;
	// }
	public static function get_handler(){
		
	}

	public static function Query($args=[]){
		if($args){
			return new WPJAM_Query(static::get_handler(), $args);
		}else{
			return static::get_handler();
		}
	}

	public static function get_last_changed(){
		return static::get_handler()->get_last_changed();
	}

	public static function get_list_cache(){
		return new WPJAM_listCache(static::get_handler()->get_cache_group());
	}

	public static function get_cache_group(){
		return static::get_handler()->get_cache_group();
	}

	public static function find_one_by($field, $value){
		return static::get_handler()->find_one_by($field, $value);
	}

	public static function find_one($id){
		return static::get_handler()->find_one($id);
	}

	public static function get($id){
		return static::get_handler()->get($id);
	}

	public static function find_by($field, $value, $order='ASC'){
		return static::get_handler()->find_by($field, $value, $order);
	}

	public static function get_by($field, $value, $order='ASC'){
		return static::get_handler()->get_by($field, $value, $order);
	}

	public static function get_ids($ids){
		return static::get_by_ids($ids);
	}

	public static function get_by_ids($ids){
		return static::get_handler()->get_by_ids($ids);
	}

	public static function get_by_cache_keys($values){
		return static::update_caches($values);
	}

	public static function update_caches($values){
		return static::get_handler()->update_caches($values);
	}

	public static function get_all(){
		return static::get_handler()->get_results();
	}

	public static function insert($data){
		return static::get_handler()->insert($data);
	}

	public static function insert_multi($datas){
		return static::get_handler()->insert_multi($datas);
	}

	public static function update($id, $data){
		return static::get_handler()->update($id, $data);
	}

	public static function delete($id){
		return static::get_handler()->delete($id);
	}

	public static function move($id, $data){
		return static::get_handler()->move($id, $data);
	}
	
	public static function delete_by($field, $value){
		return static::get_handler()->delete(array($field=>$value));
	}

	public static function delete_multi($ids){
		if(method_exists(static::get_handler(), 'delete_multi')){
			return static::get_handler()->delete_multi($ids);
		}elseif($ids){
			foreach($ids as $id){
				$result	= static::get_handler()->delete($id);
				if(is_wp_error($result)){
					return $result;
				}
			}

			return $result;
		}
	}

	public static function get_searchable_fields(){
		if(method_exists(static::get_handler(), 'get_searchable_fields')){
			return static::get_handler()->get_searchable_fields(); 
		}else{
			return [];
		}
	}

	public static function get_filterable_fields(){
		if(method_exists(static::get_handler(), 'get_filterable_fields')){
			return static::get_handler()->get_filterable_fields(); 
		}else{
			return [];
		}
	}

	public static function get_primary_key(){
		return static::get_handler()->get_primary_key();
	}

	// 后台 list table 显示
	public static function query_items($limit, $offset){
		if(method_exists(static::get_handler(), 'query_items')){
			return static::get_handler()->query_items($limit, $offset);
		}
	}

	public static function list($limit, $offset){
		// _deprecated_function(__METHOD__, 'WPJAM Basic 3.7', 'WPJAM_Model::query_items');
		if(method_exists(static::get_handler(), 'query_items')){
			return static::get_handler()->query_items($limit, $offset);
		}
	}

	// 后台 item 处理
	public static function item_callback($item){
		if(method_exists(static::get_handler(), 'item_callback')){
			return static::get_handler()->item_callback($item);
		}
	}

	// 后台 views 处理
	public static function views(){
		if(method_exists(static::get_handler(), 'views')){
			return static::get_handler()->views();
		}
	}
}

class WPJAM_Query{
	public $request;
	public $query_vars;
	public $datas;
	public $max_num_pages	= 0;
	public $found_rows 		= 0;
	public $next_first 		= 0;
	public $next_cursor 	= 0;
	public $handler;

	public function __construct($handler, $query='') {

		$this->handler	= $handler;

		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	public function query( $query ) {
		$this->query_vars = wp_parse_args( $query, array(
			'first'		=> null,
			'cursor'	=> null,
			'orderby'	=> null,
			'order'		=> 'DESC',
			'number'	=> 20,
			'search'	=> '',
			'offset'	=> null
		));

		$last_changed	= $this->handler->get_last_changed();
		$key			= md5(maybe_serialize($this->query_vars));
		$cache_key		= 'wpjam_query:'.$key.':'.$last_changed;
		$cache_group	= $this->handler->get_cache_group();

		$orderby 		= $this->query_vars['orderby']?:'id';
		$cache_it		= ($orderby == 'rand') ? false : true;

		$result			= $cache_it ? wp_cache_get($cache_key, $cache_group) : false;

		if($result === false){
			foreach ($this->query_vars as $key => $value) {
				if($value === null){
					continue;
				}

				if($key == 'number'){
					if($value != -1){
						$this->handler->limit($value);
					}
				}elseif($key == 'offset'){
					$this->handler->offset($value);
				}elseif($key == 'orderby'){
					$this->handler->order_by($value);
				}elseif($key == 'order'){
					$this->handler->order($value);
				}elseif($key == 'first'){
					$this->handler->where_gt($orderby, $value);
				}elseif($key == 'cursor'){
					if($value > 0){
						$field = $this->query_vars['orderby']??'id';
						$this->handler->where_lt($orderby, $value);
					}
				}elseif($key == 'search'){
					$this->handler->search($value);
				}elseif(strpos($key, '__in_set')){
					$this->handler->find_in_set($value, str_replace('__in_set', '', $key));
				}elseif(strpos($key, '__in')){
					$this->handler->where_in(str_replace('__in', '', $key), $value);
				}elseif(strpos($key, '__not_in')){
					$this->handler->where_not_in(str_replace('__not_in', '', $key), $value);
				}else{
					$this->handler->where($key, $value);
				}
			}
			
			$result	= [
				'datas'			=> $this->handler->get_results(),
				'request'		=> $this->handler->get_request(),
				'found_rows'	=> $this->handler->find_total(),
			];

			if($cache_it){
				wp_cache_set($cache_key, $result, $cache_group, DAY_IN_SECONDS);
			}
		}
		
		$this->datas		= $result['datas'];
		$this->request		= $result['request'];
		$this->found_rows	= $result['found_rows'];	

		if ($this->found_rows && $this->query_vars['number'] && $this->query_vars['number'] != -1){
			$this->max_num_pages = ceil($this->found_rows / $this->query_vars['number']);

			if($this->query_vars['offset'] === null){
				if($this->found_rows > $this->query_vars['number']){				
					$this->next_cursor	= (int)$this->datas[count($this->datas)-1][$orderby];
				}
			}
		}

		return $this->datas;
	}
}

class WPJAM_DB{
	private $table;
	private $primary_key;
	private $field_types;
	private $searchable_fields;
	private $filterable_fields;

	private $limit			= 0;
	private $offset			= 0;
	private $order_by		= '';
	private $group_by		= '';
	private $having			= '';
	private $order			= 'DESC';
	private $where			= [];
	private $search_term	= null;
	private $conditions		= '';

	private $cache			= true;
	private $cache_key		= null;
	private $cache_group	= null;

	public function __construct($table, array $args = []){
		$this->table	= $table;
		$args = wp_parse_args($args, array(
			'primary_key'		=> 'id',
			'cache'				=> true,
			'cache_key'			=> '',
			'cache_group'		=> $table,
			'cache_time'		=> DAY_IN_SECONDS,
			'field_types'		=> [],
			'searchable_fields'	=> [],
			'filterable_fields'	=> [],
		));

		$this->primary_key			= $args['primary_key'];
		$this->order_by				= $args['primary_key'];
		$this->cache_group			= $args['cache_group'];
		$this->cache				= $args['cache'];
		$this->cache_time			= $args['cache_time'];
		$this->cache_key			= $args['cache_key']?:$args['primary_key'];
		$this->field_types			= $args['field_types'];
		$this->searchable_fields	= $args['searchable_fields'];
		$this->filterable_fields	= $args['filterable_fields'];
	}

	public function get_table(){
		return $this->table;
	}

	public function get_cache_key($key){
		return $this->cache_key.'_'.$key;
	}

	public function cache_get($key){
		if($this->cache){
			if($this->cache_key == $this->primary_key){
				return $this->cache_get_by_primary_key($key);
			}else{
				if(defined('CACHE_DEBUG')){
					trigger_error('cache_get_'.$this->get_cache_key($key));
				}

				if(!is_scalar($key)){
					trigger_error(var_export($key, true));
					return false;
				}
			
				return wp_cache_get($this->get_cache_key($key), $this->cache_group);
			}
		}else{
			return false;
		}
	}

	public function cache_get_by_primary_key($id){
		if($this->cache){
			if(defined('CACHE_DEBUG')){
				trigger_error('cache_get_by_primary_key_'.$id);
			}

			if(!is_scalar($id)){
				trigger_error(var_export($id, true));
				return false;
			}

			return wp_cache_get($id, $this->cache_group);
		}else{
			return false;
		}
	}

	public function cache_set($key, $data, $cache_time=0){
		if($this->cache){
			$cache_time	= $cache_time ?: $this->cache_time;
			if($this->cache_key == $this->primary_key){
				$this->cache_set_by_primary_key($key, $data, $cache_time);
			}else{
				if(defined('CACHE_DEBUG')){
					trigger_error('cache_set_'.$this->get_cache_key($key));
				}
				wp_cache_set($this->get_cache_key($key), $data, $this->cache_group, $cache_time);
			}
		}
	}

	public function cache_set_by_primary_key($id, $data, $cache_time=0){
		if($this->cache){
			if(defined('CACHE_DEBUG')){
				trigger_error('cache_set_by_primary_key_'.$id);
			}
			$cache_time	= $cache_time ?: $this->cache_time;
			wp_cache_set($id, $data, $this->cache_group, $cache_time);
		}
	}

	public function cache_delete($key){
		if($this->cache){
			if(defined('CACHE_DEBUG')){
				trigger_error('cache_delete_'.$this->get_cache_key($key));
			}
			wp_cache_delete($this->get_cache_key($key), $this->cache_group);
		}
	}

	public function cache_delete_by_primary_key($id){
		if($this->cache){
			if(defined('CACHE_DEBUG')){
				trigger_error('cache_delete_by_primary_key_'.$id);
			}
			wp_cache_delete($id, $this->cache_group);
		}
	}

	public function cache_delete_multi($keys){
		if($this->cache){
			$keys	= array_map(function($key){ return $this->get_cache_key($key); }, $keys);
			wp_cache_delete_multi($keys, $this->cache_group);
		}
	}

	public function cache_delete_multi_by_primary_key($ids){
		if($this->cache){
			wp_cache_delete_multi($ids, $this->cache_group);
		}
	}

	public function cache_delete_by_conditions($conditions){
		if($this->cache){
			if(empty($conditions)){
				return;
			}
			
			if(is_array($conditions)){
				$conditions	= array_filter($conditions, function($condition){
					return $condition;
				});

				if(empty($conditions)){
					return;
				}

				$conditions		= ' WHERE ' . implode(' OR ', $conditions);
			}

			global $wpdb;

			if($this->cache_key != $this->primary_key){
				if($results = $wpdb->get_results("SELECT {$this->primary_key}, {$this->cache_key} FROM `{$this->table}` {$conditions}", ARRAY_A)){
					// $primary_key	= $this->primary_key;
					// $cache_key		= $this->cache_key;

					foreach ($results as $result) {
						$this->cache_delete_by_primary_key($result[$this->primary_key]);
						$this->cache_delete($result[$this->cache_key]);
					}
					// $this->cache_delete_multi_by_primary_key(array_column($results, $this->primary_key));
					// $this->cache_delete_multi(array_column($results, $this->cache_key));
				}
			}else{
				if($ids = $wpdb->get_col("SELECT {$this->primary_key} FROM `{$this->table}` {$conditions}")){
					// $this->cache_delete_multi_by_primary_key($ids, $this->cache_group);
					foreach ($ids as $id) {
						$this->cache_delete_by_primary_key($id);
					}
				}
			}
		}
	}

	public function get_cache_group(){
		return $this->cache_group;
	}

	public function get_last_changed(){
		return wp_cache_get_last_changed($this->cache_group);
	}

	public function set_last_changed(){
		wp_cache_set('last_changed', microtime(), $this->cache_group);
	}

	public function get_primary_key(){
		return $this->primary_key;
	}

	public function get_searchable_fields(){
		return $this->searchable_fields;
	}

	public function get_filterable_fields(){
		return $this->filterable_fields;
	}

	public function find_one_by($field, $value){
		global $wpdb;

		$field_type	= $this->process_field_formats($field);
		
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$this->table}` WHERE `{$field}` = {$field_type}", $value), ARRAY_A);
	}

	public function find_by($field, $value, $order='ASC'){
		global $wpdb;

		$field_type	= $this->process_field_formats($field);
		
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$this->table}` WHERE `{$field}` = {$field_type} ORDER BY `{$this->primary_key}` {$order}", $value), ARRAY_A);
	}

	public function find_one($id){
		$result = $this->cache_get_by_primary_key($id);
		if($result === false){
			$result = $this->find_one_by($this->primary_key, $id);
			if($result){
				$this->cache_set_by_primary_key($id, $result);
			}else{
				$this->cache_set_by_primary_key($id, $result, MINUTE_IN_SECONDS);
			}
		}

		return $result;
	}

	public function get($id){
		return $this->find_one($id);
	}

	public function get_by($field, $value, $order='ASC'){
		if($field == $this->cache_key){
			$result = $this->cache_get($value);

			if($result === false){
				$result = $this->find_by($field, $value, $order);
				if($result){
					$this->cache_set($value, $result);
				}else{
					$this->cache_set($value, $result, MINUTE_IN_SECONDS);
				}
			}

			return $result;
		}else{
			return $this->find_by($field, $value, $order);
		}
	}

	public function get_values_by($ids, $field){
		global $wpdb;
		
		$result = $wpdb->get_results($this->where_in($field, $ids)->get_sql(), ARRAY_A);

		if($result){
			if($field == $this->primary_key){
				return array_combine(array_column($result, $this->primary_key), $result);
			}else{
				$return = [];
				foreach ($ids as $id) {
					$return[$id]	= array_values(wp_list_filter($result, array($field => $id)));
				}
				return $return;
			}
		}else{
			return [];
		}
	}

	public function update_caches($ids, $primary=false){
		if(!$this->cache){
			return [];
		}

		if($ids && is_array($ids)){
			$ids = array_filter($ids);
			$ids = array_unique($ids);
		}else{
			return [];
		}

		$non_cached_ids = $caches = [];

		foreach ($ids as $id) {
			if($primary){
				$data	= $this->cache_get_by_primary_key($id);
			}else{
				$data	= $this->cache_get($id);
			}
			
			if(false === $data){
				$non_cached_ids[]	= $id;
			}else{
				$caches[$id]		= $data;
			}
		}

		if (empty($non_cached_ids)){
			return $caches;
		}

		if($primary){
			$datas	= self::get_values_by($non_cached_ids, $this->primary_key);
		}else{
			$datas	= self::get_values_by($non_cached_ids, $this->cache_key);
		}

		foreach ($non_cached_ids as $id) {
			$caches[$id]	= $datas[$id]??[];
			// $cache_time		= $caches[$id] ? $this->cache_time : MINUTE_IN_SECONDS;
			$cache_time		= $this->cache_time;
			if($primary){
				$this->cache_set_by_primary_key($id, $caches[$id], $cache_time);
			}else{
				$this->cache_set($id, $caches[$id], $cache_time);
			}
		}
		
		return $caches;
	}

	public function get_ids($ids){
		return self::update_caches($ids, $primary=true);
	}

	public function get_by_ids($ids){
		return self::update_caches($ids, $primary=true);
	}

	public function get_results($fields=[]){
		return $this->find($fields);
	}

	public function get_col($field=''){
		return $this->find($field, 'get_col');
	}

	public function get_var($field=''){
		return $this->find($field, 'get_var');
	}

	public function get_row($fields=[]){
		return $this->find($fields, 'get_row');
	}

	public function get_sql($fields=[]){
		return $this->find($fields, 'get_sql');
	}

	public function find($fields=[], $func='get_results'){
		global $wpdb;
		
		$order	= '';
		$limit	= '';
		$offset	= '';

		if($fields){
			if(is_array($fields)){
				$fields	= '`'.implode( '`, `', $fields ). '`';
				$fields	= esc_sql($fields); 
			}
		}else{
			$fields = '*';
		}

		// Group
		if ($this->group_by) {
			if (strstr($this->group_by, ',') !== false || strstr($this->group_by, '(') !== false) {
				$group = ' GROUP BY ' . $this->group_by;
			}else{
				$group = ' GROUP BY `' . $this->group_by . '`';
			}
		}else{
			$group = '';
		}

		// Having
		if ($this->having) {
			$having = ' HAVING ' . $this->having;
		}else{
			$having = '';
		}

		// Order
		$order = '';
		if($this->order_by){
			if (is_array($this->order_by)){
				$order	= [];
				foreach ($this->order_by as $k => $v) {
					$order[]	= '`' . $k . '`' . $v . ' ';
				}

				$order	= ' ORDER BY '.implode(',', $order);
			} elseif (strstr($this->order_by, '(') !== false && strstr($this->order_by, ')') !== false) {
				$order = ' ORDER BY ' . $this->order_by;
			} elseif (strstr($this->order_by, ',') !== false ) {
				$order = ' ORDER BY ' . $this->order_by;
			} else {
				$order = ' ORDER BY `' . $this->order_by . '` ' . $this->order;
			}
		}
		// Limit
		if ($this->limit > 0) {
			$limit = ' LIMIT ' . $this->limit;
		}
		
		// Offset
		if ($this->offset > 0) {
			$offset = ' OFFSET ' . $this->offset;
		}

		$conditions	= $this->get_conditions();

		if(($func == 'get_results' || $func == 'get_col') && $limit){
			$this->conditions	= $conditions;
		}

		$sql =  "SELECT {$fields} FROM `{$this->table}` {$conditions} {$group} {$having} {$order} {$limit} {$offset}";

		if($func == 'get_sql'){
			return $sql;
		}elseif($func == 'get_results' || $func == 'get_row'){
			// $sql	=  "SELECT SQL_CALC_FOUND_ROWS {$fields} FROM `{$this->table}` {$conditions} {$group} {$order} {$limit} {$offset}";
			$results	=  $wpdb->$func($sql, ARRAY_A);
		}else{
			$results	= $wpdb->$func($sql);
		}

		if($func == 'get_results' && $results && $fields=='*'){
			// $this->get_by_ids(array_column($results, $this->primary_key));

			// if($this->primary_key != $this->cache_key){
			// 	$this->update_caches(array_column($results, $this->cache_key));
			// }

			foreach ($results as $result) {
				$this->cache_set_by_primary_key($result[$this->primary_key], $result);
			}
		}
		
		return $results;
	}

	public function get_request(){
		global $wpdb;
		return $wpdb->last_query;
	}

	public function last_query(){
		global $wpdb;
		return $wpdb->last_query;
	}

	public function find_total($group_by=false){
		global $wpdb;

		if($group_by){
			return $wpdb->get_var("SELECT FOUND_ROWS();");
		}else{
			return $wpdb->get_var("SELECT count(*) FROM `{$this->table}` {$this->conditions}");
		}
	}

	public function insert_multi($datas){	// 使用该方法，自增的情况可能无法无法删除缓存，请注意
		global $wpdb;

		$this->set_last_changed();

		if(empty($datas)){
			return new WP_Error('empty_datas', '数据为空');
		}

		$data		= current($datas);

		$formats	= $this->process_field_formats($data);
		$values		= [];	
		$fields		= '`'.implode('`, `', array_keys($data)).'`';
		$updates	= implode(', ', array_map(function($field){ return "`$field` = VALUES(`$field`)"; }, array_keys($data)));

		$cache_keys		= [];
		$primary_keys	= [];

		foreach ($datas as $data) {
			if($data){
				foreach ($data as $k => $v) {
					if(is_array($v)){
						trigger_error($k.'的值是数组：'.var_export($data,true));
						continue;
					}
				}

				$values[]	= $wpdb->prepare('('.implode(', ', $formats).')', array_values($data));
				
				if(!empty($data[$this->primary_key])){
					$this->cache_delete_by_primary_key($data[$this->primary_key]);

					$primary_keys[]	= $data[$this->primary_key];
				}

				if($this->cache_key != $this->primary_key && !empty($data[$this->cache_key])){
					$this->cache_delete($data[$this->cache_key]);

					$cache_keys[]	= $data[$this->cache_key];
				}
			}
		}

		// if($primary_keys){
		// 	$this->cache_delete_multi_by_primary_key($primary_keys);
		// }

		// if($cache_keys){
		// 	$this->cache_delete_multi($cache_keys);
		// }

		if($this->cache_key != $this->primary_key){
			$conditions	= [];

			if($primary_keys){
				$this->where_in($this->primary_key, $primary_keys);
				$conditions[]	= $this->get_conditions(false);
			}

			if($cache_keys){
				$this->where_in($this->cache_key, $cache_keys);
				$conditions[]	= $this->get_conditions(false);
			}

			$this->cache_delete_by_conditions($conditions);
		}	

		$values	= implode(',', $values);
		$sql	=  "INSERT INTO `$this->table` ({$fields}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";
		
		if(isset($_GET['debug'])){
			echo $sql;	
		}

		$result	= $wpdb->query($sql);

		if(false === $result){
			return new WP_Error('insert_error', $wpdb->last_error);
		}else{
			return $result;	
		}	
	}

	public function insert($data){
		global $wpdb;

		$this->set_last_changed();

		if(!empty($data[$this->primary_key])){
			$this->cache_delete_by_primary_key($data[$this->primary_key]);
		}

		if($this->primary_key != $this->cache_key){
			$conditions = [];
			
			if(!empty($data[$this->primary_key])){
				$this->where($this->primary_key, $data[$this->primary_key]);	
				$conditions[] = $this->get_conditions(false);
			}
			
			if(!empty($data[$this->cache_key])){
				$this->cache_delete($data[$this->cache_key]);

				$this->where($this->cache_key, $data[$this->cache_key]);
				$conditions[] = $this->get_conditions(false);
			}

			$this->cache_delete_by_conditions($conditions);
		}

		if(!empty($data[$this->primary_key])){
			$data 		= array_filter($data, function($v){ return !is_null($v); });

			$formats	= $this->process_field_formats($data);
			$fields		= implode(', ', array_keys($data));
			$values		= $wpdb->prepare(implode(', ',$formats), array_values($data));
			$updates	= implode(', ', array_map(function($field){ return "`$field` = VALUES(`$field`)"; }, array_keys($data)));

			$wpdb->check_current_query = false;

			if(false === $wpdb->query("INSERT INTO `$this->table` ({$fields}) VALUES ({$values}) ON DUPLICATE KEY UPDATE {$updates}")){
				return new WP_Error('insert_error', $wpdb->last_error);
			}else{
				return $data[$this->primary_key];	
			}
			
		}else{
			$formats	= $this->process_field_formats($data);
			$result 	= $wpdb->insert($this->table, $data, $formats);
			
			if($result === false){
				return new WP_Error('insert_error', $wpdb->last_error);
			}else{
				$this->cache_delete_by_primary_key($wpdb->insert_id);
				return $wpdb->insert_id;
			}
		}	
	}

	/*
	用法：
	update($data, $where);
	update($id, $data);
	update($data); // $where各种 参数通过 where() 方法事先传递
	*/
	public function update(){
		global $wpdb;

		$this->set_last_changed();

		$args_num = func_num_args();
		$args = func_get_args();

		if($args_num == 2){
			if(is_array($args[0])){
				$data	= $args[0];
				$where 	= $args[1];

				$conditions = [];

				$this->where_all($where);
				$conditions[] = '('.$this->get_conditions(false).')';

				if(!empty($data[$this->primary_key])){
					$this->cache_delete_by_primary_key($data[$this->primary_key]);

					$this->where($this->primary_key, $data[$this->primary_key]);
					$conditions[] = $this->get_conditions(false);
				}

				if($this->primary_key != $this->cache_key){
					if(!empty($data[$this->cache_key])){
						$this->cache_delete($data[$this->cache_key]);

						$this->where($this->cache_key, $data[$this->cache_key]);
						$conditions[] = $this->get_conditions(false);
					}
				}

				$this->cache_delete_by_conditions($conditions);
			}else{
				$id		=$args[0];
				$where	= array($this->primary_key=>$id);
				$data	= $args[1];

				$conditions = [];

				$this->cache_delete_by_primary_key($id);

				$this->where($this->primary_key, $id);
				$conditions[] = $this->get_conditions(false);

				if(!empty($data[$this->primary_key])){
					$this->cache_delete_by_primary_key($data[$this->primary_key]);

					$this->where($this->primary_key, $data[$this->primary_key]);
					$conditions[] = $this->get_conditions(false);
				}

				if($this->primary_key != $this->cache_key){
					if(!empty($data[$this->cache_key])){
						$this->cache_delete($data[$this->cache_key]);

						$this->where($this->cache_key, $data[$this->cache_key]);
						$conditions[] = $this->get_conditions(false);	
					}
					
					$this->cache_delete_by_conditions($conditions);
				}
			}

			$format			= $this->process_field_formats($data);
			$where_format	= $this->process_field_formats($where);

			$result			= $wpdb->update($this->table, $data, $where, $format, $where_format);

			if($result === false){
				return new WP_Error('update_error', $wpdb->last_error);
			}else{
				return $result;
			}
		}
		// 如果为空，则需要事先通过各种 where 方法传递进去
		elseif($args_num == 1){	
			$data	= $args[0];

			$conditions		= []; 	
			$conditions[]	= $_condition	=$this->get_conditions(false);

			if(!empty($data[$this->primary_key])){
				$this->cache_delete_by_primary_key($data[$this->primary_key]);

				$this->where($this->primary_key, $data[$this->primary_key]);
				$conditions[] = $this->get_conditions(false);
			}

			if($this->primary_key != $this->cache_key){
				if(!empty($data[$this->cache_key])){
					$this->cache_delete($data[$this->cache_key]);

					$this->where($this->cache_key, $data[$this->cache_key]);
					$conditions[] = $this->get_conditions(false);
				}	
			}

			$this->cache_delete_by_conditions($conditions);

			$fields = $values = [];
			foreach ( $data as $field => $value ) {
				if ( is_null( $value ) ) {
					$fields[] = "`$field` = NULL";
					continue;
				}

				$fields[] = "`$field` = " . $this->process_field_formats($field);
				$values[] = $value;
			}

			$fields = implode( ', ', $fields );

			if($_condition){
				$sql = $wpdb->prepare("UPDATE `{$this->table}` SET {$fields} WHERE {$_condition}", $values);
			}else{
				$sql = $wpdb->prepare("UPDATE `{$this->table}` SET {$fields}", $values);
			}

			if(isset($_GET['debug'])){
				echo $sql;	
			}

			return $wpdb->query($sql);
			
			// return new WP_Error('update_error', 'WHERE 为空！');
		}		
	}

	/*
	用法：
	delete($where);
	delete($id);
	delete(); // $where 参数通过各种 where() 方法事先传递
	*/
	public function delete($where = ''){
		global $wpdb;

		$this->set_last_changed();

		if($where){
			// 如果传递进来字符串或者数字，认为根据主键删除
			if(!is_array($where)){
				$id		= $where; 
				$where	= array($this->primary_key=>$id);

				$this->cache_delete_by_primary_key($id);

				if($this->cache_key != $this->primary_key){
					$this->where($this->primary_key, $id);
					$this->cache_delete_by_conditions($this->get_conditions());
				}
			}
			// 传递数组，采用 wpdb 默认方式
			else{
				$this->where_all($where);
				$this->cache_delete_by_conditions($this->get_conditions());
			}

			$where_format	= $this->process_field_formats($where);
			$result			= $wpdb->delete($this->table, $where, $where_format);

			if($result === false){
				return new WP_Error('delele_error', $wpdb->last_error);
			}else{
				return $result;
			}
		}
		// 如果为空，则 $where 参数通过各种 where() 方法事先传递
		else{					
			if($conditions = $this->get_conditions()){
				$this->cache_delete_by_conditions($conditions);

				$sql = "DELETE FROM `{$this->table}` {$conditions}";

				if(isset($_GET['debug'])){
					echo $sql;	
				}

				$result = $wpdb->query($sql);

				if(false === $result ){
					return new WP_Error('delele_error', $wpdb->last_error);
				}else{
					return $result ;	
				}
			}else{
				return new WP_Error('delele_error', 'WHERE 为空！');
			}
		}
	}

	public function delete_multi($ids){
		global $wpdb;

		$this->set_last_changed();

		if(empty($ids)){
			return new WP_Error('empty_datas', '数据为空');
		}

		$this->cache_delete_multi_by_primary_key($ids);

		if($this->primary_key != $this->cache_key){
			$this->where_in($this->primary_key, $ids);
			$this->cache_delete_by_conditions($this->get_conditions());
		}

		$values = [];

		foreach ($ids as $id) {
			$values[] = $wpdb->prepare($this->process_field_formats($this->primary_key), $id);
		}

		$where = 'WHERE `' . $this->primary_key . '` IN ('.implode(',', $values).') ';

		$sql = "DELETE FROM `{$this->table}` {$where}";

		if(isset($_GET['debug'])){
			echo $sql;	
		}

		$result = $wpdb->query($sql);

		if(false === $result ){
			return new WP_Error('delele_error', $wpdb->last_error);
		}else{
			return $result ;	
		}
	}

	public function parse_list($list){
		if (!is_array($list)) {
			$list	= preg_split('/[\s,]+/', $list);
		}

		return array_values(array_unique($list));
		// return $list;
	}

	public function get_conditions($return='with_where'){
		global $wpdb;
		$where = [];

		if (!empty($this->search_term) && $this->searchable_fields) {
			$search_where = [];
			foreach ($this->searchable_fields as $field) {
				$like = '%' . $wpdb->esc_like( $this->search_term ) . '%';
				$search_where[]	= $wpdb->prepare( '`' . $field . '` LIKE  %s', $like );
			}

			$search_where = implode(' OR ', $search_where);

			$where[] = ' (' . $search_where . ')';
		}

		foreach ($this->where as $q) {
			if (isset($q['column'])) {
				if(strstr($q['column'], '(') !== false){
					$q_column	= ' '.$q['column'].' ';
				}else{
					$q_column	= ' `' . $q['column']. '` ';
				}	
			}

			// where
			if ($q['type'] == 'where') {
				$where[] = $wpdb->prepare($q_column . '= ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_not
			elseif ($q['type'] == 'not') {
				$where[] = $wpdb->prepare($q_column . '!= ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_like
			elseif ($q['type'] == 'like') {
				$where[] = $wpdb->prepare($q_column . 'LIKE ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_not_like
			elseif ($q['type'] == 'not_like') {
				$where[] = $wpdb->prepare($q_column . 'NOT LIKE ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_lt
			elseif ($q['type'] == 'lt') {
				$where[] = $wpdb->prepare($q_column . '< ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_lte
			elseif ($q['type'] == 'lte') {
				$where[] = $wpdb->prepare($q_column . '<= ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_gt
			elseif ($q['type'] == 'gt') {
				$where[] = $wpdb->prepare($q_column . '> ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_gte
			elseif ($q['type'] == 'gte') {
				$where[] = $wpdb->prepare($q_column . '>= ' . $this->process_field_formats($q['column']), $q['value']);
			}
			// where_in
			elseif ($q['type'] == 'in') {
				$values = [];				

				foreach (self::parse_list($q['value']) as $value) {
					$values[] = $wpdb->prepare($this->process_field_formats($q['column']), $value);
				}

				if(count($values) == 1){
					$where[] = $q_column . '= ' . $values[0];
				}else{
					$where[] = $q_column . 'IN ('.implode(',', $values).') ';
				}
			}
			// where_not_in
			elseif ($q['type'] == 'not_in') {
				$values = [];				

				foreach (self::parse_list($q['value']) as $value) {
					$values[] = $wpdb->prepare($this->process_field_formats($q['column']), $value);
				}

				if(count($values) == 1){
					$where[] = $q_column . '!= ' . $values[0];
				}else{
					$where[] = $q_column . 'NOT IN ('.implode(',', $values).') ';
				}
			}
			// where_any
			elseif ($q['type'] == 'any') {
				$wehre_any = [];
				foreach ($q['where'] as $column => $value) {
					$wehre_any[]	= $wpdb->prepare( '`' . $column . '` =  '.$this->process_field_formats($column), $value );
				}

				$wehre_any = implode(' OR ', $wehre_any);

				$where[] = ' ('. $wehre_any . ')';
			}
			// where_all
			elseif ($q['type'] == 'all') {
				$wehre_all = [];
				foreach ($q['where'] as $column => $value) {
					$wehre_all[]	= $wpdb->prepare( '`' . $column . '` =  '.$this->process_field_formats($column), $value );
				}

				$wehre_all = implode(' AND ', $wehre_all);

				$where[] = ' ('. $wehre_all . ')';
			}
			// where_fragment
			elseif ($q['type'] == 'fragment') {
				$where[] = ' ('. $q['fragment'] . ')';
			}
			// find_in_set
			elseif ($q['type'] == 'find_in_set') {
				$where[] = ' FIND_IN_SET ('. $q['item'] . ', '.$q['list'].')';
			}
		}

		// Finish where clause
		if (!empty($where)) {
			if($return == 'with_where'){	// 输出 where 关键字
				$conditions	= ' WHERE ' . implode(' AND ', $where);
			}elseif($return == ''){			// 不输出 where 关键字
				$conditions	= ' ' . implode(' AND ', $where);
			}elseif($return == 'array'){
				$conditions = $where;	// 直接输出 Where 数组
			}
		}else{
			$conditions	= '';
		}

		$this->clear();

		return $conditions;
	}

	public function get_wheres(){
		return $this->get_conditions(false);
	}

	private function process_field_formats($data){
		$format	= [];

		if(is_array($data)){
			foreach ($data as $field => $value) {
				$format[] = isset($this->field_types[$field])?$this->field_types[$field]:'%s';
			}
		}else{
			$format = isset($this->field_types[$data])?$this->field_types[$data]:'%s';
		}

		return $format;
	}

	public function clear(){
		$this->limit		= 0;
		$this->offset		= 0;
		$this->where		= [];
		$this->order_by		= $this->primary_key;
		$this->group_by		= '';
		$this->having		= '';
		$this->order		= 'DESC';
		$this->search_term	= null;
	}

	public function limit($limit){
		$this->limit = (int) $limit;
		return $this;
	}

	public function offset($offset){
		$this->offset = (int) $offset;		
		return $this;
	}

	public function order_by($order_by=''){
		if($order_by !== null){
			$this->order_by = $order_by;
		}
		return $this;
	}

	public function group_by($group_by=''){
		if($group_by){
			$this->group_by = $group_by;
		}
		return $this;
	}

	public function having($having=''){
		if($having){
			$this->having = $having;
		}
		return $this;
	}
	
	public function order($order='DESC'){
		$this->order = (strtoupper($order) == 'ASC')?'ASC':'DESC';
		return $this;
	}
	
	public function where($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'where', 'column' => $column, 'value' => $value);	
		}
		return $this;
	}

	public function where_not($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'not', 'column' => $column, 'value' => $value);
		}
		return $this;
	}
	
	public function where_like($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'like', 'column' => $column, 'value' => $value);
		}
		return $this;
	}

	public function where_not_like($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'not_like', 'column' => $column, 'value' => $value);
		}
		return $this;
	}

	public function where_lt($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'lt', 'column' => $column, 'value' => $value);
		}
		return $this;
	}

	public function where_lte($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'lte', 'column' => $column, 'value' => $value);
		}
		return $this;
	}

	public function where_gt($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'gt', 'column' => $column, 'value' => $value);
		}
		return $this;
	}

	public function where_gte($column, $value){
		if($value !== null){
			$this->where[] = array('type' => 'gte', 'column' => $column, 'value' => $value);
		}
		return $this;
	}

	public function where_in($column, $in){
		if($in !== null){
			if($in){
				$this->where[] = array('type' => 'in', 'column' => $column, 'value' => $in);
			}else{
				$this->where($column, '');
			}
		}
		return $this;
	}

	public function where_not_in($column, $not_in){
		if($not_in !== null){
			if($not_in){
				$this->where[] = array('type' => 'not_in', 'column' => $column, 'value' => $not_in);	
			}else{
				$this->where_not($column, '');
			}
		}
		return $this;
	}

	public function where_any(array $where){
		if($where){
			$this->where[] = array('type' => 'any', 'where' => $where);
		}
		return $this;
	}

	public function where_all(array $where){
		if($where){
			$this->where[] = array('type' => 'all', 'where' => $where);	
		}
		return $this;
	}

	public function where_fragment($where){
		if($where){
			$this->where[] = array('type' => 'fragment', 'fragment' => $where);
		}
		return $this;
	}

	public function find_in_set($item, $list){
		$this->where[] = array('type' => 'find_in_set', 'item' => $item, 'list' => $list);	
		return $this;
	}
	
	public function search($search_term=''){
		if($search_term){
			$this->search_term = $search_term;
		}
		return $this;
	}

	// 后台 list table 显示
	public function query_items($limit, $offset){ 
		$this->limit($limit); 
		$this->offset($offset);
		
		if(isset($_REQUEST['orderby']) && $this->order_by == $this->primary_key){	// 没设置过，才设置
			$this->order_by($_REQUEST['orderby']);
		}

		if(isset($_REQUEST['order'])){
			$this->order($_REQUEST['order']);
		}

		if($this->searchable_fields && is_null($this->search_term)){
			$search_term	= $_REQUEST['s'] ?? null;
			$this->search($search_term);
		}

		if($this->filterable_fields){
			foreach ($this->filterable_fields as $field_key) {
				if(isset($_REQUEST[$field_key])){
					$this->where($field_key, $_REQUEST[$field_key]);
				}
			}
		}
		
		$items	= $this->find();
		$total 	= $this->find_total($this->group_by);

		return compact('items', 'total');
	}

	public function item_callback($item){
		return $item;
	}

	public function views(){
	}
}

class WPJAM_DBTransaction{
	public static function wpdb()
	{
		global $wpdb;
		return $wpdb;
	}

	public static function beginTransaction()
	{
		return self::wpdb()->query("START TRANSACTION;");
	}

	public static function queryException()
	{
		$error = self::wpdb()->last_error;
		if (!empty($error)) {
			throw new Exception($error);
		}
	}

	public static function commit()
	{
		self::queryException();
		return self::wpdb()->query("COMMIT;");
	}

	public static function rollBack()
	{
		return self::wpdb()->query("ROLLBACK;");
	}
}

abstract class WPJAM_Item{
	private $primary_key	= 'id';
	private $primary_title	= 'ID';
	private $unique_key		= '';
	private $unique_title	= '';
	private $total			= 0;

	public function __construct($args=[]){
		$args = wp_parse_args($args, [
			'primary_key'	=> 'id',
			'primary_title'	=> 'ID',
			'unique_key'	=> '',
			'unique_title'	=> '',
			'total'			=> 0,
		]);

		$this->primary_key		= $args['primary_key'];
		$this->primary_title	= $args['primary_title'];
		$this->unique_key		= $args['unique_key'];
		$this->unique_title		= $args['unique_title'];
		$this->total			= $args['total'];
	}

	public function get_primary_key(){
		return $this->primary_key;
	}

	public function get_results(){
		return $this->get_items();
	}

	public function get_items(){
	}

	public function update_items($items){
	}

	public function parse_items($items){
		if(empty($items)){
			return [];
		}
		
		foreach ($items as $id => &$item) {
			$item[$this->primary_key]	= $id;
		}

		return $items;
	}

	public function exists($value){
		$items	= $this->get_items();

		if(empty($items)){
			return false;
		}else{
			return in_array($value, array_column($items, $this->unique_key));
		}
	}

	public function get($id){
		$items	= $this->get_items();

		return $items[$id] ?? false;
	}

	public function insert($data){
		$items			= $this->get_items();

		if($this->total && count($items) >= $this->total){
			return new WP_Error('over_total', '最多支持'.$this->total.'个');
		}

		if(in_array($this->primary_key, ['option_key', 'id'])){
			if($this->unique_key){

				if(empty($data[$this->unique_key])){
					return new WP_Error('empty_'.$this->unique_key, $this->unique_title.'不能为空');
				}

				if($this->exists($data[$this->unique_key])){
					return new WP_Error('duplicate_'.$this->unique_key, $this->unique_title.'重复');
				}
			}

			if($items){
				$ids	= array_keys($items);

				$ids	= array_map(function($id){
					return intval(str_replace('option_key_', '', $id));
				}, $ids);
				
				$id		= max($ids);
				$id		= $id+1;
			}else{
				$id		= 1;
			}

			if($this->primary_key == 'option_key'){
				$id		= 'option_key_'.$id;
			}
		}else{
			if(empty($data[$this->primary_key])){
				return new WP_Error('empty_'.$this->primary_key, $this->primary_title.'不能为空');
			}

			$id	= $data[$this->primary_key];

			if(isset($items[$id])){
				return new WP_Error('duplicate_'.$this->primary_key, $this->primary_title.'值重复');
			}
		}

		$items[$id]	= $data;

		$this->update_items($items);

		return $id;
	}
	
	public function update($id, $data){
		$items			= $this->get_items();
		
		if(!isset($items[$id])){
			return new WP_Error('invalid_'.$this->primary_key, $this->primary_title.'为「'.$id.'」的数据的不存在');
		}

		if(in_array($this->primary_key, ['option_key', 'id'])){

			if($this->unique_key && isset($data[$this->unique_key])){
				if(empty($data[$this->unique_key])){
					return new WP_Error('empty_'.$this->unique_key, $this->unique_title.'不能为空');
				}

				if($data[$this->unique_key] != $items[$id][$this->unique_key]){
					if($this->exists($data[$this->unique_key])){
						return new WP_Error('duplicate_'.$this->unique_key, $this->unique_title.'重复');
					}
				}
			}
		}

		$data[$this->primary_key] = $id;

		$items[$id]	= wp_parse_args($data, $items[$id]);

		return $this->update_items($items);	
	}


	public function delete($id){
		$items	= $this->get_items();

		if(!isset($items[$id])){
			return new WP_Error('invalid_'.$this->primary_key, $this->primary_title.'为「'.$id.'」的数据的不存在');
		}

		unset($items[$id]);

		return $this->update_items($items);
	}

	public function move($id, $data){
		$items	= $this->get_items();

		if(empty($items) || empty($items[$id])){
			return new WP_Error('key_not_exists', $id.'的值不存在');
		}

		$next	= $data['next'] ?? false;
		$prev	= $data['prev'] ?? false;

		if(!$next && !$prev){
			return new WP_Error('invalid_move', '无效移动位置');
		}

		$item	= $items[$id];
		unset($items[$id]);

		if($next){
			if(empty($items[$next])){
				return new WP_Error('key_not_exists', $next.'的值不存在');
			}

			$offset	= array_search($next, array_keys($items));

			if($offset){
				$items	= array_slice($items, 0, $offset, true) +  [$id => $item] + array_slice($items, $offset, null, true);	
			}else{
				$items	= [$id => $item] + $items;	
			}
		}else{
			if(empty($items[$prev])){
				return new WP_Error('key_not_exists', $prev.'的值不存在');
			}

			$offset	= array_search($prev, array_keys($items));
			$offset ++;

			if($offset){
				$items	= array_slice($items, 0, $offset, true) +  [$id => $item] + array_slice($items, $offset, null, true);	
			}else{
				$items	= [$id => $item] + $items;	
			}
		}

		return $this->update_items($items);
	}

	public function get_searchable_fields(){
		return false;
	}

	public function get_filterable_fields(){
		return false;
	}

	// 后台 list table 显示
	public function query_items($limit, $offset){
		$items	= $this->get_items();
		$total 	= count($items);

		return compact('items', 'total');
	}

	public function item_callback($item){
		return $item;
	}

	public function views(){
	}
}

class WPJAM_Option extends WPJAM_Item{
	private $option_name;
	private $primary_key;

	public function __construct($option_name, $args=[]){
		$this->option_name	= $option_name;
		
		if(!is_array($args)){
			$args	= ['primary_key' => $args];
		}else{
			$args	= wp_parse_args($args, ['primary_key'=>'option_key']);
		}

		parent::__construct($args);
	}

	public function get_items(){
		$items	= get_option($this->option_name);

		return $this->parse_items($items);
	}

	public function update_items($items){
		if($items && in_array($this->get_primary_key(), ['option_key','id'])){
			foreach ($items as &$item){
				unset($item[$this->get_primary_key()]);
			}
		}
		return update_option($this->option_name, $items);	
	}
}

class WPJAM_PostOption extends WPJAM_Item{
	private $meta_key;

	public function __construct($meta_key, $args=[]){
		$this->meta_key	= $meta_key;

		if(!is_array($args)){
			$args	= ['primary_key'=>$args];
		}else{
			$args	= wp_parse_args($args, ['primary_key'=>'option_key']);
		}

		parent::__construct($args);
	}

	public function get_post_id(){
		return wpjam_get_data_parameter('post_id');
	}

	public function get_items(){
		$post_id	= $this->get_post_id();
		$meta_key	= $this->meta_key;

		$items		= get_post_meta($post_id, $meta_key, true);

		return $this->parse_items($items);
	}

	public function update_items($items){
		if($items && in_array($this->get_primary_key(), ['option_key','id'])){
			foreach ($items as &$item){
				unset($item[$this->get_primary_key()]);
				unset($item['post_id']);
			}
		}

		$post_id	= $this->get_post_id();
		$meta_key	= $this->meta_key;

		return update_post_meta($post_id, $meta_key, $items);	
	}
}

class WPJAM_PostContent extends WPJAM_Item{
	public function __construct($args=[]){
		$args	= wp_parse_args($args, ['primary_key'=>'id']);
		parent::__construct($args);
	}

	public function get_post_id(){
		return wpjam_get_data_parameter('post_id');
	}

	public function get_items(){
		$post_id	= $this->get_post_id();
		$items		= [];
		$_post		= get_post($post_id);

		if($_post && $_post->post_content){
			$items	= maybe_unserialize($_post->post_content);
		}

		return $this->parse_items($items);
	}

	public function update_items($items){
		if($items){
			foreach ($items as &$item){
				unset($item[$this->get_primary_key()]);
				unset($item['post_id']);
			}
		}

		$post_id		= $this->get_post_id();
		$post_content	= maybe_serialize($items);
		return WPJAM_Post::update($post_id, compact('post_content'));
	}
}