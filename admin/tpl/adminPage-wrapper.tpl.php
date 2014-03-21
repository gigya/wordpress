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
		<a href="?page=gigya_global_settings" class="nav-tab <?php echo $page == 'gigya_global_settings' ? 'nav-tab-active' : ''; ?>">Global Settings</a>
		<a href="?page=gigya_login_settings" class="nav-tab <?php echo $page == 'gigya_login_settings' ? 'nav-tab-active' : ''; ?>">Social Login Settings</a>
		<a href="?page=gigya_share_settings" class="nav-tab <?php echo $page == 'gigya_share_settings' ? 'nav-tab-active' : ''; ?>">Share Settings</a>
		<a href="?page=gigya_comments_settings" class="nav-tab <?php echo $page == 'gigya_comments_settings' ? 'nav-tab-active' : ''; ?>">Comments Settings</a>
		<a href="?page=gigya_reactions_settings" class="nav-tab <?php echo $page == 'gigya_reactions_settings' ? 'nav-tab-active' : ''; ?>">Reactions Settings</a>
		<a href="?page=gigya_gm_settings" class="nav-tab <?php echo $page == 'gigya_gm_settings' ? 'nav-tab-active' : ''; ?>">Gamification Settings</a>
		<a href="?page=gigya_raas_settings" class="nav-tab <?php echo $page == 'ggigya_raas_settings' ? 'nav-tab-active' : ''; ?>">RAAS Settings</a>
	</div>

	<?php
		$helpUrl = 'http://developers.gigya.com/015_Partners/030_CMS_and_Ecommerce_Platforms/030_Wordpress_Plugin';
		printf(__('To learn more about gigya & how setup an account, please visit our developer documentation ' . '<a target="_blank"  href="%1$s">here</a>.'), $helpUrl);
	?>

</div>