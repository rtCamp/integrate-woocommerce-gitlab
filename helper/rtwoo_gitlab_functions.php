<?php

/**
 * rtWooGitlab Functions
 *
 * Helper functions for rtwoo-gitlab
 *
 * @author udit
 */

function rtWooGLMail($subject, $message) {
	wp_mail('faishal.saiyed@rtcamp.com', $subject, $message);
}
?>