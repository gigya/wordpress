<?php $settings = $this->data->getSettings(); ?>
<div class="wrap">
	<h2><?php _e( 'Gigya Socialize' ); ?></h2>
	<?php
	$helpUrl = 'options-general.php?page=gigya-socialize&amp;help=1';
	echo sprintf( __( 'You can receive help for many of these settings by clicking <a target="_blank"  href="%1$s">here</a>.' ), $helpUrl );
	?>
	<form method="post">
	<h3><?php _e( 'General Settings' ); ?></h3>
	<a target="_blank"  href="<?php echo $helpUrl; ?>#general"><?php _e( 'Get Help' ); ?></a>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-api-key"><?php _e( 'Gigya Socialize API Key' ); ?></label></th>
				<td>
					<input type="text" style="width:95%" class="regular-text" id="gs-for-wordpress-api-key" name="gs-for-wordpress-api-key" value="<?php echo attribute_escape( $settings[ 'gs-for-wordpress-api-key' ] ); ?>" />
				</td>
			</tr>
				<th scope="row"><label for="gs-for-wordpress-secret-key"><?php _e( 'Gigya Socialize Secret Key' ); ?></label></th>
				<td>
					<input type="text" style="width:95%" class="regular-text" id="gs-for-wordpress-secret-key" name="gs-for-wordpress-secret-key" value="<?php echo attribute_escape( $settings[ 'gs-for-wordpress-secret-key' ] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-post-login-redirect"><?php _e( 'Post Login Redirect' ); ?></label></th>
				<td>
					<input type="text" style="width:95%" class="regular-text" id="gs-for-wordpress-post-login-redirect" name="gs-for-wordpress-post-login-redirect" value="<?php echo attribute_escape( $settings[ 'gs-for-wordpress-post-login-redirect' ] ); ?>" /><br />
					<?php _e( 'If you provide a value here, users will be redirect to this paged after logging in via either the Gigya widget on the login page or the regular login form.  To redirect to your blog home page, enter ' . site_url() ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Comment Extras' ); ?></th>
				<td>
					<label for="gs-for-wordpress-comment-extras"><input id="gs-for-wordpress-comment-extras" type="checkbox" name="gs-for-wordpress-comment-extras" value="1" <?php checked( true, $settings[ 'gs-for-wordpress-comment-extras'] ); ?> /> <?php _e( 'Enable commenting feature.' ); ?></label>
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php _e( 'Notification Settings' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-friend-status-update-via"><?php _e( 'Status Update Link' ); ?></label></th>
				<td>
					<input type="text" style="width:95%" class="regular-text" id="gis-for-wordpress-status-update-via" name="gs-for-wordpress-status-update-via" value="<?php echo attribute_escape( $settings[ 'gs-for-wordpress-status-update-via' ] ); ?>" /><br />
					<?php _e( 'If you provide a value here, the link will be appended to all status updates users make from the Gigya widget on your site. For example: <code>Test Status Update</code> becomes <code>Test Status Update via http://example.com</code>' ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-friend-notification-title"><?php _e( 'Default Friend Invite Title' ); ?></label></th>
				<td>
					<input type="text" style="width:95%" class="regular-text" id="gs-for-wordpress-friend-notification-title" name="gs-for-wordpress-friend-notification-title" value="<?php echo attribute_escape( $settings[ 'gs-for-wordpress-friend-notification-title' ] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-friend-notification-content"><?php _e( 'Default Friend Invite Content' ); ?></label></th>
				<td>
					<textarea style="width: 95%;" class="large-text" id="gs-for-wordpress-friend-notification-content" name="gs-for-wordpress-friend-notification-content"><?php echo htmlentities( $settings[ 'gs-for-wordpress-friend-notification-content' ] ); ?></textarea>
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php _e( 'Appearance' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-sign-in-component-ui"><?php _e( 'Sign In Component' ); ?></label> &mdash; <em>Advanced Use Only</em></th>
				<td>
					<textarea style="width: 95%;" class="large-text" id="gs-for-wordpress-sign-in-component-ui" rows="10" name="gs-for-wordpress-sign-in-component-ui"><?php echo htmlentities( $settings[ 'gs-for-wordpress-sign-in-component-ui' ] ); ?></textarea>
					<p><?php echo sprintf( __( 'To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see <a target="_blank"  href="%1$s">here</a> for help on what to put in the text area.' ), "{$helpUrl}#login-ui" ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-widget-sign-in-component-ui"><?php _e( 'Widget Sign In Component' ); ?></label> &mdash; <em>Advanced Use Only</em></th>
				<td>
					<textarea style="width: 95%;" class="large-text" id="gs-for-wordpress-widget-sign-in-component-ui" rows="10" name="gs-for-wordpress-widget-sign-in-component-ui"><?php echo htmlentities( $settings[ 'gs-for-wordpress-widget-sign-in-component-ui' ] ); ?></textarea>
					<p><?php echo sprintf( __( 'To customize the look of the sign in component for the widget provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see <a target="_blank"  href="%1$s">here</a> for help on what to put in the text area.' ), "{$helpUrl}#login-ui" ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-comments-sign-in-component-ui"><?php _e( 'Comment Sign In Component' ); ?></label> &mdash; <em>Advanced Use Only</em></th>
				<td>
					<textarea style="width: 95%;" class="large-text" id="gs-for-wordpress-comments-sign-in-component-ui" rows="10" name="gs-for-wordpress-comments-sign-in-component-ui"><?php echo htmlentities( $settings[ 'gs-for-wordpress-comments-sign-in-component-ui' ] ); ?></textarea>
					<p><?php echo sprintf( __( 'To customize the look of the sign in component for the comment widget provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see <a target="_blank"  href="%1$s">here</a> for help on what to put in the text area.' ), "{$helpUrl}#login-ui" ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="gs-for-wordpress-friend-selector-component-ui"><?php _e( 'Friend Selector Component' ); ?></label> &mdash; <em>Advanced Use Only</em></th>
				<td>
					<textarea style="width: 95%;" class="large-text" id="gs-for-wordpress-friend-selector-component-ui" rows="10" name="gs-for-wordpress-friend-selector-component-ui"><?php echo htmlentities( $settings[ 'gs-for-wordpress-friend-selector-component-ui' ] ); ?></textarea>
					<p><?php echo sprintf( __( 'To customize the look of the friend selector component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used.  Please see <a target="_blank"  href="%1$s">here</a> for help on what to put in the text area.' ), "{$helpUrl}#friend-selection-ui" ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

	<p class="submit">
		<?php wp_nonce_field( 'save-gs-for-wordpress-settings' ); ?>
		<input type="submit" name="save-gs-for-wordpress-settings" id="save-gs-for-wordpress-settings" value="<?php _e( 'Save Settings' ); ?>" />
	</p>
	</form>
</div>