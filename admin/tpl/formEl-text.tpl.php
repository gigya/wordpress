<?php
/**
 * Template - Text-field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div
		id="gigya-form-field-<?php echo $var['id']; ?>"
		class="gigya-form-field row text-field <?php echo ( isset( $var['class'] ) ) ? $var['class'] : ''; ?> <?php echo ( isset( $var['depends_on'] ) ) ? 'gigya-depends-on' : ''; ?>"
	<?php echo ( isset( $var['depends_on'] ) ) ? 'data-depends-on="' . htmlspecialchars( json_encode( $var['depends_on'] ), ENT_QUOTES, 'UTF-8' ) . '"' . ( empty( $var['display'] ) ? ' style="display:none;"' : '' ) : ''; ?>
>
	<label for="gigya_<?php echo $var['id']; ?>"><?php echo ( isset( $var['label'] ) ) ? $var['label'] : ''; ?><?php if ( ! empty( $var['required'] ) ) {
			echo '&nbsp;<span class="required">*</span>';
		} ?></label>
	<input type="text" size="<?php echo ( isset( $size ) ) ? (string) $size : '60'; ?>"
		   class="<?php echo ( isset( $var['subclass'] ) ) ? $var['subclass'] : 'input-xlarge'; ?>"
		   style="<?php echo ( isset( $style ) ) ? $style : ''; ?>" value="<?php echo $var['value']; ?>"
		   id="gigya_<?php echo $var['id']; ?>" name="<?php echo $var['name'] ?>"
		<?php if ( ! empty( $var['required'] ) ) {
			echo 'data-required="true"';
		} ?>
	/>
	<?php
	if ( isset( $var['markup'] ) ):
		echo $var['markup'];
	endif;
	?>
	<?php if ( ! empty( $var['desc'] ) ): ?>
		<small><?php echo $var['desc']; ?></small>
	<?php endif; ?>
</div>