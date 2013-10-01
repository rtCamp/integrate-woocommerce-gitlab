<?php
/*
  Plugin Name: rtwoo-gitlab
  Plugin URI: http://rtcamp.com
  Description: Gitlab Binding with Woocommerce
  Version: 1.0.5
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

if ( !defined( 'RT_WOO_GL_VERSION' ) ) {
	define( 'RT_WOO_GL_VERSION', '1.0.0' );
}
if ( !defined( 'RT_WOO_GL_PATH' ) ) {
	define( 'RT_WOO_GL_PATH', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'RT_WOO_GL_URL' ) ) {
	define( 'RT_WOO_GL_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'RT_WOO_GL_PATH_APP' ) ) {
	define( 'RT_WOO_GL_PATH_APP', plugin_dir_path( __FILE__ ) . 'app/' );
}
if ( !defined( 'RT_WOO_GL_PATH_ADMIN' ) ) {
	define( 'RT_WOO_GL_PATH_ADMIN', plugin_dir_path( __FILE__ ) . 'admin/' );
}
if ( !defined( 'RT_WOO_GL_PATH_LIB' ) ) {
	define( 'RT_WOO_GL_PATH_LIB', plugin_dir_path( __FILE__ ) . 'lib/' );
}
if ( !defined( 'RT_WOO_GL_PATH_HELPER' ) ) {
	define( 'RT_WOO_GL_PATH_HELPER', plugin_dir_path( __FILE__ ) . 'helper/' );
}
if ( !defined( 'RT_WOO_GL_PATH_TEMPLATES' ) ) {
	define( 'RT_WOO_GL_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'templates/' );
}


function rtwoo_gitlab_include_class_file( $dir ) {
	if ( $dh = opendir( $dir ) ) {
		while ( $file = readdir( $dh ) ) {
			//Loop
			if ( $file !== '.' && $file !== '..' && $file[0] !== '.' ) {
				if ( is_dir( $dir . $file ) ) {
					rtwoo_gitlab_include_class_file( $dir . $file . '/' );
				} else {
					include_once $dir . $file;
				}
			}
		}
		closedir( $dh );
		return 0;
	}
}

function rtwoo_gitlab_include() {
	$rtWooGLIncludePaths = array(
		RT_WOO_GL_PATH_HELPER,
		RT_WOO_GL_PATH_LIB,
		RT_WOO_GL_PATH_ADMIN,
		RT_WOO_GL_PATH_APP,
	);
	foreach ( $rtWooGLIncludePaths as $path ) {
		rtwoo_gitlab_include_class_file( $path );
	}
}

function rtwoo_gitlab_woo_check() {
	if ( !function_exists( 'woothemes_queue_update' ) ) {
		require_once( 'woo-includes/woo-functions.php' );
	}

	if ( is_woocommerce_active() ) {
		return true;
	} else {
		return false;
	}
}

function rtwoo_gitlab_init() {

	rtwoo_gitlab_include();

	$flag = rtwoo_gitlab_woo_check();

	if ( $flag ) {
		global $rtWooGitlab;
		$rtWooGitlab = new RtWooGitlab();
	}
}
add_action( 'woocommerce_init', 'rtwoo_gitlab_init' );
