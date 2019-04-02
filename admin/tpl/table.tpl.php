<?php
/**
 * Template - Line of Text-field form elements for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<table class="gigya-wp-settings-table <?php echo (isset($class)) ? $class : ''; ?>">
	<?php
    $row_size = 0;
	foreach ( $rows as $id => $row ):
        $row_size = count($row) + 1;
		echo _gigya_element_render( $row, $id, $name_prefix . '[' . $name . ']' );
	endforeach;
	?>

    <tr>
        <td colspan="<?php echo $row_size; ?>">
            <a class="button button-primary gigya-add-dynamic-field-line">Add</a>
        </td>
    </tr>

	<?php if ( ! empty( $desc ) ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
</table>