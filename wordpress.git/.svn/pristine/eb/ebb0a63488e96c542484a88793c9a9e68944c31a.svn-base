<?php
/**
 * Gigya comments anchor.
 */
$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
$params = array(
		'categoryID' => $comments_options['categoryID'],
		'streamID'   => get_the_ID(),
		'dataFormat' => 'html'
);
$gigya = new GigyaCMS;
$html = $gigya->call( 'comments.getComments', $params );
?>
<div class="gigya-comments-spider">
	<?php echo $html['comments']; ?>
</div>