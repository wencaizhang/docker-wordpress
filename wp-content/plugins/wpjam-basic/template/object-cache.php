<?php
if(isset($_GET['debug']) && $_GET['debug'] == 'sql'){
	require_once ( ABSPATH . WPINC . '/cache.php' );
	return;
}

if ( !defined( 'WP_CACHE_KEY_SALT' ) )
	define( 'WP_CACHE_KEY_SALT', '' );

if ( class_exists( 'Memcached' ) ) {

	function wp_cache_add( $key, $data, $group = '', $expire = 0 ) {
		global $wp_object_cache;
		return $wp_object_cache->add( $key, $data, $group, $expire );
	}

	function wp_cache_incr( $key, $n = 1, $group = '' ) {
		global $wp_object_cache;
		return $wp_object_cache->incr( $key, $n, $group );
	}

	function wp_cache_decr( $key, $n = 1, $group = '' ) {
		global $wp_object_cache;
		return $wp_object_cache->decr( $key, $n, $group );
	}

	function wp_cache_close() {
		global $wp_object_cache;
		return $wp_object_cache->close();
	}

	function wp_cache_delete( $key, $group = '' ) {
		global $wp_object_cache;
		return $wp_object_cache->delete( $key, $group );
	}

	function wp_cache_delete_multi( $keys, $group = '' ) {
		global $wp_object_cache;
		return $wp_object_cache->delete_multi( $keys, $group );
	}

	function wp_cache_flush() {
		global $wp_object_cache;
		return $wp_object_cache->flush();
	}

	function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
		global $wp_object_cache;
		return $wp_object_cache->get( $key, $group, $force, $found );
	}

	function wp_cache_get_with_cas( $key, $group = '', &$cas_token = null ) {
		global $wp_object_cache;
		return $wp_object_cache->get_with_cas( $key, $group, $cas_token );
	}

	function wp_cache_cas( $cas_token, $key, $data, $group = '', $expire = 0  ) {
		global $wp_object_cache;
		return $wp_object_cache->cas( $cas_token, $key, $data, $group, $expire );
	}
	
	function wp_cache_get_multi( $keys, $group = '' ) {
		global $wp_object_cache;
		return $wp_object_cache->get_multi( $keys, $group );
	}

	
	function wp_cache_set_multi( $keys, $datas, $group= '', $expire = 0 ) {
		global $wp_object_cache;
		return $wp_object_cache->set_multi( $keys, $datas,  $group, $expire);
	}

	function wp_cache_init() {
		global $wp_object_cache;
		$wp_object_cache = new WP_Object_Cache();
	}

	function wp_cache_replace( $key, $data, $group = '', $expire = 0 ) {
		global $wp_object_cache;
		return $wp_object_cache->replace( $key, $data, $group, $expire );
	}

	function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
		global $wp_object_cache;

		if ( defined( 'WP_INSTALLING' ) == false ) {
			return $wp_object_cache->set( $key, $data, $group, $expire );
		} else {
			return $wp_object_cache->delete( $key, $group );
		}
	}

	function wp_cache_switch_to_blog( $blog_id ) {
		global $wp_object_cache;

		return $wp_object_cache->switch_to_blog( $blog_id );
	}

	function wp_cache_add_global_groups( $groups ) {
		global $wp_object_cache;
		$wp_object_cache->add_global_groups( $groups );
	}

	function wp_cache_add_non_persistent_groups( $groups ) {
		global $wp_object_cache;
		$wp_object_cache->add_non_persistent_groups( $groups );
	}

	function wordpress_memcached_get_stats() {
		global $wp_object_cache;
		return $wp_object_cache->stats();
	}

	class WP_Object_Cache {
		private $global_groups	= array();
		private $no_mc_groups	= array();
		private $cache			= array();
		private $mc				= null;

		function add( $id, $data, $group = 'default', $expire = 0 ) {
			$key = $this->key( $id, $group );

			if ( is_object( $data ) ) {
				$data	= clone $data;
			}

			if ( in_array( $group, $this->no_mc_groups ) ) {
				$this->cache[ $key ] = $data;
				return true;
			} elseif ( isset( $this->cache[ $key ] ) && $this->cache[ $key ] !== false ) {
				return false;
			}

			$result	= $this->mc->add( $key, $data, $expire );

			if ( false !== $result ) {
				$this->cache[ $key ]	= $data;
			}

			return $result;
		}

		function add_global_groups( $groups ) {
			if ( ! is_array( $groups ) ) {
				$groups = (array) $groups;
			}

			$this->global_groups	= array_merge( $this->global_groups, $groups );
			$this->global_groups	= array_unique( $this->global_groups );
		}

		function add_non_persistent_groups( $groups ) {
			if ( ! is_array( $groups ) ) {
				$groups = (array) $groups;
			}

			$this->no_mc_groups		= array_merge( $this->no_mc_groups, $groups );
			$this->no_mc_groups		= array_unique( $this->no_mc_groups );
		}

		function incr( $id, $n = 1, $group = 'default' ) {
			$key	= $this->key( $id, $group );

			return $this->mc->increment( $key, $n );
		}

		function decr( $id, $n = 1, $group = 'default' ) {
			$key	= $this->key( $id, $group );

			return $this->mc->decrement( $key, $n );
		}

		function close() {
			$this->mc->quit();
		}

		function delete( $id, $group = 'default' ) {

			$key = $this->key( $id, $group );

			unset( $this->cache[ $key ] );

			if ( in_array( $group, $this->no_mc_groups ) ) {
				return true;
			}

			return $this->mc->delete( $key );
		}

		function delete_multi($ids, $group = 'default'){
			$keys =	array_map(function($id) use ($group){ return $this->key( $id, $group ); }, $ids);

			if ( in_array( $group, $this->no_mc_groups ) ) {
				return true;
			}

			foreach ($keys as $key) {
				unset( $this->cache[ $key ] );
			}

			$result	= $this->mc->deleteMulti( $keys );

			return $result;
		}

		function flush() {
			return $this->mc->flush();
		}

		function get( $id, $group = 'default', $force = false, &$found = null ) {
			$key	= $this->key( $id, $group );

			if ( isset( $this->cache[ $key ] ) && ( ! $force || in_array( $group, $this->no_mc_groups ) ) ) {
				if ( is_object( $this->cache[ $key ] ) ) {
					$value	= clone $this->cache[ $key ];
				} else {
					$value	= $this->cache[ $key ];
				}
				$found	= true;
			} else if ( in_array( $group, $this->no_mc_groups ) ) {
				$value	= false;
				$found	= true;
			} else {
				$value	= $this->mc->get( $key );
				$found	= true;
				if ($this->mc->getResultCode() == Memcached::RES_NOTFOUND) {
					$value = false;
					$found = false;
				}

				$this->cache[ $key ] = $value;
			}

			return $value;
		}

		function get_with_cas( $id, $group = 'default', &$cas_token=null){
			$key	= $this->key( $id, $group );
			
			if(defined('Memcached::GET_EXTENDED')) {
				$result	= $this->mc->get($key, null, Memcached::GET_EXTENDED);

				if ($this->mc->getResultCode() == Memcached::RES_NOTFOUND) {
					$value	= false;
				}else{
					$value		= $result['value'];
					$cas_token 	= $result['cas'];
				}
			}else{
				$value	= $this->mc->get($key, null, $cas_token);

				if ($this->mc->getResultCode() == Memcached::RES_NOTFOUND) {
					$value	= false;
				}
			}

			return $value;
		}

		function cas( $cas_token, $id, $data, $group = 'default', $expire = 0 ) {
			$key = $this->key( $id, $group );

			if ( is_object( $data ) ) {
				$data = clone $data;
			}

			$this->cache[ $key ] = $data;

			return $this->mc->cas( $cas_token, $key, $data, $expire );
		}

		function get_multi( $ids, $group = 'default' ) {
			if ( in_array( $group, $this->no_mc_groups )){
				return false;
			}

			$keys =	array_map(function($id) use ($group){
				return $this->key( $id, $group );
			}, $ids);

			$key_ids = array_combine($keys, $ids);

			// $version = intval($this->mc->getVersion());
			// if ($version < 3) {
			// 	$results = $this->mc->getMulti( $keys, $cas_tokens, Memcached::GET_PRESERVE_ORDER );
			// } else {
			// 	$results = $this->mc->getMulti( $keys, Memcached::GET_PRESERVE_ORDER);
			// }

			$results	= $this->mc->getMulti($keys);

			if($results){
				$returns	= array();
				foreach ($key_ids as $key=>$id) {
					if(isset($results[$key])){
						$this->cache[ $key ] = $results[$key];
						$returns[$id]	= $results[$key];
					}
				}

				return $returns;
			}

			return $results;
		}

		function key( $id, $group ) {
			if ( empty( $group ) ) {
				$group = 'default';
			}

			if ( false !== array_search( $group, $this->global_groups ) ) {
				$prefix = $this->global_prefix;
			} else {
				$prefix = $this->blog_prefix;
			}

			return preg_replace( '/\s+/', '', WP_CACHE_KEY_SALT . "$prefix$group:$id" );
		}

		function replace( $id, $data, $group = 'default', $expire = 0 ) {
			$key	= $this->key( $id, $group );

			if ( is_object( $data ) ) {
				$data = clone $data;
			}

			$result = $this->mc->replace( $key, $data, $expire );

			if ( false !== $result ) {
				$this->cache[ $key ] = $data;
			}

			return $result;
		}

		function set( $id, $data, $group = 'default', $expire = 0 ) {
			$key = $this->key( $id, $group );

			if ( is_object( $data ) ) {
				$data = clone $data;
			}

			$this->cache[ $key ] = $data;

			if ( in_array( $group, $this->no_mc_groups ) ) {
				return true;
			}

			return $this->mc->set( $key, $data, $expire );
		}

		function set_multi( $ids, $datas, $group = 'default', $expire = 0 ) {
			
			if ( in_array( $group, $this->no_mc_groups ) ) {
				return true;
			}

			$keys =	array_map(function($id) use ($group){
				return $this->key( $id, $group );
			}, $ids);

			foreach ($keys as $i=>$key) {
				$this->cache[ $key ] = $datas[$i];
			}

			$items	= array_combine($keys, $datas);

			$result = $this->mc->setMulti( $items, $expire );

			return $result;
		}

		function switch_to_blog( $blog_id ) {
			global $table_prefix;
			$blog_id = (int) $blog_id;
			$this->blog_prefix = ( is_multisite() ? $blog_id : $table_prefix ) . ':';
		}

		function colorize_debug_line( $line ) {
			$colors = array(
				'get'	=> 'green',
				'set'	=> 'purple',
				'add'	=> 'blue',
				'delete' => 'red'
			);

			$cmd = substr( $line, 0, strpos( $line, ' ' ) );

			$cmd2 = "<span style='color:{$colors[$cmd]}'>$cmd</span>";

			return $cmd2 . substr( $line, strlen( $cmd ) ) . "\n";
		}

		function stats() {
			$stats_text = '';
			
			$stats = $this->mc->getStats();
			foreach ( $stats as $key => $details ) {
				$stats_text .= 'memcached: ' . $key . "\n\r";
				foreach ( $details as $name => $value ) {
					$stats_text .= $name . ': ' . $value . "\n\r";
				}
				$stats_text .= "\n\r";
			}

			return $stats_text;
		}

		function get_mc(){
			return $this->mc;
		}

		

		function failure_callback( $host, $port ) {
		}

		function __construct() {

			$this->mc = new Memcached();
			$this->mc ->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

			if(!$this->mc->getServerList()){
				$this->mc->addServer('127.0.0.1', 11211, 100);
			}

			// global $memcached_servers;

			// if ( isset( $memcached_servers ) ) {
			// 	$buckets = $memcached_servers;
			// } else {
			// 	$buckets = array( '127.0.0.1' );
			// }

			// reset( $buckets );
			// if ( is_int( key( $buckets ) ) ) {
			// 	$buckets = array( 'default' => $buckets );
			// }

			// foreach ( $buckets as $bucket => $servers ) {
			// 	$this->mc[ $bucket ] = new Memcached($bucket);
			// 	$this->mc[ $bucket ] ->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

			// 	if(count($this->mc[ $bucket ]->getServerList())){
			// 		continue;
			// 	}

			// 	$instances = array();
			// 	foreach ( $servers as $server ) {
			// 		@list( $node, $port ) = explode( ':', $server.":" );
			// 		if ( empty( $port ) ) {
			// 			$port = ini_get( 'memcache.default_port' );
			// 		}
			// 		$port = intval( $port );
			// 		if ( ! $port ) {
			// 			$port = 11211;
			// 		}

			// 		$instances[] = array( $node, $port, 1 );
			// 	}
			// 	$this->mc[ $bucket ]->addServers( $instances );
			// }

			global $blog_id, $table_prefix;

			$this->global_prefix = '';
			$this->blog_prefix   = '';

			if ( function_exists( 'is_multisite' ) ) {
				$this->global_prefix = ( is_multisite() || defined( 'CUSTOM_USER_TABLE' ) && defined( 'CUSTOM_USER_META_TABLE' ) ) ? '' : $table_prefix;
				$this->blog_prefix   = ( is_multisite() ? $blog_id : $table_prefix ) . ':';
			}

			// $this->cache_hits   =& $this->stats['get'];
			// $this->cache_misses =& $this->stats['add'];
		}


	}
} else {

	// No Memcached

	if ( function_exists( 'wp_using_ext_object_cache' ) ) {
		// In 3.7+, we can handle this smoothly
		wp_using_ext_object_cache( false );
	} else {
		// In earlier versions, there isn't a clean bail-out method.
		wp_die( 'Memcached class not available.' );
	}
}
