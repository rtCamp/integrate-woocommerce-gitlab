<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtWooGLAdmin
 *
 * @author udit
 */
if ( !class_exists( 'RtWooGlAdmin' ) ) {
	class RtWooGlAdmin {
		
		public $error_message;

		public function __construct() {
			$this->init_gitlab_client();
			$this->settings();
			$this->add_product_metabox();
			add_action( 'wp_ajax_rtwoogl_test_connection', array( $this, 'test_connection_wrapper' ) );
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

		function test_connection( $endPoint, $token ) {
			$flag     = $this->validate_api( $endPoint, $token );
			$response = false;
			if ( !$flag ) {
				$response = array( 'result' => 'error', 'message' => $this->error_message );
			} else {
				$obj    = new rtGitlabClient( $endPoint, $token );
				$result = $obj->test_connection();
				if ( $result ) {
					$response = array( 'result' => 'success', 'message' => __( 'Connection Successful.', 'rtwoo-gitlab' ) );
				} else {
					$response = array( 'result' => 'error', 'message' => __( 'Connection Failed. Invalid API Endpoint/Token. Please verify.', 'rtwoo-gitlab' ) );
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
		}

		function add_product_metabox() {
			add_action( 'add_meta_boxes', array( $this, 'add_gitlab_project_meta' ) );	// WP 3.0+
			add_action( 'admin_init', array( $this, 'add_gitlab_project_meta' ), 1 );	// backwards compatible
			add_action( 'save_post', array( $this, 'save_gitlab_project_meta' ) );
		}

		function add_gitlab_project_meta() {
			add_meta_box( 'rtwoogl_project_mapping', __( 'Gitlab Project', 'rtwoo-gitlab' ), array( $this, 'form_gitlab_project_meta' ), 'product', 'side', 'high' );
		}

		function form_gitlab_project_meta( $post ) {
			global $rtGitlabClient;
			$projects   = $rtGitlabClient->get_all_projects();
			$project_id = get_post_meta( $post->ID, '_rtwoogl_project', true );
			wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' ); ?>
				<label for="_rtwoogl_project" class="selectit"><?php _e( 'Gitlab Project', 'rtwoo-gitlab' ); ?></label>
				<select id="_rtwoogl_project" name="_rtwoogl_project">
					<option value=""><?php _e( 'N/A', 'rtwoo-gitlab' ); ?></option>
					<?php foreach ( $projects as $project ) { ?>
					<option value="<?php echo esc_attr( $project->id ); ?>" <?php echo esc_attr( ( $project_id == $project->id ) ? 'selected="selected"' : '' ); ?>><?php echo esc_attr( $project->name_with_namespace ); ?></option>
				<?php } ?>
				</select>
			<?php }

		function save_gitlab_project_meta( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( !isset( $_POST['post_type'] ) || !isset( $_POST[$_POST['post_type'] . '_noncename'] ) ) {
				return;
			}
			if ( !wp_verify_nonce( $_POST[$_POST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) ) {
				return;
			}
			if ( 'product' == $_POST['post_type'] ) {
				if ( isset($_POST['_rtwoogl_project'] ) ) {
					update_post_meta( $post_id, '_rtwoogl_project', $_POST['_rtwoogl_project'] );
				}
			}
		}
	}
}
?>
