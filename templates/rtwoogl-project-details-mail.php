<?php

/**
 * rtwoogl-project-details-mail Template
 *
 *
 * @author udit
 */
?>
<h2><?php _e( 'Source-code Access', 'rtwoo-gitlab' ); ?></h2>
<span><?php _e( '**You can directly access the source-code via GitLab.', 'rtwoo-gitlab' ); ?></span>
<p><strong><?php _e( 'Username:', 'rtwoo-gitlab' ); ?></strong> <?php echo esc_attr( $rtWooGLUser->username ); ?></p>

<?php // Check whether new user or existing user
if ( $rtWooGLUserStatus == 'new' ) { ?>
	<p><strong><?php _e( 'Password:', 'rtwoo-gitlab' ); ?></strong> <?php echo esc_attr( $password ); ?></p>
<?php } else if ( $rtWooGLUserStatus == 'old' ) { ?>
	<p><strong><?php _e( 'Password:', 'rtwoo-gitlab' ); ?></strong> <?php _e( 'Same as before. If forgotten; please click here:' ); ?> <a href="<?php echo esc_url( $rtWooGLForgotPasswordLink ); ?>"><?php _e( 'Forgot Password ?', 'rtwoo-gitlab' ); ?></a></p>
<?php } ?>

<p><strong><?php _e( 'Project URLs:', 'rtwoo-gitlab' ); ?></strong><br />
<?php
	foreach ( $order->get_items() as $product ) {
		$project_id = get_post_meta( $product['product_id'], '_rtwoogl_project', true );
		if ( empty( $project_id ) ) {
			continue;
		}

		$projectDetails = $rtGitlabClient->get_project_details( $project_id );
		echo esc_attr( $projectDetails->name_with_namespace.' : '.$projectDetails->web_url ).'<br />';
	}
?>
</p>