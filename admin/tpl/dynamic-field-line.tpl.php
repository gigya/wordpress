<?php
/**
 * Template - Line of Text-field form elements for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<tr class="row <?php echo (isset($class)) ? $class : ''; ?>">
	<?php
		foreach ($fields as $field):
			$size = (isset($field['size'])) ? (string)$field['size'] : '60';
		    $subclass = (isset($field['subclass'])) ? $field['subclass'] : 'input-xlarge';
		    $id = (isset($field['id'])) ? $field['id'] : preg_replace('/\[|\]/', '-', $field['name']);

		    $required = '';
			if ( ! empty( $field['required'] ) ) {
				$required = 'data-required="' . strval( $field['required'] ) . '"';
			}
	?>
	<td style="vertical-align: top;">
		<?php if ( $field['type'] == 'text' ): ?>
			<label for="gigya_<?php echo $id; ?>"><?php echo ( isset( $field['label'] ) ) ? $field['label'] : ''; ?><?php if ( ! empty( $field['required'] ) ) {
					echo '&nbsp;<span class="required">*</span>';
				} ?></label>
			<input type="text" size="<?php echo $size; ?>"
				   class="gigya-form-field <?php echo ( ( isset( $field['class'] ) ) ? $field['class'] . ' ' : '' ) . $subclass; ?>"
				   value="<?php echo $field['value']; ?>"
				   id="gigya_<?php echo $id; ?>"
				   name="<?php echo $field['name'] ?>" <?php echo $required; ?>
			/>
		<?php elseif ( $field['type'] == 'checkbox' ): ?>
			<label for="gigya_<?php echo $id; ?>"><?php echo ( isset( $field['label'] ) ) ? $field['label'] : ''; ?></label>
			<input type="checkbox"
				   class="gigya-form-field <?php echo ( ( isset( $field['class'] ) ) ? $field['class'] . ' ' : '' ) . $subclass; ?>"
				   id="gigya_<?php echo $id; ?>" name="<?php echo $field['name'] ?>"
				   <?php echo ($field['value']) ? 'checked' : ''; ?>
			/>
		<?php endif; ?>
		<?php
		if ( isset( $markup ) ):
			echo $markup;
		endif;
		?>
	</td>
    <?php
        endforeach;
    ?>
    <td>
        &nbsp;<br /><a class="button gigya-remove-dynamic-field-line button-primary dashicons-before dashicons-trash" <?php echo (isset($disabled)) ? 'disabled' : ''; ?>></a>
    </td>

	<?php if ( ! empty( $desc ) ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</tr>