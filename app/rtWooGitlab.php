<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtWooGitlab
 *
 * @author udit
 */
if(!class_exists('rtWooGitlab')) {
	class rtWooGitlab {
		public function __construct() {
			// Plugin Init
			add_action('init', array($this, 'admin_init'), 5);
			add_action('init', array($this, 'init'), 6);
		}
		
		function admin_init() {
			global $rtWooGLAdmin;
			$rtWooGLAdmin = new rtWooGLAdmin();
		}
		
		function init() {
			
		}
	}
}
?>
