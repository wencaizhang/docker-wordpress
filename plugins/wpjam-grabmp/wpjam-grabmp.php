<?php
/*
Plugin Name: WPJAM GrabMP
Plugin URI: http://blog.wpjam.com/project/wpjam-grabmp/
Description: 一键抓取公众号文章到 WordPress 博客，并且突破微信图片防盗链
Version: 2.0
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if(!defined('WPJAM_GRABMP_PLUGIN_DIR')){
	define('WPJAM_GRABMP_PLUGIN_DIR',	plugin_dir_path(__FILE__));
	
	include WPJAM_GRABMP_PLUGIN_DIR.'admin/admin.php';
}