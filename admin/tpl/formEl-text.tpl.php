<?php
/**
 * Template - Text-field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="row text-field <?php echo (isset($class)) ? $class : ''; ?>">
	<label for="gigya_<?php echo $id; ?>"><?php echo (isset($label)) ? $label : ''; ?></label>
	<input type="text" size="<?php echo (isset($size)) ? (string)$size : '60'; ?>" class="<?php echo (isset($subclass)) ? $subclass : 'input-xlarge'; ?>" style="<?php echo (isset($style)) ? $style : ''; ?>" value="<?php echo $value; ?>" id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>" />
	<?php
		if ( isset($markup) ):
			echo $markup;
		endif;
	?>
	<?php if ( ! empty( $desc ) ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>