<?php
/**
 * Template - Textarea form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div id="gigya-form-field-<?php echo $id; ?>"
     class="gigya-form-field row textarea <?php echo isset($class) ? $class : ''; ?> <?php echo ( isset( $depends_on ) ) ? 'gigya-depends-on' : ''; ?>"
	<?php echo ( isset( $depends_on ) ) ? 'data-depends-on="' . htmlspecialchars( json_encode( $depends_on ), ENT_QUOTES, 'UTF-8' ) . '"' . ( empty( $display ) ? ' style="display:none;"' : '' ) : ''; ?>
>
	<label for="gigya_<?php echo $id; ?>">
		<?php echo $label; ?>
	</label>
	<textarea rows="5" cols="20" class="large-text"
			  id="gigya_<?php echo $id; ?>"
			  name="<?php echo $name ?>"
			  <?php echo ( isset( $placeholder ) ) ? 'placeholder="' . $placeholder . '"' : ''; ?>
	><?php if ( ! empty( $value ) ) echo $value; ?></textarea>
	<?php if ( isset($desc) and $desc ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>