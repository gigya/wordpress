<?php
/*
Plugin Name: Make Your Site Social
Plugin URI: http://wiki.gigya.com/050_Socialize_Plugins/030_Wordpress_Plugin
DDescription: Increase Registration and Engagement by integrating the Gigya service into your WordPress self hosted blog.
Author: Gil Noy for Gigya
Version: 3.0.5
Author URI: http://www.gigya.com
*/
define("GIGYA_VERSION", "3.0.5");
define("GIGYA_SETTINGS_PREFIX", "gigya_settings_fields");
define("GIGYA_PERMISSION_LEVEL", "manage_options");
define("GIGYA_PLUGIN_URL", WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));
define("GIGYA_PLUGIN_PATH", WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
define("GIGYA_IS_3_2", (floatval(get_bloginfo("version")) >= 3.2));

require_once(dirname(__FILE__) . '/resources/staticdata.php');
require_once(dirname(__FILE__) . '/resources/core.php');
require_once(dirname(__FILE__) . '/resources/user.php');
require_once(dirname(__FILE__) . '/resources/util.php');
require_once(dirname(__FILE__) . '/resources/msg.php');
require_once(dirname(__FILE__) . '/widget.php');
require_once(dirname(__FILE__) . '/resources/handlers.php');

gigya_load_external_file();

if (!function_exists('gigya_admin_menu')) :
  function gigya_admin_menu() {
    $page = add_submenu_page('options-general.php',
      __('Gigya', 'gigya'),
      __('Gigya', 'gigya'),
      GIGYA_PERMISSION_LEVEL,
      __FILE__,
      'gigya_manage_menu'
    );
  }
endif;


function gigya_admin_init() {
  register_setting('gigya_settings_fields', GIGYA_SETTINGS_PREFIX);
}


function gigya_manage_menu() {
  include("admin/settings.php");
}


if (!function_exists('gigya_admin_styles')) :
  function gigya_admin_styles() {
    /*
     * It will be called only on your plugin admin page, enqueue our script here
     */
    //wp_enqueue_script( 'myPluginScript' );
  }
endif;

if (!function_exists('gigya_login_page')) :
  function gigya_login_page() {
    if (gigya_get_option("login_plugin") == 1):
      include("login.php");
    endif;
  }
endif;

if (!function_exists('gigya_signup_page')) :
  function gigya_signup_page() {
    if (gigya_get_option("login_plugin") == 1):
      include("login.php");
    endif;
  }
endif;
if (!empty($_POST) && 'gigya_user_login' == $_POST['action'] && '1' === $_POST['step']) {
  $_GET['action'] = 'register';
}
add_action('init', 'gigya_init_options');
add_action('init', 'gigya_enque_js');
add_action('wp_head', 'gigya_enque_gigya_script');
add_action('login_head', 'gigya_enque_gigya_script');
add_action('admin_init', 'gigya_admin_init');
add_action('admin_menu', 'gigya_admin_menu');

# Login Actions
add_action('login_head', 'gigya_login_page');
add_action('signup_header', 'gigya_signup_page');
add_action('wp_ajax_gigya_user_login', 'gigya_user_login');
add_action('wp_ajax_nopriv_gigya_user_login', 'gigya_user_login');
add_action('wp_ajax_nopriv_gigya_add_comment', 'gigya_user_login');;
add_action('wp_login', 'gigya_notify_user_login');
// instead of wp_logout action - in wp_logout cant retrieve user id - neccessary for notifing Gigya api
add_action('clear_auth_cookie', 'gigya_notify_user_logout');
add_action('user_register', 'gigya_notify_user_register');

# End
add_action('delete_user', 'gigya_delete_account');
add_action('edit_user_profile', 'gigya_user_profile_extra');
add_action('show_user_profile', 'gigya_user_profile_extra');
add_filter('get_avatar', 'gigya_update_avatar_image', 10, 5);
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Gigya");'));
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_GigyaFollowBar");'));
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_GigyaActivityFeed");'));
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_GigyaGamification");'));
add_filter('the_content', 'gigya_share_plugin');
add_filter('the_content', 'gigya_reaction_plugin');

add_action('admin_enqueue_scripts', 'gigya_admin_enqueue');

# Shortcode

// [gamification foo="foo-value"]
add_shortcode('gamification_plugin', 'gamification_shortcode');
// [activity_plugin foo="foo-value"]
add_shortcode('activity_plugin', 'activity_shortcode');

