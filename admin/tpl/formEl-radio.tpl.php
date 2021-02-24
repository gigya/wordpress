<?php
/**
 * Template - Radio form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row radio <?php echo ( isset( $class ) ) ? $class : ''; ?>">
	<fieldset id="gigya_<?php echo $id; ?>">
		<legend><?php echo isset( $label ) ? $label : ''; ?></legend>
		<?php foreach ( $options as $key => $option ) : ?>
			<label>
				<input
					type="radio"
					name="<?php echo $name ?>"
					<?php if ( $value == $key ) echo 'checked="checked"' ?>
					<?php if ( !empty( $disabled ) ) echo 'disabled' ?>
					value="<?php echo $key; ?>"
				>
				<span><?php echo $option; ?></span>
			</label>
		<?php endforeach ?>
	</fieldset>
	<?php if ( isset($desc) and $desc ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</div>
