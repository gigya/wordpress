<?php
	if(!current_user_can(GIGYA_PERMISSION_LEVEL))
		wp_die(__('Cheatin&#8217; uh?'));
	
	$helpUrl = 'options-general.php?page=gigya-socialize-for-wordpress/gigya.php&help=1';
		
	if(isset($_GET["help"])) {
		include 'help.php';
		exit;
	}	
		
	$api_key = gigya_get_option("api_key");
	$secret_key = gigya_get_option("secret_key");
	$post_login_redirect = gigya_get_option("post_login_redirect");
	$login_ui = gigya_get_option("login_ui");
	$force_email = gigya_get_option("force_email") == 1 ?  1 : 0;
	$account_linking = gigya_get_option("account_linking") == 1 ? 1 : 0 ;
	$share_plugin = gigya_get_option("share_plugin") == 1 ? 1 : 0 ; 
?>  

<input type="hidden" name="wordtour_settings[default_artist]" value="<?php echo $options["default_artist"]?>"></input>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e( 'gigya'); ?></h2>
	
	<?php
	echo sprintf( __( 'You can receive help for many of these settings by clicking <a target="_blank"  href="%1$s">here</a>.' ), $helpUrl );
	?>
	
	<form action="options.php" method="post">
		<?php wp_nonce_field('update-options'); ?>
		<?php settings_fields(GIGYA_SETTINGS_PREFIX);?>
		<h3><?php _e( 'General Settings' ); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="gigya_api_key"><?php _e( 'Gigya Socialize API Key' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $api_key;?>" id="gigya_api_key" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[api_key]">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gigya_secret_key"><?php _e( 'Gigya Socialize Secret Key' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $secret_key;?>" id="gigya_secret_key" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[secret_key]">
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php _e( 'Post Login Redirect' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $post_login_redirect;?>" id="gigya_post_login_redirect" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[post_login_redirect]">
						<br/>
						<span class="description">
						<?php _e( 'If you provide a value here, users will be redirect to this paged after logging in via either the Gigya widget on the login page or the regular login form.  To redirect to '); 
							   echo "your blog home page, enter " . home_url();
							   echo ", your blog admin page, enter " . admin_url();
						?> 
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gigya_force_email"><?php _e( 'Email required for registration' ); ?></label></th>
					<td>
						<input type="checkbox" <?php echo ($force_email ? "checked='true'" : "");?> value="1" id="gigya_force_email" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[force_email]">
						<br/>
						<span class="description">
						<?php _e( 'When enabled, new user registering with a social network that does not provide email (such as Twitter, , Linkedin or others), the user will be prompt with a dialog to complete the registration by providing an email.  Otherwise a temporary email will be generated for the user in-order to complete the registration.'); 
						?> 
						</span>
					</td>
				</tr>
				<tr style="visibility:hidden;">
					<th scope="row"><label for="gigya_account_linking"><?php _e( 'Enable account linking' ); ?></label></th>
					<td>
						<input type="checkbox" checked="true" value="1" id="gigya_faccount_linking" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[account_linking]">
						<br/>
						<span class="description">
						<?php _e( ''); 
						?> 
						</span>
					
					</td>
				</tr>
			</tbody>
			</table>
			<br>
			<h3><?php _e( 'Sharing' ); ?></h3>
			<table  class="form-table" >
			<tbody>
				<tr>	
					<th scope="row"><label for="gigya_Share_Btn"><?php _e( 'Enable Gigya Share Button' ); ?></label></th>
					<td scope="row">
						<input type="checkbox" <?php echo ($share_plugin ? "checked='true'" : "");?> value="1" id="gigya_share_plugin" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[share_plugin]">					
						<br/>
						<span class="description">
						<?php _e( 'The Share plugin makes it easy for your blog readers to syndicate content to Social Network by adding a share button at the end of each post.'); 
						?> 
						</span>
					</td>
				</tr>
				
			</tbody>
			</table>
			<br>
			<h3><?php _e('Appearance'); ?></h3>
			<table>
				<tr>
					<th scope="row" valign="top"><label><?php _e( 'Sign In Component - Advanced Use Only' ); ?></label></th>
					<td>
						<textarea rows="10" class="large-text" id="gigya_login_ui" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[login_ui]"><?php echo $login_ui;?></textarea>
						<br/>
						<span class="description">
						To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see <a target="_blank" href="<?php echo $helpUrl;?>#login-ui">here</a> for help on what to put in the text area.
						</span>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" value="<?php _e('Save Changes') ?>" class="button-primary" name="Submit">
		</p>
  	</form>
</div>
	
	
	
	
  