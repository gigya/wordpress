<style>
  input {
    -moz-box-sizing: inherit !important;
    -webkit-box-sizing: inherit !important;
    -ms-box-sizing: inherit !important;
    box-sizing: inherit !important;
  }

  .bs-docs-sidenav {
    margin: 0 !important;
  }

  .bs-docs-sidenav > li > a {
    display: block;
    width: 190px 9;
    margin: 0 0 -1px;
    padding: 8px 14px;
    border: 1px solid #E5E5E5;
  }

    /* Sidenav for Docs
    -------------------------------------------------- */

  #gigya-settings-wrap .bs-docs-sidenav {
    width: 220px;
    margin: 30px 0 0;
    padding: 0;
    background-color: #fff;
    -webkit-border-radius: 6px;
    -moz-border-radius: 6px;
    border-radius: 6px;
    -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, .065);
    -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, .065);
    box-shadow: 0 1px 4px rgba(0, 0, 0, .065);
  }

  #gigya-settings-wrap .bs-docs-sidenav > li {
    margin-bottom: 0;
  }

  #gigya-settings-wrap .bs-docs-sidenav > li > a {
    display: block;
    width: 190px  \9;
    margin: 0 0 -1px;
    padding: 8px 14px;
    border: 1px solid #e5e5e5;
  }

  #gigya-settings-wrap .bs-docs-sidenav > li:first-child > a {
    -webkit-border-radius: 6px 6px 0 0;
    -moz-border-radius: 6px 6px 0 0;
    border-radius: 6px 6px 0 0;
  }

  #gigya-settings-wrap .bs-docs-sidenav > li:last-child > a {
    -webkit-border-radius: 0 0 6px 6px;
    -moz-border-radius: 0 0 6px 6px;
    border-radius: 0 0 6px 6px;
  }

  #gigya-settings-wrap .bs-docs-sidenav > .active > a {
    position: relative;
    z-index: 2;
    padding: 9px 15px;
    border: 0;
    text-shadow: 0 1px 0 rgba(0, 0, 0, .15);
    -webkit-box-shadow: inset 1px 0 0 rgba(0, 0, 0, .1), inset -1px 0 0 rgba(0, 0, 0, .1);
    -moz-box-shadow: inset 1px 0 0 rgba(0, 0, 0, .1), inset -1px 0 0 rgba(0, 0, 0, .1);
    box-shadow: inset 1px 0 0 rgba(0, 0, 0, .1), inset -1px 0 0 rgba(0, 0, 0, .1);
    color: #FFF;
  }

    /* Chevrons */
  #gigya-settings-wrap .bs-docs-sidenav .icon-chevron-right {
    float: right;
    margin-top: 2px;
    margin-right: -6px;
    opacity: .25;
  }

  #gigya-settings-wrap .bs-docs-sidenav > li > a:hover {
    background-color: #f5f5f5;
  }

  #gigya-settings-wrap .bs-docs-sidenav a:hover .icon-chevron-right {
    opacity: .5;
  }

  #gigya-settings-wrap .bs-docs-sidenav .active .icon-chevron-right,
  #gigya-settings-wrap .bs-docs-sidenav .active a:hover .icon-chevron-right {
    opacity: 1;
  }

  #gigya-settings-wrap .bs-docs-sidenav.affix {
    top: 40px;
  }

  #gigya-settings-wrap .bs-docs-sidenav.affix-bottom {
    position: absolute;
    top: auto;
    bottom: 270px;
  }

  i {
    display: inline-block;
    margin: 5px 0;

  }

  #gigya-settings-wrap input {
    margin-bottom: 0 !important;
  }

  #footer {
    position: static;
  }


</style>

<script>
  jQuery(document).ready(function ($) {
    //$("[rel=popover]").popover();
  });
</script>

<div class="row" id="gigya-settings-wrap">

<div class="span10">

<?php
if (!current_user_can(GIGYA_PERMISSION_LEVEL)) {
  wp_die(__('Cheatin&#8217; uh?'));
}

$helpUrl = 'http://developers.gigya.com/';

if (isset($_GET["help"])) {
  include 'help.php';
  exit;
}

function gigya_input_field($id = "", $label = "", $default = null, $desc = null) {
  $value = gigya_get_option($id);
  if ($default && empty($value)) {
    $value = $default;
  }
  ?>
  <div class="row">
    <div class="span3">
      <label for="gigya_<?php echo $id; ?>">
        <?php _e($label); ?>
        <?php if ($desc): ?>
        <?php endif; ?>
      </label>
    </div>
    <div class="span6">
      <input type="text" class="input-xlarge" value="<?php echo $value; ?>" id="gigya_<?php echo $id; ?>"
             name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
      <br/>
      <i>
        <small><?php echo $desc; ?></small>
      </i>
    </div>
  </div>
<?php
}

;

function gigya_checkbox_field($id = "", $label = "", $default = null, $desc = null) {
  $value = gigya_get_option($id);
  if ($default && empty($value))
    $value = $default;
  ?>
  <div class="row">
    <div class="span3">
      <label for="gigya_<?php echo $id; ?>">
        <?php _e($label); ?>
      </label>
    </div>
    <div class="span6">
      <input type="checkbox" <?php echo($value || $value == "1" ? "checked='true'" : ""); ?> value="1"
             id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
      <?php if ($desc): ?>
        <br/>
        <i>
          <small><?php echo $desc; ?></small>
        </i>
      <?php endif; ?>
    </div>
  </div>
<?php
}

;

function gigya_textarea_field($id = "", $label = "", $default = null, $desc = null) {
  $value = gigya_get_option($id);
  if ($default && empty($value))
    $value = $default;
  ?>
  <div class="row">
    <div class="span3">
      <label for="gigya_<?php echo $id; ?>">
        <?php _e($label); ?>
      </label>
    </div>
    <div class="span6">
      <textarea class="large-text" id="gigya_<?php echo $id; ?>"
                name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]"><?php echo $value; ?></textarea>
      <?php if ($desc): ?>
        <br/>
        <i>
          <small><?php echo $desc; ?></small>
        </i>
      <?php endif; ?>
    </div>
  </div>
<?php
}

;

function gigya_select_field($id = "", $options = array(), $label = "", $default = null, $desc = null) {
  $value = gigya_get_option($id);
  if ($default && empty($value))
    $value = $default;
  ?>
  <div class="row">
    <div class="span3">
      <label for="gigya_<?php echo $id; ?>">
        <?php _e($label); ?>
        <?php if ($desc): ?>
          <i class="icon-question-sign" data-html="1" data-content="<?php echo esc_html($desc); ?>"
             data-title="<?php echo $label; ?>" rel="popover"></i>
        <?php endif; ?>
      </label>
    </div>
    <div class="span6">
      <select id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]">
        <?php foreach ($options as $option) { ?>
          <option  <?php if ($value == $option["v"])
            echo "selected='true'"; ?> value="<?php echo $option["v"]; ?>"><?php echo $option["t"]; ?></option>
        <?php } ?>
      </select>
      <?php if ($desc): ?>
        <br/>
        <i>
          <small><?php echo $desc; ?></small>
        </i>
      <?php endif; ?>
    </div>
  </div>
<?php
}

?>

<?php
$share_layout = gigya_get_option("share_layout");
if (empty($share_layout))
  $short_url = "horizontal";

$show_counts = gigya_get_option("share_show_counts");
if (empty($show_counts))
  $show_counts = "right";

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



?>

<input type="hidden" name="wordtour_settings[default_artist]" value="<?php echo $options["default_artist"] ?>"></input>

<div class="wrap">
  <div class="icon32" id="icon-options-general"><br></div>
  <h2><?php _e('gigya'); ?> <i><?php _e('v'); ?>:<?php echo GIGYA_VERSION; ?></i></h2>

  <?php
  echo sprintf(__('To learn more about gigya & how setup an account, please visit our developer documentation <a target="_blank"  href="%1$s">here</a>.'), $helpUrl);
  ?>

  <form action="options.php" method="post">
    <?php wp_nonce_field('update-options'); ?>
    <?php settings_fields(GIGYA_SETTINGS_PREFIX); ?>
    <input type="hidden" value="1" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[login_plugin_startup]">
    <input type="hidden" value="1" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[share_providers_startup]">


    <section id="general-section">
      <h3><?php _e('General Settings'); ?></h3>

      <div class="well">
        <!--  API KEY -->
        <?php gigya_input_field("api_key", "Gigya Socialize API Key"); ?>
        <!--  SECRET KEY -->
        <?php gigya_input_field("secret_key", "Gigya Socialize Secret Key"); ?>
        <!--  LANGUAGE -->
        <?php gigya_input_field("lang", "Language", "en", "en (default),zh-cn,zh-hk,zh-tw,cs,da,nl,fi,fr,de,el,hu,it,ja,ko,no,pl,pt,pt-br,ru,es,es-mx,sv,tl
							<p>If not defined, English will be the default language. For the complete list of supported languages, go the <a href=\"http://developers.gigya.com/020_Client_API/010_Objects/Conf_object\" target=\"_blank\">gigya documentation</a></p>"
        ); ?>
        <!--  SHORT URL -->
        <?php
        gigya_select_field("short_url", gigya_get_field_options("short_url"), "shortURL", gigya_get_field_default("short_url"));
        ?>
        <!--  Connect Without -->
        <?php
        gigya_select_field("connect_without", gigya_get_field_options("connect_without_login_behavior"), "Connect Without Login Behavior", gigya_get_field_default("connect_without_login_behavior"));
        ?>
        <!--  GLOBAL PARAMS -->
        <?php gigya_input_field("global_params", "Global Parameters", null, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format'); ?>
        <!--  Google Analytics -->
        <?php gigya_checkbox_field("google_analytics", "Google's Social Analytics"); ?>
        <!--  Load Jquery -->
        <?php gigya_select_field("load_jquery", gigya_get_field_options("load_jquery"), "Load Jquery", gigya_get_field_default("load_jquery")); ?>
      </div>
    </section>

    <section id="login-section">
      <h3><?php _e('Login'); ?></h3>

      <div class="well">
        <?php gigya_checkbox_field("login_plugin", "Enable Gigya Login", null); ?>
        <br/>
        <i>The Plugin displays all the available providers' logos as login options, enabling the user to login to your
          site via his social network / webmail account.</i>
        <hr/>
        <!--  Button Style -->
        <?php
        gigya_select_field("login_button_style", gigya_get_field_options("login_button_style"), "Button Style", gigya_get_field_default("login_button_style"))
        ?>
        <!-- Width -->
        <?php gigya_input_field("login_width", "Width", null); ?>
        <!-- Height -->
        <?php gigya_input_field("login_height", "Height", null); ?>

        <!--  Login Redirect -->
        <?php gigya_input_field("post_login_redirect", "Post Login Redirect", null, 'Provide a URL to redirect users after they logged-in via Gigya social login.'); ?>
        <!--  Force Email -->
        <?php gigya_checkbox_field("force_email", "Email required for registration", null, 'When enabled, new user registering with a social network which does not provide a user email (such as Twitter, Linkedin or others) will be required to provide an Email to complete his registration process to the site. Otherwise a temporary email will be generated for the user in-order to complete the registration.'); ?>
        <!--  Login Providers -->
        <?php gigya_input_field("login_providers", "Login Providers", null, "Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin"); ?>
        <!--  Show Term Link -->
        <?php gigya_checkbox_field("login_term_link", "Show Terms Link", null); ?>
        <!--  Custom Code -->
        <?php gigya_input_field("login_custom_code", "Custom Code", null, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format'); ?>
        <!--  Custom Code -->
        <?php gigya_input_field("login_add_connection_custom", "Custom Code Add Connection", null, 'Enter values in <strong>key1=value1,key2=value2,...,keyX=valueX</strong> format'); ?>
        <!--  Custom Deprecated -->
        <?php gigya_textarea_field("login_ui", "Custom Code (deprecated)", null, "To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see <a target='_blank' href='http://developers.gigya.com/050_CMS_Modules/030_Wordpress_Plugin'>here</a> for help on what to put in the text area."); ?>
        <?php gigya_checkbox_field("show_reg", "Show login form [TBD]", NULL, "Pop up reginstration form during user social registration"); ?>
      </div>
    </section>

    <section id="share-section">
      <h3><?php _e('Sharing'); ?></h3>

      <div class="well">
        <?php
        /* Share Plugin */
        $gigya_share_plugin = array(
          "default" => "1",
          "options" => array(
            array("v" => "1", "t" => "None", "d" => true),
            array("v" => "", "t" => "Bottom"),
            array("v" => "2", "t" => "Top"),
            array("v" => "3", "t" => "Both")
          )
        );
        gigya_select_field("share_plugin", $gigya_share_plugin["options"], "Enable Gigya Share Button", $gigya_share_plugin["default"]);
        /* Count */
        gigya_select_field("share_show_counts", gigya_get_field_options("share_show_counts"), "Show Counts", gigya_get_field_default("share_show_counts"));
        /*  Share Providers*/
        gigya_select_field("share_privacy", gigya_get_field_options("activity_privacy"), "Privacy", gigya_get_field_default("activity_privacy"));

        gigya_input_field("share_providers", "Share Providers", null, 'for example: share,email,pinterest,twitter-tweet,google-plusone,facebook-like');
        /*  Custom Code */
        gigya_textarea_field("share_custom", "Custom Code", null);

        /*  Advanced Params */
        gigya_input_field("share_advanced", "Advanced Parameters", null, 'Enter values in <strong>key|value</strong> format');


        ?>
      </div>
    </section>

    <section id="comments-section">
      <h3><?php _e('Comments'); ?></h3>

      <div class="well">
        <?php gigya_checkbox_field("comments_plugin", "Enable Gigya Comments", null); ?>
        <br/>
        <i><?php _e('Gigya\'s Comments Plugin enables site users to post comments and have discussions about published content on the site.'); ?>
          <hr/>
          <!-- Category ID  -->
          <?php gigya_input_field("comments_cat_id", "Category ID", null) ?>
          <!-- Enable Share Providers  -->
          <?php gigya_input_field("comments_enable_share_providers", "Enable Share Providers", null); ?>
          <!-- Enable Activity  -->
          <?php
          gigya_select_field("comments_enable_share_activity", gigya_get_field_options("comments_enable_share_activity"), "Enable Gigya Share Button", gigya_get_field_default("comments_enable_share_activity"));
          ?>
          <!--  Custom Code -->
          <?php gigya_input_field("commets_custom_code", "Custom Code", null, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format'); ?>
          <!--  Privacy -->
          <?php gigya_select_field("comments_privacy", gigya_get_field_options("activity_privacy"), "Privacy", gigya_get_field_default("activity_privacy")); ?>
      </div>
    </section>

    <section id="reactions-section">
      <h3><?php _e('Reactions'); ?></h3>

      <div class="well">
        <?php gigya_checkbox_field("reaction_plugin", "Enable Reaction Plugin", gigya_get_field_default("reaction_plugin")); ?>
        <hr/>
        <!--  Position -->
        <?php gigya_select_field("reaction_position", gigya_get_field_options("reaction_position"), "Position", gigya_get_field_default("reaction_position")); ?>

        <!--  Show Count -->
        <?php gigya_select_field("reaction_show_counts", gigya_get_field_options("reaction_show_counts"), "Show Counts", gigya_get_field_default("reaction_show_counts")); ?>
        <!--  Show Layout -->
        <?php gigya_select_field("reaction_layout", gigya_get_field_options("reaction_layout"), "Layout", gigya_get_field_default("reaction_layout")); ?>

        <!--  Reaction Array -->
        <?php gigya_textarea_field("reaction_buttons", "Reaction Buttons", null); ?>
        <!--  Providers -->
        <?php gigya_input_field("reaction_providers", "Providers", null, "Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin"); ?>
        <!--  Enable Share Activity -->
        <?php gigya_select_field("reaction_enable_share_activity", gigya_get_field_options("reaction_enable_share_activity"), "Enable Gigya Share Button", gigya_get_field_default("reaction_enable_share_activity")); ?>
        <!--  Count Type -->
        <?php gigya_select_field("reaction_count_type", gigya_get_field_options("reaction_count_type"), "Count Type", gigya_get_field_default("reaction_count_type")); ?>
        <!--  Privacy -->
        <?php gigya_select_field("reaction_privacy", gigya_get_field_options("activity_privacy"), "Privacy", gigya_get_field_default("activity_privacy")); ?>
        <!--  Custom Code -->
        <?php gigya_input_field("reactions_custom_code", "Custom Code", null, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format'); ?>
        <!--  Multiple Reactions -->
        <?php gigya_checkbox_field("reaction_multiple", "Allow multiple reactions", gigya_get_field_default("reaction_multiple")); ?>
      </div>
    </section>

    <section id="gamification-section">
      <h3><?php _e('Gamification'); ?></h3>

      <div class="well">
        <?php gigya_checkbox_field("gamification_notification", "Enable Notifications", gigya_get_field_default("gamification_notification")); ?>
        <br/>
        <i>Define in Widget area</i>

        <hr/>

      </div>
    </section>


    <p class="submit">
      <input type="submit" value="<?php _e('Save Changes') ?>" class="button-primary" name="Submit">
    </p>
  </form>
</div>
<!-- END CONTENT-->
</div>
</div>
	
	
	
	
  