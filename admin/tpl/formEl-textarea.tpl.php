<?php
/**
 * Template - Textarea form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="row textarea <?php echo $class ?>">
	<label for="gigya_<?php echo $id; ?>">
		<?php echo $label; ?>
	</label>
	<textarea rows="5" cols="20" class="large-text" id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>"><?php if ( ! empty( $value ) ) echo $value; ?></textarea>
	<?php if ( $desc ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>