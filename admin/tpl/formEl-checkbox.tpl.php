<?php
/**
 * Template - Checkbox form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="row checkbox <?php echo $class ?>">
	<label for="gigya_<?php echo $id; ?>">
		<input type="hidden" value="0" id="gigya_<?php echo $id; ?>" name="<?php echo $section ?>[<?php echo $id; ?>]" />
		<input type="checkbox" <?php echo( $value || $value == "1" ? "checked" : "" ); ?> value="1" id="gigya_<?php echo $id; ?>" name="<?php echo $section ?>[<?php echo $id; ?>]" />
		<?php echo $label; ?>
	</label>
	<?php if ( $desc ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>