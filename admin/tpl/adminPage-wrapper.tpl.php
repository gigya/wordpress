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
		<?php foreach ( $sections as $section ) : ?>
			<a href="?page=<?php echo $section['slug'] ?>" class="nav-tab <?php echo (isset($page) and $page == $section['slug']) ? 'nav-tab-active ' : ''; echo (isset($section['display']) and $section['display'] == 'hidden') ? 'hidden ' : ''; ?>" id="tab-<?php echo $section['slug']; ?>"><?php echo $section['title'] ?></a>
		<?php endforeach; ?>
	</div>
	<?php
		$helpUrl = 'https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/LATEST/en-US/414f36ba70b21014bbc5a10ce4041860.html';
		printf( __( 'To learn more about SAP Customer Data Cloud & how to setup an account, please visit our developer documentation <a target="_blank" rel="noopener noreferrer" href="%1$s" title="SAP CDC Documentation">here</a>.' ), $helpUrl );
	?>
</div>
