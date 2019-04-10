<?php
/**
 * Template - Custom-Text field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row text-field <?php echo $class ?>">
	<label for="gigya_<?php echo $id; ?>"><?php echo $label; ?></label>
	<input type="text" disabled size="60" class="input-xlarge" value="*************************" id="gigya_<?php echo $id; ?>" />
</div>