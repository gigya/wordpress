<?php
/**
 * Template -  markup error message for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */

?>

<div
	<?php $button_exists = false;
	if ( isset( $attrs ) ) {
		foreach ( $attrs as $attr => $parent_attr_value ) {
			echo $attr . '="' . $parent_attr_value . '"' . PHP_EOL;
			if ( $attr == 'class' and strpos($parent_attr_value,'is-dismissible' ) )
			{
				$button_exists = true;
			}
		}
	}; ?>>
	<p <?php if ( isset( $p_attrs ) ) {
		foreach ( $p_attrs as $attr => $parent_attr_value ) {
			echo $attr . '="' . $parent_attr_value . '"' . PHP_EOL;
		}
	}; ?>>
		<?php if ( is_array( $error_message ) ): ?>
			<?php foreach ( $error_message as $key => $error_line ): ?>
				<strong><?php echo $error_line ?> </strong><br>
			<?php endforeach ?>
		<?php else: ?>
			<?php
			echo '<strong>' . $error_message . '</strong>'; ?>
		<?php endif ?>
	</p>
	<?php
	if (!$button_exists): ?>
		<button type="button" class="notice-dismiss gigya-hide-notice-error-message"><span class="screen-reader-text">Dismiss this notice.</span>
		</button>
		<?php endif ?>
</div>
