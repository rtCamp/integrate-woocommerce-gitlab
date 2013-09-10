<?php

/**
 * rtWooGitlab Functions
 *
 * Helper functions for rtwoo-gitlab
 *
 * @author udit
 */

function rtwoogl_mail( $subject, $message ) {
	wp_mail( 'faishal.saiyed@rtcamp.com', $subject, $message, 'Content-Type: text/html' );
}

function rtwoogl_get_query_vars( $post, $keys = array() ) {
	if ( empty( $keys ) ){
		return $post;
	}
	$result = array();
	foreach ( $keys as $key ) {
		if ( isset ( $post[$key] ) ) {
			$result[$key] = $post[$key];
		}
		else {
			$result[$key] = false;
		}
	}
	return $result;
}
