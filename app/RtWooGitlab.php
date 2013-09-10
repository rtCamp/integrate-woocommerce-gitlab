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
if ( !class_exists( 'RtWooGitlab' ) ) {
	class RtWooGitlab {
		
		public $templateURL;
		
		public function __construct() {
			// Plugin Init
			add_action( 'init', array( $this, 'admin_init' ), 5 );
			add_action( 'init', array( $this, 'init' ), 6 );
		}

		function admin_init() {
			global $rtWooGLAdmin;
			$rtWooGLAdmin = new RtWooGlAdmin();
		}

		function init() {
			$this->templateURL = apply_filters( 'rtwoogl_template_url', 'rtwoogl/' );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_user_on_checkout' ), 10, 2 );
			add_action( 'woocommerce_order_status_changed', array( $this, 'remove_user_on_order_status_change' ),10, 3 );
			add_action( 'before_delete_post', array( $this, 'remove_user_on_order_delete' ), 10, 1 );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'add_gitlab_user_details' ), 10, 2 );
		}

		function validate_email_hijack( $order ) {
			$flag = rtwoogl_backtrace( 'trigger', 'WC_Email_Customer_Completed_Order' );
			if ( !$flag ) {
				return false;
			}
			$rtWooGLUserStatus = get_post_meta( $order->id, '_rtwoogl_user', true );
			if ( empty( $rtWooGLUserStatus ) ) {
				return false;
			}
			$wp_user_id      = get_post_meta( $order->id, '_customer_user', true );
			$rtwoogl_user_id = get_user_meta( $wp_user_id, '_rtwoogl_user_id', true );
			if ( empty( $rtwoogl_user_id ) ) {
				return false;
			}
			return true;
		}

		function add_gitlab_user_details( $order, $sendToAdmin = false ) {

			global $rtGitlabClient;
			if ( !$this->validate_email_hijack( $order ) ) {
				return;
			}
			$wp_user_id                = get_post_meta( $order->id, '_customer_user', true );
			$rtwoogl_user_id           = get_user_meta( $wp_user_id, '_rtwoogl_user_id', true );
			$rtWooGLUser               = $rtGitlabClient->get_user( $rtwoogl_user_id );
			$rtWooGLUserStatus         = get_post_meta( $order->id, '_rtwoogl_user', true );
			$password                  = get_user_meta( $wp_user_id, '_rtwoogl_user_pwd', true );
			$rtWooGLForgotPasswordLink = get_option( 'rtwoogl_forgot_password_link', '' );

			rtwoogl_get_template(
				'rtwoogl-project-details-mail.php', array(
					'rtWooGLUser' => $rtWooGLUser,
					'rtWooGLUserStatus' => $rtWooGLUserStatus,
					'password' => $password,
					'rtWooGLForgotPasswordLink' => $rtWooGLForgotPasswordLink,
					'order' => $order,
					'rtGitlabClient' => $rtGitlabClient,
				)
			);
		}
		
		function validate_checkout( $orderID ) {
			$order = new WC_Order( $orderID );
			if ( empty( $order ) ) {
				return false;
			}
			foreach ( $order->get_items() as $product ) {
				$project_id = get_post_meta( $product['product_id'], '_rtwoogl_project', true );
				if ( !empty( $project_id ) ) {
					return true;
				}
			}
			return false;
		}
		
		function create_gitlab_user( $wp_user, $orderID ) {
			global $rtGitlabClient;
			$email    = $wp_user->data->user_email;
			$password = wp_generate_password();

			$s = explode( '@', $email );
			array_pop( $s ); #remove last element.
			$s        = implode( '@', $s );
			$username = $s;
			if ( !empty( $wp_user->data->display_name ) ) {
				$name = $wp_user->data->display_name;
			} else {
				$name = $s;
			}
			$rtWooGLUser = $rtGitlabClient->create_user( $email, $password, $username, $name );

			if ( empty( $rtWooGLUser ) ) {
				$message = 'User Creation has failed via rtWooGitlab for the Order #'.$orderID.'. User Details which failed ara as follows:<br />
					Email: '.$email.'<br />
					Username: '.$username.'<br />
					Name: '.$name;

				rtwoogl_mail( '[rtWooGitlab] IMPORTANT - Unexpected Behavior', $message );
				return false;
			} else {
				update_post_meta( $orderID, '_rtwoogl_user', 'new' );
				update_user_meta( $wp_user->ID, '_rtwoogl_user_pwd', $password );
			}
			return $rtWooGLUser;
		}

		function prepare_gitlab_user( $orderID ) {
			global $rtGitlabClient;
			$wp_user     = get_user_by( 'id', get_current_user_id() );
			$rtWooGLUser = $rtGitlabClient->search_user( $wp_user->data->user_email );
			if ( empty( $rtWooGLUser ) ) {
				$rtWooGLUser = $this->create_gitlab_user( $wp_user, $orderID );
			} else {
				update_post_meta( $orderID, '_rtwoogl_user', 'old' );
			}
			return $rtWooGLUser;
		}
		
		function grant_access_to_gitlab( $order, $rtWooGLUser, $accessLevel ) {
			global $rtGitlabClient;
			foreach ( $order->get_items() as $product ) {
				$project_id = get_post_meta( $product['product_id'], '_rtwoogl_project', true );
				if ( empty( $project_id ) ) {
					continue;
				}
				$projectDetails = $rtGitlabClient->get_project_details( $project_id );
				$projectMemberDetails = $rtGitlabClient->add_user_to_project( $rtWooGLUser->id, $project_id, $accessLevel );
				if ( empty( $projectMemberDetails ) ) {
					$message = 'User could not be added to Project '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$order->id.'. User Details which failed ara as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name;
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
				} else {
					$message = 'New User is added to the project.<br />
						Project: '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>)<br />
						User: '.$projectMemberDetails->name.'('.$projectMemberDetails->username.')';
					$subject = '[rtWooGitlab] New User added to Gitlab Project';
				}
				rtwoogl_mail( $subject, $message );
			}
		}

		function add_user_on_checkout( $orderID, $orderMeta ) {
			global $rtGitlabClient;
			$flag = $this->validate_checkout( $orderID );
			if ( !$flag ) {
				return;
			}
			$rtWooGLUser = $this->prepare_gitlab_user( $orderID );
			if ( empty( $rtWooGLUser ) ) {
				return;
			}
			update_user_meta( get_current_user_id(), '_rtwoogl_user_id', $rtWooGLUser->id );
			$accessLevel = get_option( 'rtwoogl_default_access', '20' );
			$order = new WC_Order( $orderID );
			$this->grant_access_for_gitlab( $order, $rtWooGLUser, $accessLevel );
		}

		function remove_user_on_order_status_change( $orderID, $oldStatus, $newStatus ) {
			if ( $newStatus === 'refunded' ) {
				$this->remove_user_on_order_delete( $orderID );
			}
		}
		
		function revoke_access_from_gitlab( $order, $rtWooGLUser ) {
			global $rtGitlabClient;
			foreach ( $order->get_items() as $product ) {
				$project_id = get_post_meta( $product['product_id'], '_rtwoogl_project', true );
				if ( empty( $project_id ) ) {
					return;
				}
				$projectDetails = $rtGitlabClient->get_project_details( $project_id );
				$response       = $rtGitlabClient->remove_user_from_project( $rtWooGLUser->id, $project_id );
				if ( empty( $response ) ) {
					$message = 'User could not be removed/alreadey removed from Project '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$order->id.'. User Details which failed ara as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name;
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
				} else {
					$message = 'User is removed successfully from Project '.$projectDetails->name_with_namespace.'(<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$order->id.'. User Details which failed ara as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name;
					$subject = '[rtWooGitlab] User removed from Gitlab Project';
				}
				rtwoogl_mail( $subject, $message );
			}
		}

		function remove_user_on_order_delete( $postID ) {

			global $rtGitlabClient;
			$order = new WC_Order( $postID );
			if ( empty( $order ) ) {
				return;
			}

			$wp_user_id      = get_post_meta( $order->id, '_customer_user', true );
			$rtwoogl_user_id = get_user_meta( $wp_user_id, '_rtwoogl_user_id', true );
			if ( empty( $rtwoogl_user_id ) ) {
				return;
			}
			$rtWooGLUser = $rtGitlabClient->get_user( $rtwoogl_user_id );

			$this->revoke_access_from_gitlab( $order, $rtWooGLUser );

		}
	}
}