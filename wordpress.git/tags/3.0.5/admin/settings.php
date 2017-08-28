<?php
	if(!current_user_can(GIGYA_PERMISSION_LEVEL))
		wp_die(__('Cheatin&#8217; uh?'));
	
	$helpUrl = 'http://developers.gigya.com/';
		
	if(isset($_GET["help"])) {
		include 'help.php';
		exit;
	}	
		
	$api_key = gigya_get_option("api_key");
	$secret_key = gigya_get_option("secret_key");
	$lang = gigya_get_option("lang");
	if(empty($lang)) $lang = "en"; 
	$post_login_redirect = gigya_get_option("post_login_redirect");
	$login_ui = gigya_get_option("login_ui");
	$force_email = gigya_get_option("force_email") == 1 ?  1 : 0;
	$account_linking = gigya_get_option("account_linking") == 1 ? 1 : 0 ;
	$share_plugin = gigya_get_option("share_plugin");
	$comments_plugin = gigya_get_option("comments_plugin") == 1 ? 1 : 0 ;
	$gigya_comments_cat_id = gigya_get_option("gigya_comments_cat_id");
	$login_plugin = gigya_get_option("login_plugin") == 1 ? 1 : 0 ; 
	$providers = gigya_get_option("share_providers"); 
	$loginProviders = gigya_get_option("login_providers");
	$load_jquery = gigya_get_option("load_jquery");
	$global_params = gigya_get_option("global_params");
	
	
?>  

<input type="hidden" name="wordtour_settings[default_artist]" value="<?php echo $options["default_artist"]?>"></input>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e( 'gigya'); ?></h2>
	
	<?php
	echo sprintf( __( 'To learn more about gigya & how setup an account, please visit our developer documentation <a target="_blank"  href="%1$s">here</a>.' ), $helpUrl );
	?>
	
	<form action="options.php" method="post">
		<?php wp_nonce_field('update-options'); ?>
		<?php settings_fields(GIGYA_SETTINGS_PREFIX);?>
		<input type="hidden" value="1" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[login_plugin_startup]">
		<input type="hidden" value="1" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[share_providers_startup]">
		<h3><?php _e( 'General Settings' ); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<td><?php _e( 'Version' ); ?></td>
					<td><?php echo GIGYA_VERSION;?></td>
				</tr>
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
					<th scope="row"><label for="gigya_lang"><?php _e( 'Language' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $lang;?>" id="gigya_lang" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[lang]">
						<span class="description">
							en (default),zh-cn,zh-hk,zh-tw,cs,da,nl,fi,fr,de,el,hu,it,ja,ko,no,pl,pt,pt-br,ru,es,es-mx,sv,tl
							<p>
							If not defined, English will be the default language. For the complete list of supported languages, go the <a href="http://developers.gigya.com/020_Client_API/010_Objects/Conf_object" target="_blank">gigya documentation</a>
							</p>
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gigya_global_params"><?php _e( 'Global Parameters' ); ?></label></th>
					<td>
						<textarea rows="6" class="large-text" id="gigya_global_params" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[global_params]"><?php echo $global_params;?></textarea>
					</td>
				</tr>
				
				
				
				<tr>
					<th scope="row"><label><?php _e( 'Post Login Redirect' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $post_login_redirect;?>" id="gigya_post_login_redirect" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[post_login_redirect]">
						<br/>
						<span class="description">
						<?php _e( 'Provide a URL to redirect users after they logged-in via Gigya social login.'); ?> 
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gigya_force_email"><?php _e( 'Email required for registration' ); ?></label></th>
					<td>
						<input type="checkbox" <?php echo ($force_email ? "checked='true'" : "");?> value="1" id="gigya_force_email" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[force_email]">
						<br/>
						<span class="description">
						<?php _e( 'When enabled, new user registering with a social network which does not provide a user email (such as Twitter, Linkedin or others) will be required to provide an Email to complete his registration process to the site. Otherwise a temporary email will be generated for the user in-order to complete the registration.'); 
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
				<tr>
					<th scope="row"><label for="gigya_login_providers"><?php _e( 'Login Providers' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $loginProviders;?>" id="gigya_login_providers" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[login_providers]">
						<p class="description">Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin</p>
					</td>
				</tr>
			</tbody>
			</table>
			<br>
			<h3><?php _e( 'Login' ); ?></h3>
			<table  class="form-table" >
			<tbody>
				<tr>	
					<th scope="row"><label for="gigya_Share_Btn"><?php _e( 'Enable Gigya Login' ); ?></label></th>
					<td scope="row">
						<input type="checkbox" <?php echo ($login_plugin ? "checked='true'" : "");?> value="1" id="gigya_login_plugin" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[login_plugin]"/>					
						<br/>
						<span class="description">
						<?php _e('The Plugin displays all the available providers\' logos as login options, enabling the user to login to your site via his social network / webmail account.'); ?> 
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
						<select id="gigya_share_plugin" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[share_plugin]">
							<option <?php if($share_plugin==1) echo "selected='true'";?> value="1">None</option>
							<option <?php if(empty($share_plugin)) echo "selected='true'";?> value="">Bottom</option>
							<option <?php if($share_plugin==2) echo "selected='true'";?> value="2">Top</option>
							<option <?php if($share_plugin==3) echo "selected='true'";?> value="3">Both</option>
						</select>
											
						<br/>
						<span class="description">
						<?php _e( 'The Share plugin makes it easy for your blog readers to syndicate content to Social Network by adding a share buttons to blog posts.'); 
						?> 
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gigya_share_providers"><?php _e( 'Share Providers' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $providers;?>" id="gigya_share_providers" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[share_providers]">
						<p>for example: facebook-like,google-plusone,share,twitter,email</p>
					</td>
				</tr>
				
			</tbody>
			</table>
			<br>
			<h3><?php _e( 'Comments' ); ?></h3>
			<table  class="form-table" >
			<tbody>
				<tr>	
					<th scope="row"><label for="gigya_comments_plugin"><?php _e( 'Enable Gigya Comments' ); ?></label></th>
					<td scope="row">
						<input type="checkbox" <?php echo ($comments_plugin ? "checked='true'" : "");?> value="1" id="gigya_comments_plugin" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[comments_plugin]">					
						<br/>
						<span class="description">
						<?php _e( 'Gigya\'s Comments Plugin enables site users to post comments and have discussions about published content on the site.'); 
						?> 
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gigya_comments_cat_id"><?php _e( 'Comments Category ID' ); ?></label></th>
					<td>
						<input type="text" class="large-text" value="<?php echo $gigya_comments_cat_id;?>" id="gigya_comments_cat_id" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[gigya_comments_cat_id]">
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
						To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see <a target="_blank" href=" http://developers.gigya.com/050_CMS_Modules/030_Wordpress_Plugin">here</a> for help on what to put in the text area.
						</span>
					</td>
				</tr>
			</tbody>
			</table>
			<br>
			<h3><?php _e('Scripts'); ?></h3>
			<table>
				<tr>
					<th scope="row" valign="top"><label><?php _e( 'Update jQuery' ); ?></label></th>
					<td>
						<select id="gigya_load_jquery" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[load_jquery]">
							<option value="">No</option>
							<option <?php if($load_jquery == "1") echo 'selected="true"';?> value="1">Load From Site</option>
							<option <?php if($load_jquery == "2") echo 'selected="true"';?> value="2">Load From Google CDN</option>
						</selev>
						<span class="description">
						Load lastest version of jQuery & jQuery UI
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
	
	
	
	
  