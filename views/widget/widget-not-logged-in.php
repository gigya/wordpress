<?php 
$title = empty( $options[ 'title' ] ) ? __( 'Join the Community' ) : $options[ 'title' ];
echo $before_title . $title . $after_title;
?>
<p id="gs-for-wordpress-login-container">
	<?php echo empty( $options[ 'wordpress-header' ] ) ? __( 'Already a member?' ) : $options[ 'wordpress-header' ]; ?><br />
	<a id="gs-for-wordpress-login" href="<?php echo site_url('wp-login.php', 'login'); ?>"><?php _e( 'Login' ); ?></a>
</p>
<div class="gs-for-wordpress-login-widget" id="componentDiv"></div>
<a id="gs-for-wordpress-redirect-url" style="display: none;" href="<?php echo add_query_arg(array()); ?>"></a>
<script type='text/javascript'>
	jQuery(document).ready(function() { 
		<?php echo $this->getWidgetLoginUIComponentCode(); ?>
		if( typeof( gigya ) != 'undefined' ) {
			gigya.services.socialize.showLoginUI(conf, login_params);
			gigya.services.socialize.addEventHandlers(conf,{onLogin:processLogin});
		}
	});
</script>