<div class="wrap">
	<div class="header">
		<span class="icon32" id="icon-options-general"></span>
		<h1><?php _e('Gigya'); ?> <i><?php echo 'v:' . GIGYA__VERSION; ?></i></h1>
	</div>
	<h2 class="nav-tab-wrapper">
		<a href="?page=gigya" class="nav-tab <?php echo $page == 'gigya' ? 'nav-tab-active' : ''; ?>">Global Settings</a>
		<a href="?page=gigya-social-login" class="nav-tab <?php echo $page == 'gigya-social-login' ? 'nav-tab-active' : ''; ?>">Social Login Settings</a>
		<a href="?page=gigya-share" class="nav-tab <?php echo $page == 'gigya-share' ? 'nav-tab-active' : ''; ?>">Share Settings</a>
		<a href="?page=gigya-comments" class="nav-tab <?php echo $page == 'gigya-comments' ? 'nav-tab-active' : ''; ?>">Comments Settings</a>
		<a href="?page=gigya-reactions" class="nav-tab <?php echo $page == 'gigya-reactions' ? 'nav-tab-active' : ''; ?>">Reactions Settings</a>
		<a href="?page=gigya-gm" class="nav-tab <?php echo $page == 'gigya-gm' ? 'nav-tab-active' : ''; ?>">Gamification Settings</a>
		<a href="?page=gigya-raas" class="nav-tab <?php echo $page == 'gigya-raas' ? 'nav-tab-active' : ''; ?>">RAAS Settings</a>
	</h2>

	<?php
	// TODO: fix link
		$helpUrl = 'http://developers.gigya.com/050_Partners/050_CMS_Modules/030_Wordpress_Plugin';
		echo sprintf(__('To learn more about gigya & how setup an account, please visit our developer documentation ' . '<a target="_blank"  href="%1$s">here</a>.'), $helpUrl);
	?>

	<?php settings_errors(); ?>

	<form class="gigya-settings" action="options.php" method="post">
		<?php wp_nonce_field( 'update-options' ); ?>
		<?php settings_fields( GIGYA__SETTINGS_PREFIX ); ?>
		<input type="hidden" value="1" name="<?php echo GIGYA__SETTINGS_PREFIX ?>[login_plugin_startup]">
		<input type="hidden" value="1" name="<?php echo GIGYA__SETTINGS_PREFIX ?>[share_providers_startup]">
		<div class="<?php echo $page ?>">
			<div class="well">
				<?php do_settings_sections( $page ); ?>
			</div>
			<?php submit_button(); ?>
	</form>
</div>