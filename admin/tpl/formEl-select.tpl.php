<?php
/**
 * Template - Select form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row select <?php echo isset( $class ) ? $class : ''; ?>">
	<label for="gigya_<?php echo $id; ?>"><?php echo $label; ?></label>
	<select id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>"
		<?php if ( isset( $attrs ) ) {
			foreach ( $attrs as $attr => $select_attr_value ) {
				echo $attr . ' ="' . $select_attr_value . '"' . PHP_EOL;
			}
		} ?>
	>
		<?php foreach ( $options as $key => $option ) : ?>
			<?php if ( is_array( $option ) ): ?>
				<option value="<?php echo $options[ $key ]['value']; ?>"
					<?php if ( isset( $options[ $key ]['attrs'] ) ) {
						foreach ( $options[ $key ]['attrs'] as $attr1 => $option_attr_value ) {
							echo $attr1 . ' ="' . $option_attr_value . '"' . PHP_EOL;
						}
					}
					if ( $value == $key ) {
						echo ' selected="true"';
					} ?>
				><?php echo $option['value']; ?></option>

			<?php else: ?>
				<option value="<?php echo $key; ?>"<?php if ( $value == $key ) {
					echo ' selected="true"';
				} ?>><?php echo $option; ?></option>
			<?php endif; ?>
		<?php endforeach ?>
	</select>
	<?php if ( isset( $desc ) ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
	<?php
	if ( isset( $markup ) ):
		echo $markup;
	endif;
	?>
</div>

