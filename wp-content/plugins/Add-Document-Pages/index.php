<?php
/*
Plugin Name: Add Document Pages（添加文档页）
Plugin URI: http://www.xintheme.com/xin-plugins/27706.html
Description: 为你的WordPress网站增加一个文档页面。如：技术文档，帮助中心等。
Version: 1.0
Author: XINTHEME
Author URI: http://www.xintheme.com
*/
class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		} 

		return self::$instance;

	} 

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();

		// Add a filter to the wp 4.7 version attributes metabox
		add_filter(
			'theme_page_templates', array( $this, 'add_new_template' )
		);

		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);

		// Add your templates to this array.
		$this->templates = array(
			'documentation.php' => '文档模板',
		);
			
	} 

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}


	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		
		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 

		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

} 
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

add_action('init', 'xintheme_page_excerpt');
function xintheme_page_excerpt() {
    add_post_type_support('page', array('excerpt'));
}

register_nav_menus(['documentation-nav'	=> '文档页-主菜单']);

function plugins_document_css(){
	$bootstrap	= plugins_url('/assets/css/bootstrap.css', __FILE__ );
	$style		= plugins_url('/assets/css/style.css', __FILE__ );
	echo'<link href="'.$bootstrap.'" rel="stylesheet">
<link href="'.$style.'" rel="stylesheet">
';
}

function plugins_document_js(){
	$jquery		= plugins_url('/assets/js/jquery.js', __FILE__ );
	$bootstrap	= plugins_url('/assets/js/bootstrap.js', __FILE__ );
	$holde		= plugins_url('/assets/js/holde.js', __FILE__ );
	$application= plugins_url('/assets/js/application.js', __FILE__ );
	echo'<script src="'.$jquery.'"></script>
<script src="'.$bootstrap.'"></script>
<script src="'.$holde.'"></script>
<script src="'.$application.'"></script>
';
}