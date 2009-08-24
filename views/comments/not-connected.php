<div id="gigya-socialize-comments">
	<h2 id="comment-options-header"><?php _e( 'Commenting Options' ); ?></h2>
	<p>
		<?php _e( 'Enter your personal information, or sign in with your social network account below.' ); ?>
	</p>
	<div id="gigya-comment-social-network-area"></div>
	<script type='text/javascript'>
	jQuery(document).ready(function() {
		<?php echo $this->getCommentsExtraUIComponentCode(); ?>
		if( typeof( gigya ) != 'undefined' ) {
			gigya.services.socialize.showLoginUI(comment_conf, comment_params);
			gigya.services.socialize.addEventHandlers(comment_conf,{onLogin:commentLogin});
		}
	});
</script>
	<p>
		<?php _e( sprintf( 'Alternatively you can <a class="external" href="%s">create an avatar</a> that will appear whenever you leave a comment on a Gravatar-enabled blog.', 'http://gravatar.com' ) ); ?>
	</p>
</div>