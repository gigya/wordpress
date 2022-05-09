<div class="custom-description	<?php echo ( isset( $var['class'] ) ) ? $var['class'] : ''; ?> <?php echo ( isset( $var['depends_on'] ) ) ? 'gigya-depends-on' : ''; ?>"
<?php echo ( isset( $var['depends_on'] ) ) ? 'data-depends-on="' . htmlspecialchars( json_encode( $var['depends_on'] ), ENT_QUOTES, 'UTF-8' ) . '"' . ( empty( $var['display'] ) ? ' style="display:none;"' : '' ) : ''; ?>
>
<?php if ( ! empty( $var['small'] ) ): ?>
	<small>
		<?php endif; ?>
		<?php  if(isset ($var['desc'])){echo $var['desc'];}; ?>
		<?php if ( ! empty( $var['small'] ) ): ?>
	</small>
<?php endif; ?>

</div>
