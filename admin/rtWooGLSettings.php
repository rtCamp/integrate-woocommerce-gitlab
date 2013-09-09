<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtWooGLSettings
 *
 * @author udit
 */
if(!class_exists('rtWooGLSettings')) {
	class rtWooGLSettings {
		public function __construct() {
			$this->initSettings();

			add_action('woocommerce_update_options_rtwoogl', array($this, 'updateWooSettings'));

			$this->wooSettingsInject();
		}

		function initSettings() {
			global $woocommerce_settings;
			$woocommerce_settings['rtwoogl'] = array(
				array(
					'title' => __('General', 'rtwoo-gitlab'),
					'type' => 'title',
					'desc' => '',
					'id' => 'rtwoogl_general'
				),
				array(
					'title' => __('Gitlab Endpoint', 'rtwoo-gitlab'),
					'desc' => __('Gitlab API Endpoint to Access the Git Server', 'rtwoo-gitlab'),
					'id' => 'rtwoogl_api_endpoint',
					'css' => 'min-width:250px;',
					'desc_tip'	=>  true,
					'type' => 'text',
					'default' => ''
				),
				array(
					'title' => __('Gitlab Private Token', 'rtwoo-gitlab'),
					'desc' => __('Gitlab API Private Token', 'rtwoo-gitlab'),
					'id' => 'rtwoogl_private_token',
					'css' => 'min-width:250px;',
					'desc_tip'	=>  true,
					'type' => 'text',
					'default' => ''
				),
				array(
					'title' => __('Gitlab Forgot Password Link', 'rtwoo-gitlab'),
					'desc' => __('User will be given this link; in case he/she has forgotten the Gitlab login password.', 'rtwoo-gitlab'),
					'id' => 'rtwoogl_forgot_password_link',
					'css' => 'min-width:250px;',
					'desc_tip'	=>  true,
					'type' => 'text',
					'default' => ''
				),
				array(
					'title' => __('Gitlab Default Access', 'rtwoo-gitlab'),
					'desc' => __('Default Access Level for the Gitlab Users that will be created for the projects.', 'rtwoo-gitlab'),
					'id' => 'rtwoogl_default_access',
					'type' 		=> 'select',
					'class'		=> 'chosen_select',
					'css' 		=> 'min-width:250px;',
					'default'	=> '20',
					'desc_tip'	=>  true,
					'options' => array(
						'10'  	=> __( 'Guest', 'rtwoo-gitlab' ),
						'20' => __( 'Reporter', 'rtwoo-gitlab' ),
						'30'  => __( 'Developer', 'rtwoo-gitlab' ),
						'40'  => __( 'Master', 'rtwoo-gitlab' ),
					)
				),
				array( 'type' => 'sectionend', 'id' => 'rtwoogl_general')
			);
		}

		function updateWooSettings() {
			global $woocommerce_settings;
			woocommerce_update_options($woocommerce_settings['rtwoogl']);
		}

		function wooSettingsInject() {
			add_filter('woocommerce_settings_tabs_array', array($this, 'addWooSettingsTab'));
			add_action('woocommerce_settings_tabs_rtwoogl', array($this, 'wooSettings'));
		}

		function addWooSettingsTab($tabs) {
			$tabs['rtwoogl'] = __('Gitlab', 'rtwoo-gitlab');
			return $tabs;
		}

		function wooSettings() {

			global $woocommerce_settings;
			woocommerce_admin_fields($woocommerce_settings['rtwoogl']);
			?>
				<div>
					<a href="#" id="rtwoogl_test_connection" class="button" style="margin-top: 15px;">Test Connection</a>
				</div>
				<script>
					jQuery('#rtwoogl_test_connection').click(function(e) {
						e.preventDefault();
						var rtwoogl_loading_file = '<?php echo admin_url ( "/images/loading.gif" ); ?>';
						var that = this;
						jQuery(that).parent().append('<img class="tmp-process" src="'+ rtwoogl_loading_file +  '" />');
						jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
							action: 'rtwoogl_test_connection',
							endPoint: jQuery('#rtwoogl_api_endpoint').val().trim(),
							token: jQuery('#rtwoogl_private_token').val().trim()
						}, function(data, status, xhr) {
							jQuery(that).next().remove();
							data = jQuery.parseJSON(data);
							if(data.message !== 'undefined')
								jQuery(that).parent().append('<span>'+data.message+'</span>');
						});
					});
				</script>
			<?php
		}
	}
}
?>
