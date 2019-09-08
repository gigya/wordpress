<?php
/**
 * Template - wrapper for Gigya setting pages.
 * Render with @see _gigya_render_tpl().
 */
?>
<div class="wrap">
	<div class="header">
		<span class="icon32" id="icon-options-general"></span>

		<h1>SAP Customer Data Cloud GConnector version <?php echo GIGYA__VERSION; ?></h1>
	</div>
	<div class="nav-tab-wrapper">
		<?php foreach ( GigyaSettings::getSections() as $section ) : ?>
			<a href="?page=<?php echo $section['slug'] ?>" class="nav-tab <?php echo (isset($page) and $page == $section['slug']) ? 'nav-tab-active ' : ''; echo (isset($section['display']) and $section['display'] == 'hidden') ? 'hidden ' : ''; ?>" id="tab-<?php echo $section['slug']; ?>"><?php echo $section['title'] ?></a>
		<?php endforeach; ?>
	</div>
	<?php
		$helpUrl = 'https://developers.gigya.com/display/GD/WordPress';
		printf( __( 'To learn more about SAP Customer Data Cloud & how to setup an account, please visit our developer documentation <a target="_blank" rel="noopener noreferrer" href="%1$s">here</a>.' ), $helpUrl );
	?>
</div>