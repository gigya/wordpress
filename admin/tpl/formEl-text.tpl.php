<?php
/**
 * Template - Text-field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="row text-field <?php echo $class ?>">
	<label for="gigya_<?php echo $id; ?>"><?php echo $label; ?></label>
	<input type="text" size="60" class="input-xlarge" value="<?php echo $value; ?>" id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>" />
	<?php if ( ! empty( $desc ) ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>