<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * rtWooGLSettings
 *
 * @author udit
 */
if ( !class_exists( 'RtWooGlSettings' ) ) {
	class RtWooGlSettings {

		public function __construct() {
			$this->init_settings();
			add_action( 'woocommerce_update_options_rtwoogl', array( $this, 'update_woo_settings' ) );
			$this->woo_settings_inject();
		}

		function init_settings() {
			global $woocommerce_settings;
			$woocommerce_settings['rtwoogl'] = array(
				array(
					'title' => __( 'General', 'rtwoo-gitlab' ),
					'type' => 'title',
					'id' => 'rtwoogl_general',
				),
				array(
					'title' => __( 'Gitlab Endpoint', 'rtwoo-gitlab' ),
					'desc' => __( 'Gitlab API Endpoint to access the Git Server', 'rtwoo-gitlab' ),
					'id' => 'rtwoogl_api_endpoint',
					'css' => 'min-width:250px;',
					'desc_tip' => true,
					'type' => 'text',
					'default' => '',
				),
				array(
					'title' => __( 'Gitlab Private Token', 'rtwoo-gitlab' ),
					'desc' => __( 'Gitlab API Private Token of a Gitlab Admin who has access of all the Gitlab Repositories.', 'rtwoo-gitlab' ),
					'id' => 'rtwoogl_private_token',
					'css' => 'min-width:250px;',
					'desc_tip' => true,
					'type' => 'text',
					'default' => '',
				),
				array(
					'title' => __( 'Gitlab Forgot Password Link', 'rtwoo-gitlab' ),
					'desc' => __( 'User will be given this link in the email; in case he/she has forgotten the Gitlab login password.', 'rtwoo-gitlab' ),
					'id' => 'rtwoogl_forgot_password_link',
					'css' => 'min-width:250px;',
					'desc_tip' => true,
					'type' => 'text',
					'default' => '',
				),
				array(
					'title' => __( 'Gitlab Default Access', 'rtwoo-gitlab' ),
					'desc' => __( 'Default Access Level for the Gitlab Users that will be created for the projects.', 'rtwoo-gitlab' ),
					'id' => 'rtwoogl_default_access',
					'type' => 'select',
					'class' => 'chosen_select',
					'css' => 'min-width:250px;',
					'default' => '20',
					'desc_tip' => true,
					'options' => array(
						'10' => __( 'Guest', 'rtwoo-gitlab' ),
						'20' => __( 'Reporter', 'rtwoo-gitlab' ),
						'30' => __( 'Developer', 'rtwoo-gitlab' ),
						'40' => __( 'Master', 'rtwoo-gitlab' ),
					)
				),
				array(
					'title' => __( 'Gitlab Admin Email', 'rtwoo-gitlab' ),
					'desc' => __( 'Email Communication will happen on this EmaiID. If not set; Wordpress Admin Email will be used.', 'rtwoo-gitlab' ),
					'id' => 'rtwoogl_admin_email',
					'css' => 'min-width:250px;',
					'desc_tip' => true,
					'type' => 'text',
					'default' => get_option('admin_email'),
				),
				array( 'type' => 'sectionend', 'id' => 'rtwoogl_general' ),
			);
		}

		function update_woo_settings() {
			global $woocommerce_settings;
			woocommerce_update_options( $woocommerce_settings['rtwoogl'] );
		}

		function woo_settings_inject() {
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_woo_settings_tab' ) );
			add_action( 'woocommerce_settings_tabs_rtwoogl', array( $this, 'woo_settings' ) );
		}

		function add_woo_settings_tab( $tabs ) {
			$tabs['rtwoogl'] = __( 'GitLab', 'rtwoo-gitlab' );
			return $tabs;
		}

		function woo_settings() {
			global $woocommerce_settings, $rtWooGLAdmin;
			$endPoint = get_option( 'rtwoogl_api_endpoint', '' );
			$token = get_option( 'rtwoogl_private_token', '' );
			$response = $rtWooGLAdmin->test_connection( $endPoint, $token );
			if( $response['result'] == 'error' ) {
				echo '<div id="rtwoogl_message" class="error fade"><p><strong>'.$response['message'].'</strong></p></div>';
			}
			woocommerce_admin_fields( $woocommerce_settings['rtwoogl'] );
			?>
				<div><a href="#" id="rtwoogl_test_connection" class="button" style="margin-top: 15px;">Test Connection</a></div>
			<?php
		}
	}
}
