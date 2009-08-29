<?php
function gigya_socialize_widget() {
	global $gigyaSocialize;
	$gigyaSocialize->widgetOutput(array());
}

function gigya_socialize_comment_widget() {
	global $gigyaSocialize;
	$gigyaSocialize->includeCommentFormExtra();
}
?>