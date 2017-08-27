<?php
/**
 * Template - Select form element for Gigya settings pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="row select <?php echo $class ?>">
	<label for="gigya_<?php echo $id; ?>"><?php echo $label; ?></label>
	<select id="gigya_<?php echo $id; ?>" name="<?php echo $name ?>">
		<?php foreach ( $options as $key => $option ) : ?>
			<option  <?php if ( $value == $key ) echo "selected='true'"; ?> value="<?php echo $key; ?>"><?php echo $option; ?></option>
		<?php endforeach ?>
	</select>
	<?php if ( $desc ): ?>
		<small><?php echo $desc; ?></small>
	<?php endif; ?>
	<?php
	if ( $markup ):
		 echo $markup;
	 endif;
	?>

</div>