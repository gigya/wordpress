<?php 
// Widget display arguments are already defined here... so are $settings and $options (the plugin's widget options as configured by the blog admin)
?>
<?php echo $before_widget; ?>
<div id="gigya-socialize-widget-content" style="display: none;"><!-- Hide in case the user doesn't have JavaScript enabled. -->
<?php 
if( is_user_logged_in() && $this->userHasGigyaConnection() ) {
	include( 'widget-connected.php' );
} elseif( !is_user_logged_in() ) {
	include( 'widget-not-logged-in.php' );
} else {
	include( 'widget-not-connected.php' );
}
?>			
</div>
<script type="text/javascript">
if( typeof( jQuery ) != 'undefined' && typeof( gigya ) != 'undefined' ) {
	jQuery(document).ready(function() { jQuery('#gigya-socialize-widget-content').show(); });
}
</script>
<?php echo $after_widget; ?>