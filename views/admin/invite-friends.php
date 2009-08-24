<?php 
if( $this->user->hasGigyaConnection() && $this->network->canInviteFriends($this->user->getCurrentUserLoginProviders()) ) {
	$settings = $this->data->getSettings();
?>
<div style="display: none;" id="invite-friends-container">
<h3 id="invite-your-friends" name="invite-your-friends"><?php _e( 'Invite Friends' ); ?></h3>
<p><?php echo sprintf( __( 'Invite your friends to join you on this website.  Click <a id="gs-friends-selector-toggle" href="%1$s">here</a>.' ), '#' ); ?>
<div id="gs-friends-selector"></div>
<table id="invite-friends-message" class="form-table">
	<tbody>
		<tr valign="top"><th colspan="2" style="text-align: center;"><?php _e( 'Message to Send' ); ?></th></tr>
		<tr valign="top">
			<th scope="row"><label for="invite-friends-subject"><?php _e( 'Subject' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" name="invite-friends-subject" id="invite-friends-subject" value="<?php echo attribute_escape( $settings[ 'gs-for-wordpress-friend-notification-title'] ); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="invite-friends-body"><?php _e( 'Message' ); ?></label></th>
			<td>
				<textarea rows="3" cols="30" name="invite-friends-body" id="invite-friends-body"><?php echo htmlentities( $settings[ 'gs-for-wordpress-friend-notification-content' ] ); ?></textarea><br />
				<a href="#" class="button-secondary" name="invite-friends-send" id="invite-friends-send"><?php _e( 'Send Invite!' ); ?></a>
			</td>
		</tr>
	</tbody>
</table>
<table id="invite-friends-message-sent" class="form-table">
	<tbody>
		<tr>
			<th colspan="2" style="text-align:center;"><strong><?php _e( 'Message Sent!' ); ?></strong></th>
		</tr>
	</tbody>
</table>
</div>
<script type="text/javascript">
var collectionOfFriends = null;
var friendsParams;
var sendParams;
if( typeof( jQuery ) != 'undefined' && typeof( gigya ) != 'undefined' ) {
	jQuery(document).ready(function() {
		jQuery('#pass1').parents('tr').remove();
		jQuery('#invite-friends-container').show();
		jQuery('#invite-friends-message,#invite-friends-message-sent').slideUp(1);
		jQuery('#gs-friends-selector-toggle').click(function(event) {
			event.preventDefault();
			friendsParams = {
				containerID: 'gs-friends-selector',
				onSelectionDone: function( eventObj ) {
					collectionOfFriends = eventObj.friends;
					jQuery('#gs-friends-selector').slideUp('medium', function() { jQuery(this).empty().attr('style','').show(); });
					jQuery('#invite-friends-message').slideDown();
				}
				<?php echo $this->getFriendSelectorComponentCode(); ?>
			};
			gigya.services.socialize.showFriendSelectorUI(gigyaSocializeGeneralConfiguration,friendsParams);
		});
		jQuery('#invite-friends-send').click(function(event) {
			event.preventDefault();
			if( collectionOfFriends != null ) {
				sendParams = {
					recipients: collectionOfFriends,
					subject: jQuery('#invite-friends-subject').val(),
					body: jQuery('#invite-friends-body').val()
				};
				gigya.services.socialize.sendNotification(gsConf,sendParams);
			}
			jQuery('#invite-friends-message').slideUp('medium',function() { 
				jQuery('#invite-friends-message-sent').slideDown();
				setTimeout("jQuery('#invite-friends-message-sent').slideUp();", 5000);
			});
		});
		<?php if( $_GET[ 'show-friend-selector' ] == 1 ) { ?> 
		jQuery('#gs-friends-selector-toggle').click();
		<?php } ?>
	});
}
</script>
<?php 
}
?>