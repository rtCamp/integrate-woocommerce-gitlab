<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			$email_settings = get_option( 'woocommerce_customer_completed_order_settings', true );
			if( !empty( $email_settings ) ) {
				$email_enabled = $email_settings['enabled'];
			} else {
				$email_enabled = 'yes';
			}
			if ( $email_enabled == 'yes' ) {
				add_action( 'woocommerce_order_status_completed_notification', array( $this, 'add_user_on_order_complete' ), 1, 1 );
			} else {
				add_action( 'woocommerce_order_status_changed', array( $this, 'add_user_on_order_status_complete' ), 1, 3 );
			}
			add_action( 'woocommerce_order_status_changed', array( $this, 'remove_user_on_order_status_change' ),1, 3 );
			add_action( 'before_delete_post', array( $this, 'remove_user_on_order_delete' ), 1, 1 );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'add_gitlab_user_details' ), 1, 2 );
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
			$rtwoogl_user_id = get_post_meta( $order->id, '_rtwoogl_user_id', true );
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
			$rtwoogl_user_id = get_post_meta( $order->id, '_rtwoogl_user_id', true );
			$rtWooGLUser     = false;
			$response        = $rtGitlabClient->get_user( $rtwoogl_user_id );
			if ( $response['result'] == 'success' ) {
				$rtWooGLUser = $response['body'];
			} else {
				$message = 'Gitlab Credentials could not be sent via rtWooGitlab for the Order #'.$order->id.':<br />
					User: '.$order->billing_first_name.' '.$order->billing_last_name.'<br />
					Email: '.$order->billing_email.'<br />
					Cause: '.$response['message'];

				rtwoogl_mail( '[rtWooGitlab] IMPORTANT - Unexpected Behavior', $message );
				return;
			}
			$rtWooGLUserStatus         = get_post_meta( $order->id, '_rtwoogl_user', true );
			$password                  = get_post_meta( $order->id, '_rtwoogl_user_pwd', true );
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

		function create_gitlab_user( $orderID ) {
			global $rtGitlabClient;
			$user_id = get_post_meta( $orderID, '_customer_user', true );
			if( !empty( $user_id ) ) {
				$user = new WP_User( $user_id );
				$email = $user->data->user_email;
			} else {
				$email = get_post_meta( $orderID, '_billing_email', true );
			}
			$password = wp_generate_password();

			$fname    = get_post_meta( $orderID, '_billing_first_name', true );
			$lname    = get_post_meta( $orderID, '_billing_last_name', true );
			$s        = explode( '@', $email );
			array_pop( $s );
			$s        = implode( '@', $s );
			$username = $s;
			$rtWooGLUser = false;
			$response = $rtGitlabClient->create_user( $email, $password, $username, $username );
			if ( $response['result'] == 'success' ) {
				update_post_meta( $orderID, '_rtwoogl_user', 'new' );
				update_post_meta( $orderID, '_rtwoogl_user_pwd', $password );
				$rtWooGLUser = $response['body'];
			} else {
				$username = strtolower($fname).'.'.  strtolower($lname);
				$response = $rtGitlabClient->create_user( $email, $password, $username, $username );
				if ( $response['result'] == 'success' ) {
					update_post_meta( $orderID, '_rtwoogl_user', 'new' );
					update_post_meta( $orderID, '_rtwoogl_user_pwd', $password );
					$rtWooGLUser = $response['body'];
				} else {
					$message = 'User Creation has failed via rtWooGitlab for the Order #'.$orderID.'. User Details which failed are as follows:<br />
						Email: '.$email.'<br />
						Username: '.$username.'<br />
						Cause: '.$response['message'];

					rtwoogl_mail( '[rtWooGitlab] IMPORTANT - Unexpected Behavior', $message );
				}
			}
			return $rtWooGLUser;
		}

		function prepare_gitlab_user( $orderID ) {
			global $rtGitlabClient;
			$user_id = get_post_meta( $orderID, '_customer_user', true );
			if( !empty( $user_id ) ) {
				$user = new WP_User( $user_id );
				$email = $user->data->user_email;
			} else {
				$email = get_post_meta( $orderID, '_billing_email', true );
			}
			$response = $rtGitlabClient->search_user( $email );
			if ( $response['result'] == 'error' ) {
				$rtWooGLUser = $this->create_gitlab_user( $orderID );
			} else {
				$rtWooGLUser = $response['body'];
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
				$response = $rtGitlabClient->get_project_details( $project_id );
				if ( $response['result'] == 'success' ) {
					$projectDetails = $response['body'];
				} else {
					$message = 'User could not be added to the project via rtWooGitlab for the Product - '.$product['name'].'. Details which failed ara as follows:<br />
						Order: #'.$order->id.'<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name.'<br /><br />
						Cause: Could not fetch details for the project. <br />'.$response['message'];
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
					continue;
				}
				$response = $rtGitlabClient->add_user_to_project( $rtWooGLUser->id, $project_id, $accessLevel );
				if ( $response['result'] == 'error' ) {
					$message = 'User could not be added to Project '.$projectDetails->name_with_namespace.' (<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$order->id.'. User Details which failed are as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name.'<br /><br />
						Cause: '.$response['message'];
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
				} else {
					$projectMemberDetails = $response['body'];
					$message = 'New User is added to the project.<br />
						Project: '.$projectDetails->name_with_namespace.' (<a href="'.$projectDetails->web_url.'">here</a>)<br />
						User: '.$projectMemberDetails->name.'('.$projectMemberDetails->username.')';
					$subject = '[rtWooGitlab] New User added to Gitlab Project';
				}
				rtwoogl_mail( $subject, $message );
			}
		}

		function add_user_on_order_complete( $orderID ) {
			$flag = $this->validate_checkout( $orderID );
			if ( !$flag ) {
				return;
			}
			$rtWooGLUser = $this->prepare_gitlab_user( $orderID );
			if ( empty( $rtWooGLUser ) ) {
				return;
			}
			update_post_meta( $orderID, '_rtwoogl_user_id', $rtWooGLUser->id );
			$accessLevel = get_option( 'rtwoogl_default_access', '20' );
			$order = new WC_Order( $orderID );
			$this->grant_access_to_gitlab( $order, $rtWooGLUser, $accessLevel );
		}

		function add_user_on_order_status_complete( $orderID, $oldStatus, $newStatus ) {
			if( $newStatus != 'completed' ) {
				return;
			}
			$this->add_user_on_order_complete( $orderID );
		}

		function remove_user_on_order_status_change( $orderID, $oldStatus, $newStatus ) {
			if ( $newStatus === 'refunded' || $newStatus === 'cancelled' ) {
				$this->remove_user_on_order_delete( $orderID );
			}
		}

		function revoke_access_from_gitlab( $order, $rtWooGLUser ) {
			global $rtGitlabClient;
			foreach ( $order->get_items() as $product ) {
				$project_id = get_post_meta( $product['product_id'], '_rtwoogl_project', true );
				if ( empty( $project_id ) ) {
					continue;
				}
				$response = $rtGitlabClient->get_project_details( $project_id );
				if ( $response['result'] == 'success' ) {
					$projectDetails = $response['body'];
				} else {
					$message = 'User could not be removed from the project via rtWooGitlab for the Product - '.$product['name'].'. Details which failed are as follows:<br />
						Order: #'.$order->id.'<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name.'<br /><br />
						Cause: Could not fetch details for the project. <br />'.$response['message'];
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
					rtwoogl_mail( $subject, $message );
					continue;
				}
				$response = $rtGitlabClient->remove_user_from_project( $rtWooGLUser->id, $project_id );
				if ( $response['result'] == 'error' ) {
					$message = 'User could not be removed/alreadey removed from Project '.$projectDetails->name_with_namespace.' (<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$order->id.'. User Details which failed are as follows:<br />
						Email: '.$rtWooGLUser->email.'<br />
						Username: '.$rtWooGLUser->username.'<br />
						Name: '.$rtWooGLUser->name.'<br /><br />
						Cause: '.$response['message'];
					$subject = '[rtWooGitlab] IMPORTANT - Unexpected Behavior';
				} else {
					$message = 'User is removed successfully from Project '.$projectDetails->name_with_namespace.' (<a href="'.$projectDetails->web_url.'">here</a>) via rtWooGitlab for the Order #'.$order->id.'. User Details which failed are as follows:<br />
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
			$rtwoogl_user_id = get_post_meta( $order->id, '_rtwoogl_user_id', true );
			if ( empty( $rtwoogl_user_id ) ) {
				return;
			}
			$response = $rtGitlabClient->get_user( $rtwoogl_user_id );
			if ( $response['result'] == 'error' ) {
				return;
			}
			$rtWooGLUser = $response['body'];
			$this->revoke_access_from_gitlab( $order, $rtWooGLUser );
		}
	}
}