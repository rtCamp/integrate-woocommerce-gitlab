<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * rtGitlabClient
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
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			}
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
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
			$result['result'] = 'success';
			$result['body'] = $projects;
			return $result;
		}

		function test_connection() {
			if ( empty( $this->endPoint ) ) {
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			}
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
			}
			$response = \Httpful\Request::get( $this->endPoint.'projects' )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
			$result = $this->identify_response($response);
			return $result;
		}

		function identify_response( $response ) {
			$result = array();
			if ( !is_array( $response->body ) && !isset( $response->body->id ) ) {
				$result['result'] = 'error';
				if( isset( $response->body->message ) ) {
					$result['message'] = $response->body->message;
				} else if ( $response->code === 404 ) {
					$result['message'] = 'Invalid API Endpoint.';
				} else {
					$result['message'] = var_export($response->body, true);
				}
			} else {
				$result['result'] = 'success';
				$result['body'] = $response->body;
			}
			return $result;
		}

		function get_project_details( $projectID ) {
			if ( empty( $this->endPoint ) ) {
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			}
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
			}
			$response = \Httpful\Request::get( $this->endPoint.'/projects/'.$projectID )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
			$result = $this->identify_response($response);
			return $result;
		}

		function get_user( $id ) {
			if ( empty( $this->endPoint ) ) {
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			}
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
			}
			$response = \Httpful\Request::get( $this->endPoint.'/users/'.$id )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
			$result = $this->identify_response($response);
			return $result;
		}

		function create_user( $email, $password, $username, $name ) {
			if ( empty( $this->endPoint ) ) {
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			}
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
			}
			$args = array(
				'email' => $email,
				'password' => $password,
				'username' => $username,
				'name' => $name,
				'provider' => 'rtwoo_gitlab',
			);
			$response = \Httpful\Request::post( $this->endPoint.'/users' )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->body( json_encode( $args ) )->sendsJson()->send();
			$result = $this->identify_response($response);
			return $result;
		}

		function add_user_to_project( $userID, $projectID, $accessLevel ) {
			if ( empty( $this->endPoint ) ) {
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			}
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
			}
			$args = array(
				'id' => $projectID,
				'user_id' => $userID,
				'access_level' => $accessLevel,
			);
			$response = \Httpful\Request::post( $this->endPoint.'/projects/'.$projectID.'/members' )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->body( json_encode( $args ) )->sendsJson()->send();
			$result = $this->identify_response($response);
			return $result;
		}

		function remove_user_from_project( $userID, $projectID ) {
			if ( empty( $this->endPoint ) ) {
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			};
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
			}
			$args = array(
				'id' => $projectID,
				'user_id' => $userID,
			);
			$response = \Httpful\Request::delete( $this->endPoint.'/projects/'.$projectID.'/members/'.$userID )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->body( json_encode( $args ) )->sendsJson()->send();
			$result = $this->identify_response($response);
			return $result;
		}

		function search_user( $email ) {
			if ( empty( $this->endPoint ) ) {
				return array( 'result' => 'error', 'message' => 'API Endpoint Not Found.' );
			}
			if ( empty( $this->privateToken ) ) {
				return array( 'result' => 'error', 'message' => 'Private Token Not Found.' );
			}
			$page = 1;
			while ( 1 ) {
				$response = \Httpful\Request::get( $this->endPoint.'users/?page='.$page )->addHeader( 'PRIVATE-TOKEN', $this->privateToken )->send();
				if ( isset( $response->body->message ) ) {
					$result = array( 'result' => 'error', 'message' => $response->body->message );
					return $result;
				} else if ( $response->code == 404 ) {
					$result = array( 'result' => 'error', 'message' => 'Invalid API Endpoint.' );
					return $result;
				} else if ( !empty( $response->body ) && is_array( $response->body ) ) {
					foreach ( $response->body as $user ) {
						if ( $user->email == $email ) {
							$result = array( 'result' => 'success', 'body' => $user );
							return $result;
						}
					}
				} else {
					break;
				}
				$page++;
			}
			$result = array( 'result' => 'error', 'message' => 'No user found.' );
			return $result;
		}
	}
}
