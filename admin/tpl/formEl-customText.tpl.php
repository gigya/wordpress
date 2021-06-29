<?php
/**
 * Template - Custom-Text field form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="gigya-form-field row text-field <?php echo $var['class'] ?>">
	<label for="gigya_<?php echo $var['id']; ?>"><?php echo $var['label']; ?></label>
	<input type="text" disabled size="60" class="input-xlarge" value="*************************"
		   id="gigya_<?php echo $var['id']; ?>"/>
</div>