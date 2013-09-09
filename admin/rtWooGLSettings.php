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
					'type' => 'text',
					'default' => ''
				),
				array(
					'title' => __('Gitlab Private Token', 'rtwoo-gitlab'),
					'desc' => __('Gitlab API Private Token', 'rtwoo-gitlab'),
					'id' => 'rtwoogl_private_token',
					'type' => 'text',
					'default' => ''
				),
				array(
					'title' => __('Gitlab Forgot Password Link', 'rtwoo-gitlab'),
					'desc' => __('User will be given this link; in case he/she has forgotten the Gitlab login password.', 'rtwoo-gitlab'),
					'id' => 'rtwoogl_forgot_password_link',
					'type' => 'text',
					'default' => ''
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
		}
	}
}
?>
