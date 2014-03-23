<?php

/**
 * @file
 * class.GigyaLoginAction.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaLoginAction {

	public function __construct() {
		// Get settings variables.
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );
	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {

		// Check to see if the submitted nonce matches with the
		// generated nonce we created earlier.
		check_ajax_referer( 'gigya-login-ajax-nonce', 'nonce' );

		$data = $_POST['data'];

		// Trap for login users
		if ( is_user_logged_in() ) {
			$prm = array( 'msg' => __( 'There already a logged in user' ) );
			wp_send_json_error( $prm );
		}

		// Gigya user validate trap.
		if ( false == SigUtils::validateUserSignature( $data['UID'], $data['timestamp'], $this->global_options['global_secret_key'], $data['signature'] ) ) {
			$prm = array( 'msg' => __( 'There a problem to validate your user' ) );
			wp_send_json_error( $prm );
		}

		// initialize Gigya user user.
		$gigya_user = $data['user'];

		// Check to see if the Gigya user is a WP user.
		if ( is_numeric( $gigya_user['UID'] ) && $data['isSiteUID'] === 'true' && is_object( $wp_user = get_userdata( $gigya_user['UID'] ) ) ) {

			// Login the user.
			$this->login( $wp_user );

		} else {

			// If the user isn't a WP user,
			// try to register if allowed.
			$this->register( $gigya_user );

		}

		wp_send_json_success();

		exit;
	}

	/**
	 * Login existing WP user.
	 *
	 * @param $wp_user
	 */
	public function login( $wp_user ) {
		wp_clear_auth_cookie();
		wp_set_current_user( $wp_user->ID, $wp_user->user_login );
		wp_set_auth_cookie( $wp_user->ID );

		do_action( 'wp_login', $wp_user->user_login );
	}

	/**
	 * Register new WP user from Gigya user.
	 *
	 * @param $gigya_user
	 */
	public function register( $gigya_user ) {
		$name    = $gigya_user['firstName'] . ' ' . $gigya_user['lastName'];
		$user_id = register_new_user( $name, $gigya_user['email'] );

		do_action( 'user_register', $user_id );
	}


	/**
	 * Logout Gigya user.
	 */
	public function logout() {}

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
	function backNotify($uid, $is_new_user = FALSE, $user_info_json = NULL) {

		// API params
		$apikey = $this->global_options['global_api_key'];
		$secretkey = $this->global_options['global_secret_key'];

		// Gigya Service Request instance.
		$request = new GSRequest($apikey, $secretkey, 'socialize.notifyLogin');

		// Add user id.
		$request->setParam("siteUID", $uid);

		// Set a new user flag if true.
		if (!empty($is_new_user)) {
			$request->setParam('newUser', TRUE);
		}

		// Add user info if available.
		if (!empty($user_info_json)) {
			$request->setParam('userInfo', $user_info_json);
		}

		// Send request.
		$response = $request->send();

		// If there an error, return error message.
		if ($response->getErrorCode() !== 0) {
			return $response->getErrorMessage();
		}

		//Set  Gigya cookie.
		try {
			setcookie($response->getString("cookieName"), $response->getString("cookieValue"), 0, $response->getString("cookiePath"), $response->getString("cookieDomain"));
		}
		catch (Exception $e) {
			error_log(sprintf('error seting gigya cookie'));
			error_log(sprintf('error message : @error', array('@error' => $e->getMessage())));
		}

		return TRUE;
	}
}