<?php
/**
 * Log page template.
 */
?>
<div class="header">
	<h1>Gigya Log</h1>
	<h4>Top entry is the last entry!</h4>
</div>
<div class="gigya-log-entries">
	<?php echo implode( "<br><hr>", $log ); ?>
	<?php //json_encode( get_option( 'gigya_log' ), JSON_PRETTY_PRINT ); ?>
</div>