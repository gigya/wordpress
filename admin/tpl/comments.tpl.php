<?php
/**
 * Gigya comments anchor.
 */
$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
?>
<?php if ( empty( $comments_options['comments_hide'] ) ) : ?>
	<?php if ( ! empty( $comments_options['comments_rating'] ) ) : ?>
		<div class="gigya-rating-widget"></div>
	<?php endif ?>
	<div class="gigya-comments-widget"></div>
<?php endif ?>