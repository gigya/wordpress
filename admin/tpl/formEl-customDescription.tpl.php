<div class="custom-description <?php echo ( isset( $class ) ) ? $class : ''; ?> <?php echo ( isset( $depends_on ) ) ? 'gigya-depends-on' : ''; ?>"
	<?php echo ( isset( $depends_on ) ) ? 'data-depends-on="' . htmlspecialchars( json_encode( $depends_on ), ENT_QUOTES, 'UTF-8' ) . '"' . ( empty( $display ) ? ' style="display:none;"' : '' ) :
		''; ?>>
	<?php if ( ! empty( $small ) ): ?>

	<small>
		<?php endif; ?>
		<?php echo $desc; ?>
		<?php if ( ! empty( $small ) ): ?>
	</small>
<?php endif; ?>
</div>
