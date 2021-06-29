<?php
/**
 * Template - Checkbox form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row checkbox <?php echo ( isset( $var['class'] ) ) ? $var['class'] : '' ?>">
	<label for="gigya_<?php echo $var['id']; ?>">
		<input type="hidden" value="0" name="<?php echo $var['name'] ?>"/>
		<input type="checkbox" <?php checked( "1", $var['value'] ); ?> value="1" id="gigya_<?php echo $var['id']; ?>"
			   name="<?php echo $var['name'] ?>"/>
		<?php echo $var['label']; ?>
	</label>
	<?php if ( ! empty( $var['desc'] ) ): ?>
		<small><?php echo $var['desc']; ?></small>
	<?php endif; ?>
</div>