<?php
/**
 * Template - Hidden-field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>

<input class="gigya-form-field" type="hidden" id="<?php echo $id ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
<?php if ( !empty($msg) ) { ?>
    <p class="<?php echo $class ?>"><?php echo $msg_txt ?></p>
<?php } ?>
