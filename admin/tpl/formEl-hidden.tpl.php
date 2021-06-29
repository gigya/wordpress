<?php
/**
 * Template - Hidden-field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>

<input class="gigya-form-field" type="hidden" id="<?php echo $var['id'] ?>" name="<?php echo $var['name']; ?>"
	   value="<?php echo $var['value']; ?>">
<?php if ( ! empty( $var['msg'] ) ) { ?>
	<p class="<?php echo $var['class'] ?>"><?php echo $var['msg_txt'] ?></p>
<?php } ?>
