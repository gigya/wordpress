<?php
/**
 * Template - Text-field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div
		class="gigya-form-field row password-field <?php echo ( isset( $var['class'] ) ) ? $var['class'] : ''; ?> <?php echo ( isset( $var['depends_on'] ) ) ? 'gigya-depends-on' : ''; ?>"
	<?php echo ( isset( $var['depends_on'] ) ) ? 'data-depends-on="' . htmlspecialchars( json_encode( $var['depends_on'] ), ENT_QUOTES, 'UTF-8' ) . '"' . ( empty( $var['display'] ) ? ' style="display:none;"' : '' ) : ''; ?>
>
	<label for="gigya_<?php echo $var['id']; ?>"><?php echo $var['label']; ?></label>
	<input type="password" size="60" class="input" value="<?php echo $var['value']; ?>"
		   id="gigya_<?php echo $var['id']; ?>"
		   name="<?php echo $var['name'] ?>"/>
	<?php if ( ! empty( $var['desc'] ) ): ?>
		<small><?php echo $var['desc']; ?></small>
	<?php endif; ?>
	<?php
	if ( isset( $var['markup'] ) ):
		echo $var['markup'];
	endif;
	?>
</div>