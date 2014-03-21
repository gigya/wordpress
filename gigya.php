<?php
/**
 * Plugin Name: Gigya - Make Your Site Social
 * Plugin URI: http://gigya.com
 * Description: Allows sites to utilize the Gigya API for authentication and social network updates.
 * Version: 5.0
 * Author: Gigya
 * Author URI: http://gigya.com
 * License: GPL2+
 */

// --------------------------------------------------------------------

/**
 * Global constants.
 */
define( 'GIGYA__MINIMUM_WP_VERSION', '3.5' );
define( 'GIGYA__MINIMUM_PHP_VERSION', '5.2' );
define( 'GIGYA__VERSION', '5.0' );
define( 'GIGYA__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Gigya settings sections.
 */
define( 'GIGYA__SETTINGS_GLOBAL', 'gigya_global_settings' );
define( 'GIGYA__SETTINGS_LOGIN', 'gigya_login_settings' );
define( 'GIGYA__SETTINGS_SHARE', 'gigya_share_settings' );
define( 'GIGYA__SETTINGS_COMMENTS', 'gigya_comments_settings' );
define( 'GIGYA__SETTINGS_REACTIONS', 'gigya_reactions_settings' );
define( 'GIGYA__SETTINGS_GM', 'gigya_gm_settings' );
define( 'GIGYA__SETTINGS_RAAS', 'gigya_raas_settings' );

// --------------------------------------------------------------------


/**
 * Hook init.
 */
add_action( 'init', '_gigya_init_action' );
function _gigya_init_action() {
	require_once( GIGYA__PLUGIN_DIR . 'sdk/GSSDK.php' );
	require_once( GIGYA__PLUGIN_DIR . 'class/class.GigyaUser.php' );
	require_once( GIGYA__PLUGIN_DIR . 'class/class.GigyaApi.php' );

	// Load jQuery.
	wp_enqueue_script( 'jquery' );

	// Gigya configuration values.
	$login_options  = get_option( GIGYA__SETTINGS_LOGIN );
	$global_options = get_option( GIGYA__SETTINGS_GLOBAL );

	if ( ! empty( $login_options['login_plugin'] ) && ! empty( $global_options['global_api_key'] ) ) {
		// Load Gigya's socialize.js.
		wp_enqueue_script( 'gigya', 'http://cdn.gigya.com/JS/socialize.js?apiKey=' . $global_options['global_api_key'], 'jquery' );
	}

	if ( is_admin() ) {
		// Load the settings.
		require_once( GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php' );
		new GigyaSettings;
	}
}

// --------------------------------------------------------------------

/**
 * Hook login form.
 */
add_action( 'login_form', '_gigya_login_form_action' );
function _gigya_login_form_action() {
	// Gigya configuration values.
	$login_options  = get_option( GIGYA__SETTINGS_LOGIN );
	$global_options = get_option( GIGYA__SETTINGS_GLOBAL );

	if ( ! empty( $login_options['login_plugin'] ) && ! empty( $global_options['global_api_key'] ) ) {

		// Add an element to attach the Gigya Login UI to.
		echo '<div id="gigya-login"></div>';

		// Load custom Gigya login script.
		wp_enqueue_script( 'gigya_login_js', plugins_url( 'assets/scripts/gigya_login.js', __FILE__ ) );
		wp_enqueue_style( 'gigya_login_css', plugins_url( 'assets/styles/gigya_login.css', __FILE__ ) );

		// Parameters to be sent to the DOM.
		$params = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gigya-login-ajax-nonce' ),
		);

		// Load params to be available to client-side script.
		wp_localize_script( 'gigya_login_js', 'gigyaLoginParams', $params );
		require_once( GIGYA__PLUGIN_DIR . 'class/class.GigyaLoginAction.php' );
	}
}

// --------------------------------------------------------------------

add_action( 'wp_ajax_nopriv_gigya_login', 'ajax_gigya_login_callback' );
function ajax_gigya_login_callback() {

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'gigya-login-ajax-nonce' ) ) {
		die ( 'Busted!' );
	}

	$data           = $_POST['data'];
	$login_redirect = user_admin_url();
	$login_options  = get_option( GIGYA__SETTINGS_LOGIN );

	if ( ! empty ( $login_options['login_redirect'] ) ) {
		$login_redirect = $login_options['login_redirect'];
	}

	if ( ! is_user_logged_in() ) {
		$global_options = get_option( GIGYA__SETTINGS_GLOBAL );
		if ( SigUtils::validateUserSignature( $data['UID'], $data['timestamp'], $global_options['global_secret_key'], $data['signature'] ) ) {
			$gigya_user = $data['user'];

			// Check to see if the Gigya user is a WP user.
			if ( is_numeric( $gigya_user['UID'] ) && $data['isSiteUID'] === 'true' && is_object( $user = get_userdata( $gigya_user['UID']) )) {

				// Login the user.
				wp_clear_auth_cookie();
				wp_set_current_user ( $user->ID, $user->user_login );
				wp_set_auth_cookie  ( $user->ID );

				do_action( 'wp_login', $user->user_login );


			}
			else {
				// If the user isn't a WP user, try to register if allowed.
				$user_id = wp_create_user( $gigya_user['firstName'] . $gigya_user['lastName'], wp_generate_password(), $gigya_user['email'] );

				do_action( 'user_register', $user_id );
			}
		}
	}

	$response = json_encode( array( 'success' => true ) );
	header( "Content-Type: application/json" );
	echo $response;

	wp_safe_redirect( $login_redirect );

	exit;
}

// --------------------------------------------------------------------

/**
 * Hook script load.
 */
add_action( 'wp_enqueue_scripts', '_gigya_wp_enqueue_scripts_action' );
function _gigya_wp_enqueue_scripts_action() {


}

// --------------------------------------------------------------------

/**
 * Renders a default template.
 *
 * @param $template_file
 *   The filename of the template to render.
 * @param $variables
 *   A keyed array of variables that will appear in the output.
 *
 * @return void The output generated by the template.
 */
function _gigya_render_tpl( $template_file, $variables ) {
	// Extract the variables to a local namespace
	extract( $variables, EXTR_SKIP );

	// Start output buffering
	ob_start();

	// Include the template file
	include GIGYA__PLUGIN_DIR . '/' . $template_file;

	// End buffering and return its contents
	$output = ob_get_clean();
	echo $output;
}

// --------------------------------------------------------------------


/**
 * Loads JSON string from file.
 *
 * @param $file | relative path from Gigya plugin root.
 *              DO NOT include filename extension.
 *
 * @return bool|string
 */
function _gigya_get_json( $file ) {
	$path = GIGYA__PLUGIN_DIR . $file . '.json';
	$json = file_get_contents( $path );
	return ! empty( $json ) ? $json : FALSE;
}

// --------------------------------------------------------------------