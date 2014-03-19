<div class="row select <?php echo $class ?>">
	<div class="span3">
		<label for="gigya_<?php echo $id; ?>">
			<?php _e($label); ?>
			<?php if ($desc): ?>
				<i class="icon-question-sign" data-html="1" data-content="<?php echo esc_html($desc); ?>"
					 data-title="<?php echo $label; ?>" rel="popover"></i>
			<?php endif; ?>
		</label>
	</div>
	<div class="span6">
		<select id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA__SETTINGS_PREFIX ?>[<?php echo $id; ?>]">
			<?php foreach ($options as $key => $option) { ?>
				<option  <?php if ($value == $key)
					echo "selected='true'"; ?> value="<?php echo $key; ?>"><?php echo $option; ?></option>
			<?php } ?>
		</select>
		<?php if ($desc): ?>
			<br/>
			<i>
				<small><?php echo $desc; ?></small>
			</i>
		<?php endif; ?>
	</div>
</div>