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
 * Description of rtGitlabClient
 *
 * @author udit
 */
if ( !class_exists( 'RtGitlabClient' ) ) {
	class RtGitlabClient {

		var $endPoint;
		var $privateToken;

		public function __construct( $endPoint, $privateToken ) {
			$this->endPoint     = $endPoint;
			$this->privateToken = $privateToken;
		}

		function get_all_projects() {
			if ( empty( $this->endPoint ) ) {
				return false;
			}
			$projects = array();
			$page     = 1;
			while ( 1 ) {
				$response = \Httpful\Request::get( $this->endPoint.'projects?page='.$page )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
				if ( !empty( $response->body ) && is_array( $response->body ) ) {
					foreach ( $response->body as $project ) {
						$projects[] = $project;
					}
				} else {
					break;
				}
				$page++;
			}
			return $projects;
		}

		function test_connection() {
			if ( empty( $this->endPoint ) ) {
				return false;
			}
			$response = \Httpful\Request::get( $this->endPoint.'projects' )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
			if ( isset( $response->body->message ) || !is_array( $response->body ) ) {
				return false;
			}
			return true;
		}

		function get_project_details( $projectID ) {
			if ( empty( $this->endPoint ) ) {
				return false;
			}
			$response = \Httpful\Request::get( $this->endPoint.'/projects/'.$projectID )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
			if ( isset( $response->body->message ) || !isset( $response->body->id ) ) {
				return false;
			}
			return $response->body;
		}

		function get_user( $id ) {
			if ( empty( $this->endPoint ) ) {
				return false;
			}
			$response = \Httpful\Request::get( $this->endPoint.'/users/'.$id )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
			if ( isset( $response->body->message ) || !isset( $response->body->id ) ) {
				return false;
			}
			return $response->body;
		}

		function create_user( $email, $password, $username, $name ) {
			if ( empty( $this->endPoint ) ) {
				return false;
			}
			$args = array(
				'email' => $email,
				'password' => $password,
				'username' => $username,
				'name' => $name,
				'provider' => 'rtwoo_gitlab',
			);
			$response = \Httpful\Request::post( $this->endPoint.'/users' )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->body( json_encode( $args ) )->sendsJson()->send();

			if ( isset( $response->body->message ) || !isset( $response->body->id ) ) {
				return false;
			}
			return $response->body;
		}

		function add_user_to_project( $userID, $projectID, $accessLevel ) {
			if ( empty( $this->endPoint ) ) {
				return false;
			}
			$args = array(
				'id' => $projectID,
				'user_id' => $userID,
				'access_level' => $accessLevel,
			);
			$response = \Httpful\Request::post( $this->endPoint.'/projects/'.$projectID.'/members' )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->body( json_encode( $args ) )->sendsJson()->send();
			if ( isset( $response->body->message ) || !isset( $response->body->id ) ) {
				return false;
			}
			return $response->body;
		}

		function remove_user_from_project( $userID, $projectID ) {
			if ( empty( $this->endPoint ) ) {
				return false;
			};
			$args = array(
				'id' => $projectID,
				'user_id' => $userID,
			);
			$response = \Httpful\Request::delete( $this->endPoint.'/projects/'.$projectID.'/members/'.$userID )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->body( json_encode( $args ) )->sendsJson()->send();
			if ( isset( $response->body->message ) || !isset( $response->body->id ) ) {
				return false;
			}
			return $response->body;
		}

		function search_user( $email ) {
			if ( empty( $this->endPoint ) ) {
				return false;
			}
			$page = 1;
			while ( 1 ) {
				$response = \Httpful\Request::get( $this->endPoint.'users/?page='.$page )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
				if ( !empty( $response->body ) && is_array( $response->body ) ) {
					foreach ( $response->body as $user ) {
						if ( $user->email == $email ) {
							return $user;
						}
					}
				} else {
					break;
				}
				$page++;
			}
			return false;
		}
	}
}
