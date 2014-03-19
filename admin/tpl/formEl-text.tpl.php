<div class="row text-field <?php echo $class ?>">
	<div class="span3">
		<label for="gigya_<?php echo $id; ?>">
			<?php _e($label); ?>
		</label>
	</div>
	<div class="span6">
		<input type="text" size="80" class="input-xlarge" value="<?php echo $value; ?>" id="gigya_<?php echo $id; ?>"
					 name="<?php echo GIGYA__SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
		<br/>
		<?php if (!empty($desc)): ?>
			<i>
				<small><?php echo $desc; ?></small>
			</i>
		<?php endif; ?>
	</div>
</div>