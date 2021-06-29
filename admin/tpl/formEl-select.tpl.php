<?php
/**
 * Template - Select form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row select <?php echo isset( $var['class'] ) ? $var['class'] : ''; ?>">
	<label for="gigya_<?php echo $var['id']; ?>"><?php echo $var['label']; ?></label>
	<select id="gigya_<?php echo $var['id']; ?>" name="<?php echo $var['name'] ?>"
		<?php if ( isset( $var['attrs'] ) ) {
			foreach ( $var['attrs'] as $attr => $select_attr_value ) {
				echo $attr . ' ="' . $select_attr_value . '"' . PHP_EOL;
			}
		} ?>
	>
		<?php foreach ( $var['options'] as $key => $option ) : ?>
			<?php if ( is_array( $option ) ): ?>
				<option value="<?php echo $var['options'][ $key ]['value']; ?>"
					<?php if ( isset( $var['options'][ $key ]['attrs'] ) ) {
						foreach ( $var['options'][ $key ]['attrs'] as $attr1 => $option_attr_value ) {
							echo $attr1 . ' ="' . $option_attr_value . '"' . PHP_EOL;
						}
					}
					if ( $var['value'] == $key ) {
						echo ' selected="true"';
					} ?>
				><?php echo $option['value']; ?></option>

			<?php else: ?>
				<option value="<?php echo $key; ?>"<?php if ( $var['value'] == $key ) {
					echo ' selected="true"';
				} ?>><?php echo $option; ?></option>
			<?php endif; ?>
		<?php endforeach ?>
	</select>
	<?php if ( isset( $var['desc'] ) ): ?>
		<small><?php echo $var['desc']; ?></small>
	<?php endif; ?>
	<?php
	if ( isset( $var['markup'] ) ):
		echo $var['markup'];
	endif;
	?>
</div>

