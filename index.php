<?php
/*
  Plugin Name: WooCommerce GitLab Add-on
  Plugin URI: https://rtcamp.com/store/woocommerce-gitlab
  Description: WooCommerce GitLab Add-on provides a simple way to connect GitLab to WooCommerce.
  Version: 2.0.0
  Author: rtCamp
  Author URI: https://rtcamp.com
  Text Domain: rtwoo-gitlab
 */

/**
 * Index file, contains the plugin metadata and activation processes
 *
 * @package rtwoo-gitlab
 * @subpackage index.php
 */

if ( !defined( 'RT_WOO_GL_VERSION' ) ) {
	define( 'RT_WOO_GL_VERSION', '2.0.0' );
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

function rtwoogl_admin_notices() { ?>
    <div class="updated">
        <p><?php _e( '<b>WooCommerce GitLab</b> : WooCommerce is either not installed or deactivated. Please make sure that it is installed & activated.' ); ?></p>
    </div>
<?php }

function rtwoo_gitlab_init() {

	rtwoo_gitlab_include();

	global $rtWooGitlab;
	$rtWooGitlab = new RtWooGitlab();

}

add_filter('plugin_row_meta',  'rtwoogl_plugin_links', 10, 2);
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'rtwoogl_plugin_link_actions' );
function rtwoogl_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href=" https://rtcamp.com/store/woocommerce-gitlab#tab-faq">' . __('FAQs') . '</a>';
		$links[] = '<a href="https://rtcamp.com">' . __('Support') . '</a>';
	}
	return $links;
}

function rtwoogl_plugin_link_actions( $links ) {
    return array_merge( array( 'settings' => '<a href="'. admin_url('admin.php?page=woocommerce_settings&tab=rtwoogl') .'">' . __( 'Settings' ) . '</a>' ), $links );
}

$flag = rtwoo_gitlab_woo_check();
if ( $flag ) {
	add_action( 'woocommerce_init', 'rtwoo_gitlab_init' );
} else {
	add_action( 'admin_notices', 'rtwoogl_admin_notices' );
}

