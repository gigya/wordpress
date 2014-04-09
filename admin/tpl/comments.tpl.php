<?php
/**
 * Gigya comments anchor.
 */
$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
?>
<?php if ( empty( $comments_options['comments_hide'] ) ) : ?>
	<div class="gigya-comments-widget"></div>
<?php endif ?>