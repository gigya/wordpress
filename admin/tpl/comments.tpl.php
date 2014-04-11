<?php
/**
 * Gigya comments anchor.
 */
$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
?>
<?php if ( empty( $comments_options['hide'] ) ) : ?>
	<?php if ( ! empty( $comments_options['rating'] ) ) : ?>
		<div class="gigya-rating-widget"></div>
	<?php endif ?>
	<div class="gigya-comments-widget"></div>
<?php endif ?>