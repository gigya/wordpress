<?php

/**
 * @file
 * class.GigyaLoginAction.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaRaasLoginAction {

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
				$this->global_options['global_secret_key'],
				$data['UIDSignature']
		);

		// Gigya user validate trap.
		if ( empty( $is_sig_validate ) ) {
			$prm = array( 'msg' => __( 'There a problem to validate your user' ) );
			wp_send_json_error( $prm );
		}

		// Initialize Gigya user.
		$this->gigya_user = $data['user'];

		// Set a session with Gigya's ID.
		$_SESSION['gigya_uid'] = $this->gigya_user['UID'];

		$user_email = $data['profile']['email'];
		if ( empty( $user_email ) ) {
			$prm = array( 'msg' => __( 'Email address is required by Drupal and is missing, please contact the site administrator' ) );
			wp_send_json_error( $prm );
		}

		$wp_user = get_user_by( 'email', $user_email );
		if ( ! empty( $wp_user ) ) {
			$req_params = array(
					'UID'     => $data['UID'],
					'include' => 'loginIDs'
			);

			$api = new GigyaApi( $data['UID'] );
			$res = $api->call( 'accounts.getAccountInfo', $req_params );

			$primary_user = $this->isPrimaryUser( $res, $user_email );

			// If this user is not the primary user account in Gigya
			// we delete the account (we don't want two different users with the same email)
			if ( empty( $primary_user ) ) {

				$api->call( 'accounts.deleteAccount', array( 'UID' => $data['UID'] ) );

				// Get info about the primary account.
				$query        = 'select loginProvider from accounts where loginIDs.emails = ' . $user_email;
				$search_res   = $api->call( 'accounts.search', array( 'query' => $query ) );
				$p_provider   = $search_res['results'][0]['loginProvider'];
				$sec_provider = $res['loginProvider'];

				$msg = sprintf( __( "We found your email in our system.<br>Please login to your existing account using your <strong>%s</strong> identity.<br>
            If you wish to link your account with your <strong>%s</strong> identity - after logging-in, please go to your profile page and click the <strong>%s</strong> button.",
						array( $p_provider, $sec_provider, $sec_provider ) ) );

				$prm = array( 'msg' => $msg );
				wp_send_json_error( $prm );

				exit;
			}

			global $raas_login;
			$raas_login                 = TRUE;
			$_SESSION['gigya_raas_uid'] = $data['UID'];
			$this->login( $wp_user );

		} else {

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
		$name  = $this->gigya_user['email'];
		$email = $this->gigya_user['email'];

		$user_id = register_new_user( $name, $email );

		// Login the user.
		$wp_user = get_userdata( $user_id );
		$this->login( $wp_user );
	}

	/**
	 * Deal with missing fields on registration.
	 */
	private function registerExtra() {

		// Set submit button value.
		$submit_value = sprintf( __( 'Register %s' ), ! empty( $this->gigya_user['loginProvider'] ) ? ' ' . __( 'with' ) . ' ' . $this->gigya_user['loginProvider'] : '' );
		$output       = '';

		// Set form.
		$output .= '<form name="registerform" class="gigya-register-extra" id="registerform" action="' . wp_registration_url() . '" method="post">';
		$output .= '<h4 class="title">' . __( 'Please fill required field' ) . '</h4>';

		// Set form elements.
		$form               = array();
		$form['user_login'] = array(
				'type'  => 'text',
				'id'    => 'user_login',
				'label' => __( 'Username' ),
				'value' => ! empty( $this->gigya_user['nickname'] ) ? $this->gigya_user['nickname'] : '',
		);
		$form['user_email'] = array(
				'type'  => 'text',
				'id'    => 'user_email',
				'label' => __( 'E-mail' ),
				'value' => ! empty( $this->gigya_user['email'] ) ? $this->gigya_user['email'] : '',
		);

		// Render form elements.
		$output .= _gigya_form_render( $form );

		// Get other plugins register form implementation.
		$output .= do_action( 'register_form' );
		$output .= '<input type="hidden" name="gigyaUID" value="' . $this->gigya_user['UID'] . '">';

		// Add submit button.
		$output .= '<input type="submit" name="wp-submit" id="gigya-submit" class="button button-primary button-large" value="' . $submit_value . '">';
		$output .= '</form>';

		// Tokens replace.
		do_shortcode( $output );

		// Set a return array.
		$ret = array(
				'type' => 'register_form',
				'html' => $output,
		);

		// Return JSON to client.
		wp_send_json_success( $ret );

		exit;
	}

	/**
	 * Checks if this email is the primary user email
	 *
	 * @param $userInfo the user info from accounts.getUserInfo api call
	 * @param $email
	 *
	 * @return bool
	 */
	public static function isPrimaryUser( $userInfo, $email ) {

		foreach ( $userInfo['loginIDs'] as $email_array ) {
			if ( in_array( $email, $email_array ) ) {
				return TRUE;
			}
		}

		return FALSE;

	}

}