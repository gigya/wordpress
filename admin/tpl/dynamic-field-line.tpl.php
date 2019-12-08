<?php
/**
 * Template - Line of Text-field form elements for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<tr class="row <?php echo ( isset( $class ) ) ? $class : ''; ?>">
	<?php
	foreach ( $fields as $field ):
		$size = ( isset( $field['size'] ) ) ? (string) $field['size'] : '60';
		$subclass = ( isset( $field['subclass'] ) ) ? $field['subclass'] : 'input-xlarge';
		$id = ( isset( $field['id'] ) ) ? $field['id'] : preg_replace( '/\[|\]/', '-', $field['name'] );

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
					<?php echo ( $field['value'] ) ? 'checked' : ''; ?>
                />

            <?php elseif ( $field['type'] == 'select' ): ?>
                <label for="gigya_<?php echo $id; ?>"><?php echo ( isset( $field['label'] ) ) ? $field['label'] : ''; ?></label>
                <select id="gigya_<?php echo $id; ?>" data-type="<?php echo $field['label'] ?>"
                        name="<?php echo $field['name'] ?>"
					<?php
					echo $required;
					if ( isset( $field['attrs'] ) ) {
						foreach ( $field['attrs'] as $attr => $select_attr_value ) {
							echo $attr . ' ="' . $select_attr_value . '"' . PHP_EOL;
						}
					};
					?>
                >

                    <?php foreach ( $field['options'] as $key => $option ) : ?>
                        <option value="<?php echo $field['options'][ $key ]['label']; ?>"
							<?php if ( $field['value'] == $option['label'] ) {
								echo 'selected = true';
							} ?>
							<?php if ( isset( $field['options'][ $key ]['attrs'] ) ) {
								foreach ( $field['options'][ $key ]['attrs'] as $attr => $option_attr_value ) {
									echo $attr . ' ="' . $option_attr_value . '"' . PHP_EOL;
								}
							} ?>

                        ><?php echo $option['label']; ?></option>
					<?php endforeach ?>

                </select>

                <?php if ( isset( $field['markup'] ) ) {
					echo $field['markup'];
				}
				?>

                <?php if ( isset( $desc ) ): ?>
                    <small><?php echo $desc; ?></small>
				<?php endif; ?>
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
        &nbsp;<br/><a
                class="button gigya-remove-dynamic-field-line button-primary dashicons-before dashicons-trash" <?php echo ( isset( $disabled ) ) ? 'disabled' : ''; ?>></a>
    </td>

	<?php if ( ! empty( $desc ) ): ?>
        <small><?php echo $desc; ?></small>
	<?php endif; ?>
</tr>
