<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Yaniv Aran-Shamir
 * Date: 9/9/13
 * Time: 2:12 PM
 */

class GigyaAdmin {

  public function globalSectionCallback($args) {
    ob_start();
    ?>
    <div class="well">
      <!--  API KEY -->
      <?php gAdminFormElements::gigya_input_field("api_key", "Gigya Socialize API Key"); ?>
      <!--  SECRET KEY -->
      <?php gAdminFormElements::gigya_input_field("secret_key", "Gigya Socialize Secret Key"); ?>
      <!-- Data Center -->
      <?php gAdminFormElements::gigya_select_field('data_center', gigya_get_field_options('data_center'), 'Data Center', gigya_get_field_default('data_center')) ?>
      <!-- Providers list !-->
      <?php gAdminFormElements::gigya_input_field("providers", "List of providers", '*', 'Comma separated list of networks that would be included. For example: "Facebook, Twitter, Yahoo".
* means all networks. See list of available <a href="http://developers.gigya.com/020_Client_API/020_Methods/Socialize.showLoginUI">Providers</a>'
      ); ?>

      <!--  LANGUAGE -->
      <?php $lang_opts = array(
        'en' => 'English',
        'zh-cn' => 'Chinese',
        'zh-hk' => 'Chinese (Hong Kong)',
        'zh-tw' => 'Chinese (Taiwan)',
        'cs' => 'Czech',
        'da' => 'Danish',
        'nl' => 'Dutch',
        'fi' => 'Finnish',
        'fr' => 'French',
        'de' => 'German',
        'el' => 'Greek',
        'hu' => 'Hungarian',
        'id' => 'Indonesian',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'ms' => 'Malay',
        'no' => 'Norwegian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'pt-br' => 'Portuguese (Brazil)',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'es' => 'Spanish',
        'es-mx' => 'Spanish (Mexican)',
        'sv' => 'Swedish',
        'tl' => 'Tagalog (Philippines)',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'vi' => 'Vietnamese',
      );
      gAdminFormElements::gigya_select_field("lang", $lang_opts, "Language", "en", "Please select the interface language"); ?>
      <!--  SHORT URL -->
      <?php
      gAdminFormElements::gigya_select_field("short_url", gigya_get_field_options("short_url"), "shortURL", gigya_get_field_default("short_url"));
      ?>
      <!--  GLOBAL PARAMS -->
      <?php gAdminFormElements::gigya_textarea_field("global_params", "Additional Global Parameters (advanced)", NULL, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format<br /> See list of available <a href="http://developers.gigya.com/030_API_reference/010_Client_API/010_Objects/Conf_object" target="_blank">parameters</a>'); ?>
      <!--  Google Analytics -->
      <?php gAdminFormElements::gigya_checkbox_field("google_analytics", "Google's Social Analytics"); ?>
      <!--Debug Log-->
      <?php gAdminFormElements::gigya_checkbox_field("gigya_debug", "Enable Gigya debug log") ?>
      <!--  Load Jquery -->
      <?php gAdminFormElements::gigya_select_field("load_jquery", gigya_get_field_options("load_jquery"), "Load Jquery", gigya_get_field_default("load_jquery")); ?>
    </div>
    <?php echo ob_get_clean();
  }

  public function loginSectionCallback() {
    ob_start();
    ?>

    <div class="well">
      <?php gAdminFormElements::gigya_checkbox_field("login_plugin", "Enable Gigya Social Login", NULL); ?>
      <br/>
      <!--  Button Style -->
      <?php
      gAdminFormElements::gigya_select_field("login_button_style", gigya_get_field_options("login_button_style"), "Button Style", gigya_get_field_default("login_button_style"))
      ?>
      <!--  Connect Without -->
      <?php
      gAdminFormElements::gigya_select_field("connect_without", gigya_get_field_options("connect_without_login_behavior"), "Connect Without Login Behavior", gigya_get_field_default("connect_without_login_behavior"));
      ?>
      <!-- Width -->
      <?php gAdminFormElements::gigya_input_field("login_width", "Width", NULL, 'The width of the plugin in px', 'size'); ?>
      <!-- Height -->
      <?php gAdminFormElements::gigya_input_field("login_height", "Height", NULL, 'The height of the plugin in px', 'size'); ?>
      <!--  Login Redirect -->
      <?php gAdminFormElements::gigya_input_field("post_login_redirect", "Post Login Redirect", NULL, 'Provide a URL to redirect users after they logged-in via Gigya social login.'); ?>
      <!--  Login Providers -->
      <?php gAdminFormElements::gigya_input_field("login_providers", "Login Providers", NULL, "Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin"); ?>
      <!--  Show Term Link -->
      <?php gAdminFormElements::gigya_checkbox_field("login_term_link", "Show Terms Link", NULL); ?>
      <?php gAdminFormElements::gigya_checkbox_field("show_reg", "Show Complete Registration Form", NULL, "Check this checkbox if you have defined required fields in you site registration form. When checked a 'Complete Registration' form will pop up during user social registration, to let the user enter the missing required fields"); ?>
      <!--  Custom Code -->
      <?php gAdminFormElements::gigya_textarea_field("login_custom_code", "Custom Code", NULL, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format'); ?>
      <!--  Custom Code -->
      <?php gAdminFormElements::gigya_textarea_field("login_add_connection_custom", "Custom Code Add Connection", NULL, 'Enter values in <strong>key1=value1,key2=value2,...,keyX=valueX</strong> format'); ?>
      <!--  Custom Deprecated -->
      <?php gAdminFormElements::gigya_textarea_field("login_ui", "Custom Code (deprecated)", NULL, "To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see <a target='_blank' href='http://developers.gigya.com/050_CMS_Modules/030_Wordpress_Plugin'>here</a> for help on what to put in the text area.", 'closed'); ?>
    </div>
    <?php
    echo ob_get_clean();
  }

  public function shareSectionCallback() {
    ob_start();
    ?>
    <div class="well">
      <?php
      /* Share Plugin */
      $gigya_share_plugin = array(
        "default" => "1",
        "options" => array(
          "1" => "None",
          "" => "Bottom",
          "2" => "Top",
          "3" => "Both"
        )
      );
      gAdminFormElements::gigya_select_field("share_plugin", $gigya_share_plugin["options"], "Enable Gigya Share Button", $gigya_share_plugin["default"]);
      /* Count */
      gAdminFormElements::gigya_select_field("share_show_counts", gigya_get_field_options("share_show_counts"), "Show Counts", gigya_get_field_default("share_show_counts"));
      /*  Share Providers*/
      gAdminFormElements::gigya_select_field("share_privacy", gigya_get_field_options("activity_privacy"), "Privacy", gigya_get_field_default("activity_privacy"));

      gAdminFormElements::gigya_input_field("share_providers", "Share Providers", NULL, 'for example: share,email,pinterest,twitter-tweet,google-plusone,facebook-like');
      /*  Custom Code */
      gAdminFormElements::gigya_textarea_field("share_custom", "Custom Code", NULL);

      /*  Advanced Params */
      gAdminFormElements::gigya_textarea_field("share_advanced", "Advanced Parameters", NULL, 'Enter values in <strong>key|value</strong> format');


      ?>
    </div>
    <?php
    echo ob_get_clean();
  }

  public function commentsSectionCallback() {
    ob_start();
    ?>
    <div class="well">
      <?php gAdminFormElements::gigya_checkbox_field("comments_plugin", "Enable Gigya Comments", NULL); ?>
      <br/>
      <!-- Category ID  -->
      <?php gAdminFormElements::gigya_input_field("comments_cat_id", "Category ID", NULL) ?>
      <!-- Enable Share Providers  -->
      <?php gAdminFormElements::gigya_input_field("comments_enable_share_providers", "Enable Share Providers", NULL); ?>
      <!-- Enable Activity  -->
      <?php
      gAdminFormElements::gigya_select_field("comments_enable_share_activity", gigya_get_field_options("comments_enable_share_activity"), "Enable Sharing to Activity Feed", gigya_get_field_default("comments_enable_share_activity"), 'When publishing feed items, by default the feed items are published to social networks only and will not appear on the site\'s Activity Feed plugin. To change this behavior, you must change the publish scope to "Both".');
      ?>
      <!--  Custom Code -->
      <?php gAdminFormElements::gigya_textarea_field("commets_custom_code", "Additional Comments Parameters (advanced)", NULL, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format <br />See list of available <a href="http://developers.gigya.com/020_Client_API/030_Comments/comments.showCommentsUI" target="_blank">parameters</a>.'); ?>
      <!--  Privacy -->
      <?php gAdminFormElements::gigya_select_field("comments_privacy", gigya_get_field_options("activity_privacy"), "Privacy", gigya_get_field_default("activity_privacy")); ?>
    </div>
    <?php
    echo ob_get_clean();
  }

  public function reactionSectionCallback() {
    ob_start();
    ?>
    <div class="well">
      <?php gAdminFormElements::gigya_checkbox_field("reaction_plugin", "Enable Reaction Plugin", gigya_get_field_default("reaction_plugin")); ?>
      <!--  Position -->
      <?php gAdminFormElements::gigya_select_field("reaction_position", gigya_get_field_options("reaction_position"), "Position", gigya_get_field_default("reaction_position")); ?>

      <!--  Show Count -->
      <?php gAdminFormElements::gigya_select_field("reaction_show_counts", gigya_get_field_options("reaction_show_counts"), "Show Counts", gigya_get_field_default("reaction_show_counts")); ?>
      <!--  Show Layout -->
      <?php gAdminFormElements::gigya_select_field("reaction_layout", gigya_get_field_options("reaction_layout"), "Layout", gigya_get_field_default("reaction_layout")); ?>

      <!--  Reaction Array -->
      <?php $default_reactions = "{
	    text: 'Amazing',
        ID: 'amazing',
        iconImgUp:'http://cdn.gigya.com/gs/i/reactions/icons/Amazing_Icon_Up.png',
		iconImgOver:'http://cdn.gigya.com/gs/i/reactions/icons/Amazing_Icon_Down.png',
        tooltip:'This item is amazing',
        feedMessage: 'This is amazing!',
		headerText:'Your reaction to this post is \'Amazing\''
	}
,{
text: 'Geeky',
ID: 'geeky',
iconImgUp:'http://cdn.gigya.com/gs/i/reactions/icons/Geeky_Icon_Up.png',
iconImgOver:'http://cdn.gigya.com/gs/i/reactions/icons/Geeky_Icon_Down.png',
tooltip:'This item is geeky',
feedMessage: 'This is geeky!',
headerText:'Your reaction to this post is \'Geeky\''
}"?>
      <?php gAdminFormElements::gigya_textarea_field("reaction_buttons", "Reaction Buttons", $default_reactions, NULL); ?>
      <!--  Providers -->
      <?php gAdminFormElements::gigya_input_field("reaction_providers", "Providers", NULL, "Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin"); ?>
      <!--  Enable Share Activity -->
      <?php gAdminFormElements::gigya_select_field("reaction_enable_share_activity", gigya_get_field_options("reaction_enable_share_activity"), "Enable Sharing to Activity Feed", gigya_get_field_default("reaction_enable_share_activity"), 'When publishing feed items, by default the feed items are published to social networks only and will not appear on the site\'s Activity Feed plugin. To change this behavior, you must change the publish scope to "Both".'); ?>
      <!--  Count Type -->
      <?php gAdminFormElements::gigya_select_field("reaction_count_type", gigya_get_field_options("reaction_count_type"), "Count Type", gigya_get_field_default("reaction_count_type")); ?>
      <!--  Privacy -->
      <?php gAdminFormElements::gigya_select_field("reaction_privacy", gigya_get_field_options("activity_privacy"), "Privacy", gigya_get_field_default("activity_privacy")); ?>
      <!--  Custom Code -->
      <?php gAdminFormElements::gigya_textarea_field("reactions_custom_code", "Custom Code", NULL, 'Enter values in <strong>key1=value1|key2=value2...keyX=valueX</strong> format <br />See list of available <a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showReactionsBarUI" target="_blank">parameters</a>.'); ?>
      <!--  Multiple Reactions -->
      <?php gAdminFormElements::gigya_checkbox_field("reaction_multiple", "Allow multiple reactions", gigya_get_field_default("reaction_multiple")); ?>
    </div>
    <?php
    echo ob_get_clean();
  }

  public function gmSectionCallback() {
    ob_start();
    ?>
    <div class="well">
      <?php gAdminFormElements::gigya_checkbox_field("gamification_notification", "Enable Notifications", gigya_get_field_default("gamification_notification")); ?>
      <br/>
      <i>Define in Widget area [change this text]</i>

      <hr/>

    </div>
    <?php
    echo ob_get_clean();
  }
}

?>
