/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * rtWooGitlab Admin JavaScripts
 */

jQuery(document).ready(function($) {

	if( $('#rtwoogl_test_connection').length > 0 ) {
		$('#rtwoogl_test_connection').click(function(e) {
			e.preventDefault();
			var that = this;
			jQuery(that).next().remove();
			jQuery(that).parent().append('<span style="padding-left: 10px;"><img class="tmp-process" src="'+ rtwoogl_loading_file +  '" /></span>');
			jQuery.post(adminAjaxURL, {
				action: 'rtwoogl_test_connection',
				endPoint: jQuery('#rtwoogl_api_endpoint').val().trim(),
				token: jQuery('#rtwoogl_private_token').val().trim()
			}, function(data, status, xhr) {
				jQuery(that).next().remove();
				data = jQuery.parseJSON(data);
				if(data.message !== 'undefined')
					jQuery(that).parent().append('<span style="padding-left: 10px;">'+data.message+'</span>');
			});
		});
	}

	$('#rtwoogl_api_endpoint').attr( 'placeholder', 'e.g., http://gitlab.example.com/api/v3/' );
	$('#rtwoogl_private_token').attr( 'placeholder', 'GitLab Private Token' );
	$('#rtwoogl_forgot_password_link').attr( 'placeholder', 'http://gitlab.example.com/forgot_password' );
	$('#rtwoogl_admin_email').attr( 'placeholder', 'admin@example.com' );
});