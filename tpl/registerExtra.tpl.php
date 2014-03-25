<?php
/**
 *
 */
?>
<form name="registerform" class="gigya-register-form" id="register-extra-form" action="<?php echo esc_url(site_url('wp-login.php?action=register', 'login_post')); ?>" method="post">
	<div class="error-message"></div>
	<label for="user_login"><?php _e('Username') ?><br/>
	<input type="text" name="user_login" id="user_login" class="input" value="<?php echo $user->nickname ?>" size="20"/></label>

	<label for="user_email"><?php _e('E-mail') ?><br/>
	<input type="text" name="user_email" id="user_email" class="input" value="<?php echo $user->$email; ?>" size="25"/></label>

	<?php do_action('register_form'); ?>

	<input type="submit" name="wp-submit" id="gigya-ajax-submit" class="button button-primary button-large" value="<?php esc_attr_e('Register'); ?>"/>
</form>