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
		<a href="?page=gigya-global" class="nav-tab <?php echo $page == 'gigya-global' ? 'nav-tab-active' : ''; ?>">Global Settings</a>
		<a href="?page=gigya-social-login" class="nav-tab <?php echo $page == 'gigya-social-login' ? 'nav-tab-active' : ''; ?>">Social Login Settings</a>
		<a href="?page=gigya-share" class="nav-tab <?php echo $page == 'gigya-share' ? 'nav-tab-active' : ''; ?>">Share Settings</a>
		<a href="?page=gigya-comments" class="nav-tab <?php echo $page == 'gigya-comments' ? 'nav-tab-active' : ''; ?>">Comments Settings</a>
		<a href="?page=gigya-reactions" class="nav-tab <?php echo $page == 'gigya-reactions' ? 'nav-tab-active' : ''; ?>">Reactions Settings</a>
		<a href="?page=gigya-gm" class="nav-tab <?php echo $page == 'gigya-gm' ? 'nav-tab-active' : ''; ?>">Gamification Settings</a>
		<a href="?page=gigya-raas" class="nav-tab <?php echo $page == 'gigya-raas' ? 'nav-tab-active' : ''; ?>">RAAS Settings</a>
	</div>

	<?php
		$helpUrl = 'http://developers.gigya.com/015_Partners/030_CMS_and_Ecommerce_Platforms/030_Wordpress_Plugin';
		printf(__('To learn more about gigya & how setup an account, please visit our developer documentation ' . '<a target="_blank"  href="%1$s">here</a>.'), $helpUrl);
	?>

</div>