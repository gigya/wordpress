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
		$this->gigya_user = $data['user'];

		// Check to see if the Gigya user is a WP user.
		if ( is_numeric( $this->gigya_user['UID'] ) && $data['isSiteUID'] === 'true' && is_object( $wp_user = get_userdata( $this->gigya_user['UID'] ) ) ) {

			// Login the user.
			$this->login( $wp_user );

		} else {

			// If the user isn't a WP user,
			// try to register if allowed.
			$this->register();

		}

		wp_send_json_success();

		exit;
	}

	/**
	 * Login existing WP user.
	 *
	 * @param $wp_user
	 */
	private function login( $wp_user ) {

		// Login procedure.
		wp_clear_auth_cookie();
		wp_set_current_user( $wp_user->ID, $wp_user->user_login );
		wp_set_auth_cookie( $wp_user->ID );

		// Set a session with Gigya's ID.
		$_SESSION['gigya_login_id'] = $this->gigya_user['UID'];

		// Do others login Implementations.
		do_action( 'wp_login', $wp_user->user_login );
	}

	/**
	 * Register new WP user from Gigya user.
	 */
	private function register() {

		// Register a new user to WP with params from Gigya.
		$name    = $this->gigya_user['firstName'] . ' ' . $this->gigya_user['lastName'];
		$user_id = register_new_user( $name, $this->gigya_user['email'] );

		$this->login( $user_id );

		// Do others register Implementations.
		do_action( 'user_register', $user_id );
	}


	/**
	 * Logout Gigya user.
	 */
	public function logout() {
	}


}