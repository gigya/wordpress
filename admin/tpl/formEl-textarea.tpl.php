<?php
/**
 * Template - Textarea form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="row textarea <?php echo $class ?>">
	<div class="span3">
		<label for="gigya_<?php echo $id; ?>">
			<?php echo $label; ?>
		</label>
	</div>
	<div class="span6">
		<textarea rows="5" cols="20" class="large-text" id="gigya_<?php echo $id; ?>"
							name="<?php echo GIGYA__SETTINGS_PREFIX ?>[<?php echo $id; ?>]"><?php echo $value; ?></textarea>
		<?php if ($desc): ?>
			<br/>
			<i>
				<small><?php echo $desc; ?></small>
			</i>
		<?php endif; ?>
	</div>
</div>