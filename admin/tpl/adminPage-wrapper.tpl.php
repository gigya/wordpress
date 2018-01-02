<?php
/**
 * Template - wrapper for Gigya setting pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="wrap">
	<div class="header">
		<span class="icon32" id="icon-options-general"></span>

		<h1>Gigya v:<?php echo GIGYA__VERSION; ?></h1>
	</div>
	<div class="nav-tab-wrapper">
		<?php foreach ( GigyaSettings::getSections() as $section ) : ?>
			<a href="?page=<?php echo $section['slug'] ?>" class="nav-tab <?php echo $page == $section['slug'] ? 'nav-tab-active' : ''; ?>"><?php echo $section['title'] ?></a>
		<?php endforeach; ?>
	</div>

	<?php
		$helpUrl = 'https://developers.gigya.com/display/GD/WordPress+Plugin';
		printf( __( 'To learn more about Gigya & how to setup an account, please visit our developer documentation <a target="_blank" rel="noopener noreferrer" href="%1$s">here</a>.' ), $helpUrl );
	?>
</div>