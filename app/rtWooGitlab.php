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
			add_action('woocommerce_checkout_order_processed', array($this, 'addUserOnCheckout'), 10, 2);
			add_action('woocommerce_order_status_changed', array($this, 'removeUserOnOrderStatusChange'),10, 3);
			add_action('before_delete_post', array($this, 'removeUserOnOrderDelete'), 10, 1);
		}

		function addUserOnCheckout($orderID, $orderMeta) {

			global $rtGitlabClient;
			$order = new WC_Order($orderID);
			if(empty($order))
				return;

			$createUser = false;
			foreach ($order->get_items() as $product) {
				$project_id = get_post_meta($product['product_id'], '_rtwoogl_project', true);
				if(!empty($project_id))
					$createUser = true;
			}

			if(!$createUser)
				return;

			// Create Gitlab User
			$wp_user = get_user_by('id', get_current_user_id());
			$rtWooGLUser = $rtGitlabClient->searchUser($wp_user->data->user_email);
			if(empty($rtWooGLUser)) {
				$email = $wp_user->data->user_email;
				$password = wp_generate_password();

				$s = explode("@",$email);
				array_pop($s); #remove last element.
				$s = implode("@",$s);
				$username = $s;
				if(!empty($wp_user->data->display_name))
					$name = $wp_user->data->display_name;
				else $name = $s;
				$rtWooGLUser = $rtGitlabClient->createUser($email, $password, $username, $name);

				if(empty($rtWooGLUser)) {
					$message = 'User Creation has failed via rtWooGitlab in Project '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>) for the Order #'.$orderID.'. User Details which failed ara as follows:<br />
						Email: '.$email.'<br />
						Username: '.$username.'<br />
						Name: '.$name;

					rtWooGLMail('[rtWooGitlab] IMPORTANT - Unexpected Behavior', $message);
					return;
				}
			}

			foreach ($order->get_items() as $product) {
				$project_id = get_post_meta($product['product_id'], '_rtwoogl_project', true);
				if(empty($project_id))
					continue;

				$projectDetails = $rtGitlabClient->getProjectDetails($project_id);

				$projectMemberDetails = $rtGitlabClient->addUserToProject($rtWooGLUser->id, $project_id, 10);
				if(empty($projectMemberDetails)) {
					$message = 'User could not be added to Project '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$orderID.'. User Details which failed ara as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name;
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
				} else {
					update_user_meta(get_current_user_id(), '_rtwoogl_user_id', $projectMemberDetails->id);
					$message = 'New User is added to the project as guest.<br />
						Project: '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>)<br />
						User: '.$projectMemberDetails->name.'('.$projectMemberDetails->username.')';
					$subject = '[rtWooGitlab] New User added to Gitlab Project';
				}
				rtWooGLMail($subject, $message);
			}
		}

		function removeUserOnOrderStatusChange($orderID, $oldStatus, $newStatus) {
			if($newStatus === 'refunded') {
				$this->removeUserOnOrderDelete($orderID);
			}
		}

		function removeUserOnOrderDelete($postID) {

			global $rtGitlabClient;
			$order = new WC_Order($postID);
			if(empty($order))
				return;

			$wp_user_id = get_post_meta($order->id, '_customer_user', true);
			$rtwoogl_user_id = get_user_meta($wp_user_id, '_rtwoogl_user_id', true);

			foreach ($order->get_items() as $product) {
				$project_id = get_post_meta($product['product_id'], '_rtwoogl_project', true);

				if(empty($project_id))
					return;

				$projectDetails = $rtGitlabClient->getProjectDetails($project_id);

				if(empty($rtwoogl_user_id))
					return;

				$response = $rtGitlabClient->removeUserFromProject($rtwoogl_user_id, $project_id);
				$rtWooGLUser = $rtGitlabClient->getUser($rtwoogl_user_id);
				if(empty($response)) {
					$message = 'User could not be removed/alreadey removed from Project '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$postID.'. User Details which failed ara as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name;
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
				} else {
					$message = 'User is removed successfully from Project '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$postID.'. User Details which failed ara as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name;
					$subject = '[rtWooGitlab] User removed from Gitlab Project';
				}
				rtWooGLMail($subject, $message);
			}
		}
	}
}
?>
