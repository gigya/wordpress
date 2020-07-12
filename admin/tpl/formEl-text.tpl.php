<?php
/**
 * Template - Text-field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div
		id="gigya-form-field-<?php echo $id; ?>"
		class="gigya-form-field row text-field <?php echo ( isset( $class ) ) ? $class : ''; ?> <?php echo ( isset( $depends_on ) ) ? 'gigya-depends-on' : ''; ?>"
	<?php echo ( isset( $depends_on ) ) ? 'data-depends-on="' . htmlspecialchars( json_encode( $depends_on ), ENT_QUOTES, 'UTF-8' ) . '"' . ( empty( $display ) ? ' style="display:none;"' : '' ) : ''; ?>
>
	<label for="gigya_<?php echo $id; ?>"><?php echo ( isset( $label ) ) ? $label : ''; ?><?php if ( ! empty( $required ) ) {
			echo '&nbsp;<span class="required">*</span>';
		} ?></label>
	<input type="text" size="<?php echo ( isset( $size ) ) ? (string) $size : '60'; ?>"
		   class="<?php echo ( isset( $subclass ) ) ? $subclass : 'input-xlarge'; ?>"
		   style="<?php echo ( isset( $style ) ) ? $style : ''; ?>" value="<?php echo $value; ?>"
		   id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>"
		<?php if ( ! empty( $required ) ) {	echo 'data-required="true"'; } ?>
	/>
	<?php
	if ( isset( $markup ) ):
		echo $markup;
	endif;
	?>
	<?php if ( ! empty( $desc ) ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>