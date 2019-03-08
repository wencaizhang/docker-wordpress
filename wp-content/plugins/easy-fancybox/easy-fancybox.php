<?php
/*
Plugin Name: Easy FancyBox
Plugin URI: http://status301.net/wordpress-plugins/easy-fancybox/
Description: Easily enable the <a href="http://fancybox.net/">FancyBox jQuery extension</a> on all image, SWF, PDF, YouTube, Dailymotion and Vimeo links. Also supports iFrame and inline content.
Text Domain: easy-fancybox
Domain Path: languages
Version: 1.8.13
Author: RavanH
Author URI: http://status301.net/
*/

/*  Copyright 2018  RavanH  (email : ravanhagen@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
    For Installation instructions, usage, revision history and other info: see readme.txt included in this package
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/**************
 * CONSTANTS
 **************/

define( 'EASY_FANCYBOX_VERSION', '1.8.13' );
define( 'FANCYBOX_VERSION', '1.3.23' );
define( 'MOUSEWHEEL_VERSION', '3.1.13' );
define( 'EASING_VERSION', '1.4.1' );
define( 'METADATA_VERSION', '2.22.1' );

/**************
 *   CLASSES
 **************/

require_once dirname( __FILE__ ) . '/inc/class-easyfancybox.php';
new easyFancyBox( __FILE__ );

if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/inc/class-easyfancybox-admin.php';
    new easyFancyBox_Admin();
}
