<?php

/**
 * @file
 * class.GigyaRaasAction.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaRaasAction {

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
			exit;
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
			$prm = array( 'msg' => __( 'There a problem to validate your user' ) );
			wp_send_json_error( $prm );
			exit;
		}

		// Initialize Gigya user.
//		$account             = new GigyaAccount( $data['UID'] );
//		$this->gigya_account = $account->getAccount();

		$gigya               = new GigyaCMS( GIGYA__API_KEY, GIGYA__API_SECRET );
		$this->gigya_account = $gigya->getAccount( $data['UID'] );

		// @todo Do we need this check - or we can count on what we get from Gigya?
		if ( empty( $this->gigya_account['profile']['email'] ) ) {
			$prm = array( 'msg' => __( 'Email address is required by Drupal and is missing, please contact the site administrator' ) );
			wp_send_json_error( $prm );
			exit;
		}

		// Check if there already WP user with the same email.
		$wp_user = get_user_by( 'email', $this->gigya_account['profile']['email'] );
		if ( ! empty( $wp_user ) ) {

			$primary_user = $account->isPrimaryUser( $this->gigya_account['loginIDs']['emails'], $wp_user->data->user_email );

			// If this user is not the primary user account in Gigya
			// we delete the account (we don't want two different users with the same email)
			if ( empty( $primary_user ) ) {

				$account->delete( $this->gigya_account['UID'] );

				$providers = $account->getProviders( $this->gigya_account );

				$msg = sprintf( __( "We found your email in our system.<br>Please login to your existing account using your <strong>%s</strong> identity.<br>
            If you wish to link your account with your <strong>%s</strong> identity - after logging-in, please go to your profile page and click the <strong>%s</strong> button.",
						array( $providers['primary'], $providers['secondary'], $providers['secondary'] ) ) );

				$prm = array( 'msg' => $msg );
				wp_send_json_error( $prm );
				exit;
			}

			// Login this user.
			$this->login( $wp_user );

		} else {

			// Register new user.
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
	public static function login( $wp_user ) {

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
		$name  = $this->gigya_account['profile']['firstName'] . ' ' . $this->gigya_account['profile']['lastName'];
		$email = $this->gigya_account['profile']['email'];

		$user_id = register_new_user( $name, $email );

		// Login the user.
		$wp_user = get_userdata( $user_id );
		$this->login( $wp_user );
	}
}