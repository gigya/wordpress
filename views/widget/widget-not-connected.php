<?php 
$title = __( 'Connect your account' );
echo $before_title . $title . $after_title;
?>
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