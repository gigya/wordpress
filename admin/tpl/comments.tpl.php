<?php
/**
 * Gigya comments anchor.
 */
$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
if ( empty ( $data ) ) {
	// Get the data from the argument.
	require_once GIGYA__PLUGIN_DIR . 'features/comments/GigyaCommentsSet.php';
	$comments = new GigyaCommentsSet();
	$data     = $comments->getParams();
}
?>
<?php if ( $comments_options['position'] !== 'none' ) : ?>
	<?php if ( ! empty( $comments_options['rating'] ) ) : ?>
		<div class="gigya-rating-widget"></div>
	<?php endif ?>
	<div class="gigya-comments-widget"></div>
	<script class="data-comments" type="application/json"><?php echo json_encode( $data ) ?></script>
<?php endif ?>