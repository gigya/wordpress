<div id="gigya-socialize-comments">
	<?php 
	$user = wp_get_current_user();
	if ($this->user->hasThumbnailUrl($user->ID)) {
	    $thumbnail = $this->user->getThumbnailUrl($user->ID);
	    echo '<img id="gigya-socialize-profile-image" src="'.$thumbnail.'" alt="User Avatar" />';
	}
	?>
	<p>
	<?php 
		$loginProviders = $this->user->getCurrentUserLoginProviders();
		if( empty( $loginProviders ) ) {
			$loginProviders[] = __( 'WordPress' );
		}
		
		_e( sprintf( 'Welcome, %s.  You are signed in with your %s account.', $user->display_name, ucfirst(array_pop( $loginProviders ) ) ) );
	?>
	</p>
	<a id="gigya-socialize-logout-link" href="<?php echo wp_nonce_url( site_url( 'wp-login.php?action=logout' ), 'log-out' ); ?>"><?php _e('Click here to sign out.' ); ?></a>
</div>