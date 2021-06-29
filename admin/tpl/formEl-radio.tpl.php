<?php
/**
 * Template - Radio form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row radio <?php echo ( isset( $var['class'] ) ) ? $var['class'] : ''; ?>">
	<fieldset id="gigya_<?php echo $var['id']; ?>">
		<legend><?php echo isset( $var['label'] ) ? $var['label'] : ''; ?></legend>
		<?php foreach ( $var['options'] as $key => $option ) : ?>
			<label>
				<input
						type="radio"
						name="<?php echo $var['name'] ?>"
					<?php if ( $var['value'] == $key )
						echo 'checked="checked"' ?>
					<?php if ( ! empty( $var['disabled'] ) )
						echo 'disabled' ?>
						value="<?php echo $key; ?>"
				>
				<span><?php echo $option; ?></span>
			</label>
		<?php endforeach ?>
	</fieldset>
	<?php if ( isset( $var['desc'] ) and $var['desc'] ): ?>
		<small><?php echo $var['desc']; ?></small>
	<?php endif; ?>
</div>
