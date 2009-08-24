<?php 
$user = wp_get_current_user();
if ($this->user->hasThumbnailUrl($user->ID)) {
    $thumbnail = $this->user->getThumbnailUrl($user->ID);
    echo '<img id="gigya-socialize-profile-image" src="'.$thumbnail.'" alt="User Avatar" />';
    echo "<p id='gigya-socialize-display-name'>{$user->display_name}</p>";
} else {
    print '<p><strong>';
    printf(__('Hi, %s'), $user->display_name);
    print '</strong></p>';
}
?>
<a id="gigya-socialize-profile-link" href="<?php echo site_url( 'wp-admin/profile.php' )?>"><?php _e('Edit profile'); ?></a>
<br style="clear: right;"/>
<a id="gigya-socialize-logout-link" href="<?php echo wp_nonce_url( site_url( 'wp-login.php?action=logout' ), 'log-out' ); ?>"><?php _e('Logout'); ?></a>
<br style="clear: left;"/>
<?php 
if( $this->network->canUpdateStatus($this->user->getCurrentUserLoginProviders() ) ) {
?>
<form method="post" action="" id="gigya-socialize-update">
    <input type="text" name="gigya-socialize-update-text" id="gigya-socialize-update-text" value="<?php _e( 'What are you doing now?' ); ?>" /><input type="submit" name="gigya-socialize-update-submit" id="gigya-socialize-update-submit" value="<?php _e( 'Update' ); ?>" /><input type="hidden" name="gigya-socialize-update-provider" id="gigya-socialize-update-provider" value="<?php echo array_pop( $this->user->getCurrentUserLoginProviders() ); ?>" /><input type="hidden" name="gigya-socialize-update-via" id="gigya-socialize-update-via" value="<?php echo wp_specialchars( $settings[ 'gs-for-wordpress-status-update-via' ] ); ?>" />
</form>
<?php 
}

if ($this->network->canInviteFriends($this->user->getCurrentUserLoginProviders() ) ) {
    echo '<a id="gigya-socialize-invite-friends-link" href="'.site_url('wp-admin/profile.php?show-friend-selector=1#invite-your-friends').'">';
    echo empty($options['invite-friends']) ? __('Invite your friends!') : $options['invite-friends'];
    echo '</a>';
}
?>
