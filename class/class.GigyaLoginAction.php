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

		// Get the data from the client.
		$data = $_POST['data'];

		// Trap for login users
		if ( is_user_logged_in() ) {
			$prm = array( 'msg' => __( 'There already a logged in user' ) );
			wp_send_json_error( $prm );
		}

		// Check Gigya's signature validation.
		$is_sig_validate = GigyaApi::sigValidate( $data );

		// Gigya user validate trap.
		if ( empty( $is_sig_validate ) ) {
			$prm = array( 'msg' => __( 'There a problem to validate your user' ) );
			wp_send_json_error( $prm );
		}

		// Initialize Gigya user.
		$this->gigya_user = $data['user'];

		// Set a session with Gigya's ID.
		$_SESSION['gigya_login_id'] = $this->gigya_user['UID'];

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
	 * @param $user_id
	 *
	 * @internal param $wp_user
	 */
	private function login( $wp_user ) {

		// Login procedure.
		wp_clear_auth_cookie();
		wp_set_current_user( $wp_user->ID );
		wp_set_auth_cookie( $wp_user->ID );

		// Do others login Implementations.
		do_action( 'wp_login', $wp_user->data->user_login, $wp_user );
	}

	/**
	 * Register new WP user from Gigya user.
	 */
	private function register() {

		// Register a new user to WP with params from Gigya.
		$name    = $this->gigya_user['firstName'] . ' ' . $this->gigya_user['lastName'];
		$user_id = register_new_user( $name, $this->gigya_user['email'] );

		// Login the user.
		$wp_user = get_userdata( $user_id );
		$this->login( $wp_user );

		// Do others register Implementations.
//		do_action( 'user_register', $user_id );
	}


	/**
	 * Logout Gigya user.
	 */
	public function logout() {
	}


}