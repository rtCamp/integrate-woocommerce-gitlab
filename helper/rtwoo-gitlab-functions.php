<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

function rtwoogl_backtrace( $function = '', $class = '', $file = '' ) {
	$backtrace = debug_backtrace();
	foreach ( $backtrace as $trace ) {
		if ( !empty( $function ) ) {
			if ( isset( $trace['function'] ) && $trace['function'] == $function ) {
				if ( !empty( $class ) ) {
					if ( isset( $trace['class'] ) && $trace['class'] == $class ) {
						if ( !empty( $file ) ) {
							if ( isset( $trace['file'] ) && $trace['file'] == $file ) {
								return true;
							}
						} else {
							return true;
						}
					}
				} else {
					return true;
				}
			}
		}
	}
	return false;
}

function rtwoogl_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}
	$located = rtwoogl_locate_template( $template_name, $template_path, $default_path );
	do_action( 'rtwoogl_before_template_part', $template_name, $template_path, $located, $args );
	include( $located );
	do_action( 'rtwoogl_after_template_part', $template_name, $template_path, $located, $args );
}

function rtwoogl_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	global $rtWooGitlab;
	if ( !$template_path ) {
		$template_path = $rtWooGitlab->templateURL;
	}
	if ( !$default_path ) {
		$default_path = RT_WOO_GL_PATH_TEMPLATES;
	}
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);
	if ( !$template ) {
		$template = $default_path . $template_name;
	}
	return apply_filters( 'rtwoogl_locate_template', $template, $template_name, $template_path );
}