<div class="row checkbox <?php echo $class ?>">
	<div class="span3">
		<label for="gigya_<?php echo $id; ?>">
			<input type="hidden" value="0" id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA__SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
			<input type="checkbox" <?php echo($value || $value == "1" ? "checked" : ""); ?> value="1"
						 id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA__SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
			<?php _e($label); ?>
		</label>
		<?php if ($desc): ?>
			<br/>
			<i>
				<small><?php echo $desc; ?></small>
			</i>
		<?php endif; ?>
	</div>
</div>