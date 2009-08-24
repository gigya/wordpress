<?php
$user = wp_get_current_user();
$thumbnail = $this->getUserThumbnail( $user->ID );
if( !empty( $thumbnail ) ) {
	echo '<img id="gigya-socialize-profile-image" src="' . $thumbnail . '" alt="User Avatar" />';
	echo "<p id='gigya-socialize-display-name'>{$user->display_name}</p>";
} else {
	print '<p><strong>';
	printf( __( 'Hi, %s' ), $user->display_name );
	print '</strong></p>';
}
?>
<a id="gigya-socialize-profile-link" href="<?php echo site_url( 'wp-admin/profile.php' )?>"><?php _e( 'Edit profile' ); ?></a>
<br style="clear: right;" />
<a id="gigya-socialize-logout-link" href="<?php echo wp_nonce_url( site_url( 'wp-login.php?action=logout' ), 'log-out' ); ?>"><?php  _e( 'Logout' ); ?></a>
<br style="clear: left;" />
<?php
if( in_array( $this->usersLoginProvider(), $this->network->updateStatusValidNetworks ) ) {
	?>
	<form method="post" action="" id="gigya-socialize-update">
	<input type="text" name="gigya-socialize-update-text" id="gigya-socialize-update-text" value="<?php _e( 'What are you doing now?' ); ?>" />
	<input type="submit" name="gigya-socialize-update-submit" id="gigya-socialize-update-submit" value="<?php _e( 'Update' ); ?>" />
	<input type="hidden" name="gigya-socialize-update-provider" id="gigya-socialize-update-provider" value="<?php echo $this->usersLoginProvider(); ?>" />
	<input type="hidden" name="gigya-socialize-update-via" id="gigya-socialize-update-via" value="<?php echo wp_specialchars( $settings[ 'gs-for-wordpress-status-update-via' ] ); ?>" />
	</form>
	<?php
}
if( in_array( $this->usersLoginProvider(), $this->network->inviteFriendsValidNetworks ) ) {
	echo '<a id="gigya-socialize-invite-friends-link" href="' . site_url( 'wp-admin/profile.php?show-friend-selector=1#invite-your-friends' ) . '">';
	echo empty( $options[ 'invite-friends' ] ) ? __( 'Invite your friends!' ) : $options[ 'invite-friends' ];
	echo '</a>';
}
?>