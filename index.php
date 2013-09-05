<?php

/*
  Plugin Name: rtwoo-gitlab
  Plugin URI: http://rtcamp.com
  Description: Reseller Module in Woocommerce
  Version: 1.0
  Author: rtCamp
  Text Domain: rtwoo-gitlab
  Author URI: http://rtcamp.com
 */

/**
 * Index file, contains the plugin metadata and activation processes
 *
 * @package rtwoo-gitlab
 * @subpackage index.php
 */

if(!defined('RT_WOO_GL_VERSION')) {
	define('RT_WOO_GL_VERSION', '1.0.0');
}
if(!defined('RT_WOO_GL_PATH')) {
	define('RT_WOO_GL_PATH', plugin_dir_path(__FILE__));
}
if(!defined('RT_WOO_GL_URL')) {
	define('RT_WOO_GL_URL', plugin_dir_url(__FILE__));
}
if(!defined('RT_WOO_GL_PATH_APP')) {
	define('RT_WOO_GL_PATH_APP', plugin_dir_path(__FILE__) . 'app/');
}
if(!defined('RT_WOO_GL_PATH_ADMIN')) {
	define('RT_WOO_GL_PATH_ADMIN', plugin_dir_path(__FILE__) . 'admin/');
}
if(!defined('RT_WOO_GL_PATH_LIB')) {
	define('RT_WOO_GL_PATH_LIB', plugin_dir_path(__FILE__) . 'lib/');
}


function rtwoo_gitlab_include_class_file($dir) {
	if ($dh = opendir($dir)) {
		while ($file = readdir($dh)) {
			if ($file != "." && $file != ".." && $file[0] != '.') {
				if (is_dir($dir . $file)) {
					rtwoo_gitlab_include_class_file($dir . $file . '/');
				} else {
//					var_dump($dir . $file);
					include_once $dir . $file;
				}
			}
		}
		closedir($dh);
		return 0;
	}
}

function rtwoo_gitlab_init() {

	$rtWooGLIncludePaths = array(
		RT_WOO_GL_PATH_LIB,
		RT_WOO_GL_PATH_ADMIN,
		RT_WOO_GL_PATH_APP
	);
	foreach ($rtWooGLIncludePaths as $path) {
		rtwoo_gitlab_include_class_file($path);
	}

	/**
	 * Required functions
	 */
	if (!function_exists('woothemes_queue_update'))
	    require_once( 'woo-includes/woo-functions.php' );

	if (is_woocommerce_active()) {
		global $rtWooGitlab;
		$rtWooGitlab = new rtWooGitlab();
	}
}
add_action('woocommerce_init', 'rtwoo_gitlab_init');
?>