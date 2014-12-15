<?php
/**
 * Template - Checkbox form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<?php if ($position == '0') {
	echo '<h3>Select below which <a target="_blank" href=http://codex.wordpress.org/Roles_and_Capabilities#Roles>Roles</a> should be permitted to login via the default WordPress login UI in /wp-login.php </h3>';
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