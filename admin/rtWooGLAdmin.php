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
if(!class_exists('rtWooGLAdmin')) {
	class rtWooGLAdmin {
		public function __construct() {
			
			$this->initGitlabClient();
			$this->settings();
			$this->addProductMetaBox();
		}
		
		function initGitlabClient() {
			global $rtGitlabClient;
			$endPoint = get_option('rtwoogl_api_endpoint', '');
			$privateToken = get_option('rtwoogl_private_token', '');
			$rtGitlabClient = new rtGitlabClient($endPoint, $privateToken);
		}

		function settings() {
			global $rtWooGLSettings;
			$rtWooGLSettings = new rtWooGLSettings();
		}
		
		function addProductMetaBox() {
			// WP 3.0+
			add_action( 'add_meta_boxes', array($this, 'addGitlabProjectMeta'));
			// backwards compatible
			add_action( 'admin_init', array($this, 'addGitlabProjectMeta'), 1 );
			/* Do something with the data entered */
			add_action( 'save_post', array($this, 'saveGitlabProjectMeta') );
		}
		
		function addGitlabProjectMeta() {
			add_meta_box( 'rtwoogl_project_mapping', __("Gitlab Project","rtPanel"), array($this,'formGitlabProjectMeta'), 'product', 'side', 'high');
		}

		function formGitlabProjectMeta($post) {

			global $rtGitlabClient;
			$projects = $rtGitlabClient->getAllProjects();
			$project_id = get_post_meta($post->ID, '_rtwoogl_project', true);
			wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' );
			?>
				<label for="_rtwoogl_project" class="selectit"><?php _e('Gitlab Project', 'rtwoo-gitlab'); ?></label>
				<select id="_rtwoogl_project" name="_rtwoogl_project">
					<option value=""><?php _e('N/A', 'rtwoo-gitlab'); ?></option>
					<?php foreach ($projects as $project) { ?>
						<option value="<?php echo $project->id; ?>" <?php echo ($project_id==$project->id) ? 'selected="selected"' : ''; ?>><?php echo $project->name_with_namespace; ?></option>
					<?php } ?>
				</select>
			<?php
		}

		function saveGitlabProjectMeta($post_id) {

			// verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;

			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			if(!isset($_POST['post_type']) || !isset($_POST[$_POST['post_type'] . '_noncename']))
				return;

			if ( !wp_verify_nonce( @$_POST[$_POST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) )
				return;

			// OK,nonce has been verified and now we can save the data according the the capabilities of the user
			if( 'product' == $_POST['post_type'] ) {
				if(isset($_POST['_rtwoogl_project'])) {
					update_post_meta($post_id, '_rtwoogl_project', $_POST['_rtwoogl_project']);
				}
			}
		}
	}
}
?>
