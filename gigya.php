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
define( 'GIGYA__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

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
	require_once( GIGYA__PLUGIN_DIR . 'class/class.GigyaLoginForm.php' );
	$gigyaLoginForm = new GigyaLoginForm;
	$gigyaLoginForm->init();
}

// --------------------------------------------------------------------

/**
 * Hook AJAX login.
 */
add_action( 'wp_ajax_gigya_login', '_gigya_ajax_login_action' );
add_action( 'wp_ajax_nopriv_gigya_login', '_gigya_ajax_login_action' );
function _gigya_ajax_login_action() {
	require_once( GIGYA__PLUGIN_DIR . 'class/class.GigyaLoginAction.php' );
	$gigyaLoginAction = new GigyaLoginAction;
	$gigyaLoginAction->init();
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
 * Hook login.
 */
add_action( 'wp_login', '_gigya_wp_login_action', 10, 2 );
function _gigya_wp_login_action( $user_login, $user ) {

}

// --------------------------------------------------------------------

/**
 * Hook register.
 */
add_action( 'user_register', '_gigya_user_register_action', 10, 1 );
function _gigya_user_register_action( $uid ) {
	if ( $_SESSION['gigya_login_id'] ) {
		$gigyaUser = new GigyaUser( $_SESSION['gigya_login_id'] );
		$gigyaUser->notifyRegistration( $uid );
	} else {
		$account   = get_userdata( $uid );
		$info      = array(
				'email'    => $account->data->user_email,
				'nickname' => $account->data->user_nickname,
		);
		$user_info = json_encode( $info );
		backNotify( $uid, TRUE, $user_info );
	}
}

// --------------------------------------------------------------------

/**
 * Logs user in to Gigya's service and optionally registers them.
 *
 * @param string  $uid
 *   The drupal User ID.
 * @param boolean $is_new_user
 *   Tell Gigya if we add a new user.
 * @param mixed   $user_info_json
 *   Extra info for the user.
 *
 * @see gigya_user_login()
 *
 * @return bool|null|string True if the notify login request succeeded or the error message from Gigya
 */
function backNotify( $uid, $is_new_user = FALSE, $user_info_json = NULL ) {

	// API params
	$apikey    = $this->global_options['global_api_key'];
	$secretkey = $this->global_options['global_secret_key'];

	// Gigya Service Request instance.
	$request = new GSRequest( $apikey, $secretkey, 'socialize.notifyLogin' );

	// Add user id.
	$request->setParam( "siteUID", $uid );

	// Set a new user flag if true.
	if ( ! empty( $is_new_user ) ) {
		$request->setParam( 'newUser', TRUE );
	}

	// Add user info if available.
	if ( ! empty( $user_info_json ) ) {
		$request->setParam( 'userInfo', $user_info_json );
	}

	// Send request.
	$response = $request->send();

	// If there an error, return error message.
	if ( $response->getErrorCode() !== 0 ) {
		return $response->getErrorMessage();
	}

	//Set  Gigya cookie.
	try {
		setcookie( $response->getString( "cookieName" ), $response->getString( "cookieValue" ), 0, $response->getString( "cookiePath" ), $response->getString( "cookieDomain" ) );
	} catch ( Exception $e ) {
		error_log( sprintf( 'error seting gigya cookie' ) );
		error_log( sprintf( 'error message : @error', array( '@error' => $e->getMessage() ) ) );
	}

	return TRUE;
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