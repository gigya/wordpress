<?php
/**
 * Template - Textarea form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div id="gigya-form-field-<?php echo $var['id']; ?>"
	 class="gigya-form-field row textarea <?php echo isset( $var['class'] ) ? $var['class'] : ''; ?> <?php echo ( isset( $var['depends_on'] ) ) ? 'gigya-depends-on' : ''; ?>"
	<?php echo ( isset( $var['depends_on'] ) ) ? 'data-depends-on="' . htmlspecialchars( json_encode( $var['depends_on'] ), ENT_QUOTES, 'UTF-8' ) . '"' . ( empty( $var['display'] ) ? ' style="display:none;"' : '' ) : ''; ?>
>
	<label for="gigya_<?php echo $var['id']; ?>">
		<?php echo $var['label']; ?>
	</label>
	<textarea rows="5" cols="20" class="large-text"
			  id="gigya_<?php echo $var['id']; ?>"
			  name="<?php echo $var['name'] ?>"
			  <?php echo ( isset( $var['placeholder'] ) ) ? 'placeholder="' . $var['placeholder'] . '"' : ''; ?>
	><?php if ( ! empty( $var['value'] ) ) {
			echo $var['value'];
		} ?></textarea>
	<?php if ( isset( $var['desc'] ) and $var['desc'] ): ?>
		<small><?php echo $var['desc']; ?></small>
	<?php endif; ?>
</div>