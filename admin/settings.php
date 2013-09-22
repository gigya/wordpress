<?php
if (isset($_GET["help"])) {
  include 'help.php';
  exit;
}
$share_layout = gigya_get_option("share_layout");
if (empty($share_layout)) {
  $short_url = "horizontal";
}

$show_counts = gigya_get_option("share_show_counts");
if (empty($show_counts)) {
  $show_counts = "right";
}

?>
<?php function gigya_admin_page() {
  if (!current_user_can(GIGYA_PERMISSION_LEVEL)) {
    wp_die(__('Cheatin&#8217; uh?'));
  }
  $share_custom = gigya_get_option("share_custom");
  $login_ui = gigya_get_option("login_ui");
  $account_linking = 1;
  $share_plugin = gigya_get_option("share_plugin");
  $comments_plugin = gigya_get_option("comments_plugin") == 1 ? 1 : 0;
  $gigya_comments_cat_id = gigya_get_option("gigya_comments_cat_id");
  $login_plugin = gigya_get_option("login_plugin") == 1 ? 1 : 0;
  $providers = gigya_get_option("share_providers");
  $loginProviders = gigya_get_option("login_providers");
  $load_jquery = gigya_get_option("load_jquery");
  $global_params = gigya_get_option("global_params");
  $google_analytics = gigya_get_option("google_analytics") == 1 ? 1 : 0;
  $reaction_plugin = gigya_get_option("reaction_plugin") == 1 ? 1 : 0;
  ob_start();
  $page = $_GET['page'];
  ?>
  <div class="wrap">
    <div class="header">
      <span class="icon32" id="icon-options-general"></span>

      <h1><?php _e('Gigya'); ?> <i><?php _e('v'); ?>:<?php echo GIGYA_VERSION; ?></i></h1>
    </div>
    <h2 class="nav-tab-wrapper">
      <a href="?page=gigya" class="nav-tab <?php echo $page == 'gigya' ? 'nav-tab-active' : ''; ?>">Global Settings</a>
      <a href="?page=gigya-social-login"
         class="nav-tab <?php echo $page == 'gigya-social-login' ? 'nav-tab-active' : ''; ?>">Social Login Settings</a>
      <a href="?page=gigya-share" class="nav-tab <?php echo $page == 'gigya-share' ? 'nav-tab-active' : ''; ?>">Share
        Settings</a>
      <a href="?page=gigya-comments" class="nav-tab <?php echo $page == 'gigya-comments' ? 'nav-tab-active' : ''; ?>">Comments
        Settings</a>
      <a href="?page=gigya-reactions" class="nav-tab <?php echo $page == 'gigya-reactions' ? 'nav-tab-active' : ''; ?>">Reactions
        Settings</a>
      <a href="?page=gigya-gm" class="nav-tab <?php echo $page == 'gigya-gm' ? 'nav-tab-active' : ''; ?>">Gamification
        Settings</a>
    </h2>

    <?php
    // TODO: fix link
    $helpUrl = 'http://developers.gigya.com/050_Partners/050_CMS_Modules/030_Wordpress_Plugin';
    echo sprintf(__('To learn more about gigya & how setup an account, please visit our developer documentation <a target="_blank"  href="%1$s">here</a>.'), $helpUrl);
    ?>
    <?php settings_errors(); ?>
    <form class="gigya-settings" action="options.php" method="post">
      <?php wp_nonce_field('update-options'); ?>
      <?php settings_fields(GIGYA_SETTINGS_PREFIX); ?>
      <input type="hidden" value="1" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[login_plugin_startup]">
      <input type="hidden" value="1" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[share_providers_startup]">
      <?php
      $sec = $_GET['page'];
      ?>
      <div class="<?php echo $page ?>">
        <?php
        do_settings_sections($sec);
        submit_button();
        ?>
    </form>
  </div>
  <?php
  echo ob_get_clean();
}

function gigya_login_admin() {
  gigya_admin_page('gigya-login');
}

?>