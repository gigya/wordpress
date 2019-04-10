<?php
/**
 * Template - Checkbox form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row checkbox <?php echo (isset($class)) ? $class : '' ?>">
	<label for="gigya_<?php echo $id; ?>">
		<input type="hidden" value="0" name="<?php echo $name ?>" />
		<input type="checkbox" <?php checked( "1", $value ); ?> value="1" id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>" />
		<?php echo $label; ?>
	</label>
	<?php if ( !empty($desc) ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>