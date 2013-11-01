<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * rtWooGLAdmin
 *
 * @author udit
 */
if ( !class_exists( 'RtWooGlAdmin' ) ) {
	class RtWooGlAdmin {

		public $error_message;

		public function __construct() {
			$this->activation_check();
			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
			$this->init_gitlab_client();
			$this->settings();
			$this->add_product_metabox();
			add_action( 'wp_ajax_rtwoogl_test_connection', array( $this, 'test_connection_wrapper' ) );
		}

		function activation_check() {
			$version = get_site_option( 'RT_WOO_GITLAB_VERSION', '' );
			if ( empty( $version ) ) {
				add_action( 'admin_notices', array( $this, 'first_time_activation_notice' ) );
				update_site_option( 'RT_WOO_GITLAB_VERSION', RT_WOO_GL_VERSION );
			}
		}

		function first_time_activation_notice() { ?>
			<div class="updated">
				<p><?php _e( 'Welcome to WooCommerce GitLab. Please click <a href="'. admin_url('admin.php?page=woocommerce_settings&tab=rtwoogl') .'">here</a> to configure GitLab Settings.' ); ?></p>
			</div>
		<?php }

		function load_scripts() {
			wp_enqueue_script( 'rtwoogl_script_admin', RT_WOO_GL_URL . 'assets/javascripts/admin.js', array( 'jquery' ), RT_WOO_GL_VERSION );
			wp_localize_script( 'rtwoogl_script_admin', 'adminAjaxURL', admin_url( 'admin-ajax.php' ) );
			wp_localize_script( 'rtwoogl_script_admin', 'rtwoogl_loading_file', admin_url( '/images/loading.gif' ) );
		}

		function init_gitlab_client() {
			global $rtGitlabClient;
			$endPoint       = get_option( 'rtwoogl_api_endpoint', '' );
			$privateToken   = get_option( 'rtwoogl_private_token', '' );
			$rtGitlabClient	= new rtGitlabClient( $endPoint, $privateToken );
		}

		function test_connection_wrapper() {
			extract( rtwoogl_get_query_vars( $_POST, array( 'endPoint', 'token' ) ) );
			$response = $this->test_connection( trailingslashit( $endPoint ), $token );
			echo json_encode( $response );
			die();
		}

		/**
		 * Test Cases - Useful in Automated Testing via PHPUnit
		 * @assert ( 'http://gitlab.example.com/api/v3/', 'GitLab Private Token' ) == array( 'result' => 'success', 'message' => 'Connection Successful.' )
		 * @assert ( '', '' ) == array( 'result' => 'error', 'message' => 'Connection Failed. API Endpoint/Token is missing.' )
		 * @assert ( 'fggrg', 'GitLab Private Token' ) == array( 'result' => 'error', 'message' => 'Connection Failed. API Endpoint URL is invalid.' )
		 * @assert ( 'http://gitlab.example.com/api/v3/', 'avdgvdsg' ) == array( 'result' => 'error', 'message' => 'Connection Failed. Invalid API Endpoint/Token. Please verify.' )
		 * @assert ( 'http://google.com', 'GitLab Private Token' ) == array( 'result' => 'error', 'message' => 'Connection Failed. Invalid API Endpoint/Token. Please verify.' )
		 *
		 * @param type $endPoint
		 * @param type $token
		 * @return type $response (Success/Failure)
		 */
		function test_connection( $endPoint, $token ) {
			$flag     = $this->validate_api( $endPoint, $token );
			$response = false;
			if ( !$flag ) {
				$response = array( 'result' => 'error', 'message' => $this->error_message );
			} else {
				$obj    = new rtGitlabClient( $endPoint, $token );
				$result = $obj->test_connection();
				if ( $result['result'] == 'success' ) {
					$response = array( 'result' => 'success', 'message' => __( 'Connection Successful.', 'rtwoo-gitlab' ) );
				} else {
					$response = array( 'result' => 'error', 'message' => __( 'Connection Failed. '.$result['message'].'. Please verify the settings.', 'rtwoo-gitlab' ) );
				}
			}
			return $response;
		}

		function validate_api( $endPoint, $token ) {
			if ( $endPoint == false || $token == false || empty( $endPoint ) || empty( $token ) ) {
				$this->error_message = __( 'Connection Failed. API Endpoint/Token is missing.', 'rtwoo-gitlab' );
				return false;
			}
			if ( filter_var( trailingslashit( $endPoint ), FILTER_VALIDATE_URL ) === false ) {
				$this->error_message = __( 'Connection Failed. API Endpoint URL is invalid.', 'rtwoo-gitlab' );
				return false;
			}
			return true;
		}

		function settings() {
			global $rtWooGLSettings;
			$rtWooGLSettings = new RtWooGlSettings();
			add_action( 'admin_notices', array( $this, 'gitlab_configure_notice' ) );
		}

		function gitlab_configure_notice() {
			$endPoint       = get_option( 'rtwoogl_api_endpoint', '' );
			$privateToken   = get_option( 'rtwoogl_private_token', '' );
			if ( empty( $endPoint ) || empty( $privateToken ) ) { ?>
				<div class="error">
					<p><?php _e( '<b>WooCommerce GitLab</b> : GitLab Endpoint or Private Token is not set properly. Please check <a href="'. admin_url( 'admin.php?page=woocommerce_settings&tab=rtwoogl' ) .'">here</a>.' ); ?></p>
			    </div>
			<?php } else {
				$error = get_site_option( 'rtwoogl_settings_error' );
				if ( !empty( $error ) ) {
					echo $error;
					update_site_option( 'rtwoogl_settings_error', '' );
				}
			}
		}

		function add_product_metabox() {
			add_action( 'add_meta_boxes', array( $this, 'add_gitlab_project_meta' ) );	// WP 3.0+
			add_action( 'admin_init', array( $this, 'add_gitlab_project_meta' ), 1 );	// backwards compatible
			add_action( 'save_post', array( $this, 'save_gitlab_project_meta' ) );
		}

		function add_gitlab_project_meta() {
			add_meta_box( 'rtwoogl_project_mapping', __( 'GitLab Project', 'rtwoo-gitlab' ), array( $this, 'form_gitlab_project_meta' ), 'product', 'side', 'high' );
		}

		function form_gitlab_project_meta( $post ) {
			global $rtGitlabClient;
			$response = $rtGitlabClient->get_all_projects();
			if( $response['result'] == 'success' ) {
				$projects = $response['body'];
			} else {
				$projects = array();
			}
			$project_id = get_post_meta( $post->ID, '_rtwoogl_project', true );
			wp_nonce_field( plugin_basename( __FILE__ ), '_rtwoogl_noncename' ); ?>
				<label for="_rtwoogl_project" class="selectit"><?php _e( 'GitLab Project', 'rtwoo-gitlab' ); ?></label>
				<select id="_rtwoogl_project" name="_rtwoogl_project">
					<option value=""><?php _e( 'N/A', 'rtwoo-gitlab' ); ?></option>
					<?php foreach ( $projects as $project ) { ?>
					<option value="<?php echo esc_attr( $project->id ); ?>" <?php echo esc_attr( ( $project_id == $project->id ) ? 'selected="selected"' : '' ); ?>><?php echo esc_attr( $project->name_with_namespace ); ?></option>
				<?php } ?>
				</select>
				<?php if ( $response['result'] == 'error' ) { ?>
					<br /><br />
					<span><?php _e('GitLab Connection is failed.<br />Please check the GitLab Settings from '); ?><a target="_blank" href="<?php echo admin_url('admin.php?page=woocommerce_settings&tab=rtwoogl'); ?>">here</a></span>
				<?php } else if ( empty( $projects ) ) { ?>
					<br /><br />
					<span><?php _e('Project list is empty. There are no project repositeries on your GitLab Server.'); ?></span>
				<?php } ?>
		<?php }

		function save_gitlab_project_meta( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			extract( rtwoogl_get_query_vars( $_POST, array( '_rtwoogl_noncename', 'post_type', '_rtwoogl_project' ) ) );
			if ( !wp_verify_nonce( $_rtwoogl_noncename, plugin_basename( __FILE__ ) ) ) {
				return;
			}
			if ( 'product' == $post_type && $_rtwoogl_project ) {
				update_post_meta( $post_id, '_rtwoogl_project', $_rtwoogl_project );
			}
		}
	}
}
