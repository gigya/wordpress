<?php

/**
 * @file
 * GigyaRaasAjax.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaRaasAjax {

	private $gigya_account;

	public function __construct() {

		// Get settings variables.
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );
	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {

		// Get the data from the client (AJAX).
		$data = $_POST['data'];

		// Trap for login users
		if ( is_user_logged_in() ) {
			$prm = array( 'msg' => __( 'There already a logged in user' ) );
			wp_send_json_error( $prm );
		}

		// Check Gigya's signature validation.
		$is_sig_validate = SigUtils::validateUserSignature(
				$data['UID'],
				$data['signatureTimestamp'],
				GIGYA__API_SECRET,
				$data['UIDSignature']
		);

		// Gigya user validate trap.
		if ( empty( $is_sig_validate ) ) {
			$prm = array( 'msg' => __( 'There is a problem validating your user' ) );
			wp_send_json_error( $prm );
		}

		// Initialize Gigya account.
		$gigyaCMS            = new GigyaCMS();
		$this->gigya_account = $gigyaCMS->getAccount( $data['UID'] );
		if ( is_wp_error($this->gigya_account) ) {
			$prm = array( 'msg' => __( 'Oops! Something went wrong during your login process. Please try to login again.' ) );
			wp_send_json_error( $prm );
		}

		// Check if there is already a WP user with the same email.
		$wp_user = get_user_by( 'email', $this->gigya_account['profile']['email'] );
		if ( ! empty( $wp_user ) ) {

			$primary_user = $gigyaCMS->isPrimaryUser( $this->gigya_account['loginIDs']['emails'], $wp_user->data->user_email );

			// If this user is not the primary user account in Gigya
			// we delete the account (we don't want two different users with the same email)
			if ( empty( $primary_user ) ) {

				$gigyaCMS->deleteAccountByGUID( $this->gigya_account['UID'] );

				$providers = $gigyaCMS->getProviders( $this->gigya_account );

				$msg = sprintf( __( 'We found your email in our system.<br>Please login to your existing account using your <strong>%1$s</strong> identity.' ), $providers['primary'], $providers['secondary'] );

				$prm = array( 'msg' => $msg );
				wp_send_json_error( $prm );
			}

			// Login this user.
			$this->login( $wp_user );

		} else {

			// Register new user.
			$this->register();

		}

		wp_send_json_success();
	}

	/**
	 * Login existing WP user.
	 *
	 * @param $wp_user
	 */
	public function login( $wp_user ) {

		// Login procedure.
		wp_clear_auth_cookie();
		wp_set_current_user( $wp_user->ID );
		wp_set_auth_cookie( $wp_user->ID );
		_gigya_add_to_wp_user_meta($this->gigya_account['profile'], $wp_user->ID);
		// Hook for changing WP user metadata from Gigya's user.
		do_action( 'gigya_after_raas_login', $this->gigya_account, $wp_user );

		// Do other login Implementations.

		do_action( 'wp_login', $wp_user->data->user_login, $wp_user );

	}

	/**
	 * Register new WP user from Gigya user.
	 */
	private function register() {

		// Register a new user to WP with params from Gigya.
		if ( isset($this->gigya_account['profile']['username']) ) {
			$name = $this->gigya_account['profile']['username'];
		} else {
			$name  = $this->gigya_account['profile']['firstName'] . '_' . $this->gigya_account['profile']['lastName'];
		}
		$email = $this->gigya_account['profile']['email'];

		// If the name of the new user already exists in the system,
		// WP will reject the registration and return an error. to prevent this
		// we attach an extra value to the name to make it unique.
		$username_exist = username_exists( $name );
		if ( ! empty( $username_exist ) ) {
			$name .= uniqid( '-' );
		}

		// Hook just before register new user from Gigya RaaS.
		do_action( 'gigya_before_raas_register', $name, $email );

		$user_id = register_new_user( $name, $email );

		// On registration error.
		if ( ! empty( $user_id->errors ) ) {
			$msg = '';
			foreach ( $user_id->errors as $error ) {
				foreach ( $error as $err ) {
					$msg .= $err . "\n";
				}
			}

			// Return JSON to client.
			wp_send_json_error( array( 'msg' => $msg ) );
		}
		_gigya_add_to_wp_user_meta($this->gigya_account['profile'], $user_id);

		// Login the user.
		$wp_user = get_userdata( $user_id );
		$this->login( $wp_user );
	}

	public function updateProfile( $data ) {
		if ( is_user_logged_in() ) {
			$is_sig_validate = SigUtils::validateUserSignature(
				$data['UID'],
				$data['signatureTimestamp'],
				GIGYA__API_SECRET,
				$data['UIDSignature']
			);
			if ($is_sig_validate) {
				$gigyaCMS = new GigyaCMS();
				$gigya_account = $gigyaCMS->getAccount($data['UID']);
				if (!is_wp_error($gigya_account)) {
					_gigya_add_to_wp_user_meta( $gigya_account['profile'], get_current_user_id() );
				}
			}
		}
	}
}