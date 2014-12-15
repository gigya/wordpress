<?php
/**
 * Template - Checkbox form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<?php if ($position == '0') {
	echo "<h3>Select roles to allow admin access through wordpress login page:</h3>";
	echo "<p>If you have chosen to disable RAAS login in wp-login.php page, you can select the roles that will be allowed to login in that page. all other roles will be denied access at the worpdress login page.</p>";
}
?>
<div class="row checkbox <?php echo $class ?>">
	<label for="gigya_<?php echo $id; ?>">
		<input type="hidden" value="0" name="<?php echo $name ?>" />
		<input type="checkbox" <?php checked( "1", $value ); ?> value="1" id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>" />
		<?php echo $label; ?>
	</label>
	<?php if ( $desc ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>