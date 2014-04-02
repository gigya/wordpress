<?php

/**
 * @file
 * GigyaLoginAjax.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaLoginAjax {

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
			wp_send_json_error( array( 'msg' => __( 'There already a logged in user' ) ) );
			exit;
		}

		// Check Gigya's signature validation.
		$is_sig_validate = SigUtils::validateUserSignature(
				$data['UID'],
				$data['timestamp'],
				GIGYA__API_SECRET,
				$data['signature']
		);

		// Gigya user validate trap.
		if ( empty( $is_sig_validate ) ) {
			wp_send_json_error( array( 'msg' => __( 'There a problem to validate your user' ) ) );
			exit;
		}

		// Initialize Gigya user.
		$this->gigya_user = $data['user'];

		// Set a session with Gigya's ID.
		$_SESSION['gigya_uid'] = $this->gigya_user['UID'];

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

		// Before we insert new user to the system, we check
		// if there a user with the same email in our DB.
		// When there is we ask the user login in the
		// previous account and link it to the new one.
		$email_exists = email_exists( $this->gigya_user['email'] );
		if ( ! empty( $email_exists ) ) {

			// Return JSON with login form to client.
			wp_send_json_success( array(
					'type' => 'form',
					'html' => $this->linkAccountForm(
									$email_exists,
									$this->gigya_user['UID']
							) ) );
			exit;
		}

		// If the name of the new user is already exist in the system,
		// WP will reject the registration and return an error. to prevent this
		// we attach an extra value to the name to make it unique.
		$username_exist = username_exists( $this->gigya_user['nickname'] );
		if ( ! empty( $username_exist ) ) {
			$this->gigya_user['nickname'] = $this->gigya_user['nickname'] . ' ' . uniqid();
		}

		// When there missing email or the admin checked to
		// show the entire registration form to the user.
		if ( $this->login_options['login_show_reg'] ) {
			$this->registerExtra();
		}

		// Register a new user to WP with params from Gigya.
		$name    = $this->gigya_user['nickname'];
		$email   = $this->gigya_user['email'];
		$user_id = register_new_user( $name, $email );
		$wp_user = get_userdata( $user_id );

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
			exit;
		}

		// If we got to here, the user is already register.
		// But if we have the 'email_not_verified' flag turn on,
		// we can't auto login, and we need to verify the email first.
		if ( ! empty( $this->gigya_user['email_not_verified'] ) ) {

			// Return JSON with login form to client.
			wp_send_json_success( array(
					'type' => 'form',
					'html' => wp_login_form( array(
							'echo'           => false,
							'value_username' => $wp_user->data->user_login
					) ) ) );
			exit;

		}

		// Finally, let's login the user.
		$this->login( $wp_user );
	}

	/**
	 * Generate form for link accounts.
	 *
	 * @param        $account
	 * @param null   $gigya_uid
	 *
	 * @return string
	 */
	private function linkAccountForm( $account, $gigya_uid = null ) {

		$output = '';
		$output .= '<form name="loginform" id="loginform" action="' . site_url( 'wp-login.php', 'login_post' ) . '" method="post">';

		// Set form elements.
		$form            = array();
		$form['message'] = array(
				'markup' => __( 'Your Email address' ) . ': ' . $account['email'] . ' ' . __( 'already exists. If you have previously registered, please login with your site credentials to link the accounts. Otherwise, please use a different Email address' ) . '<br><br>'
		);
		$form['log']     = array(
				'type'  => 'text',
				'label' => __( 'Username' ),
				'value' => $account['nickname'],
				'desc'  => __( 'Enter your' ) . ' ' . get_option( 'blogname' ) . ' ' . __( 'username' )
		);

		$form['pwd']       = array(
				'type'  => 'text',
				'label' => __( 'Password' ),
				'desc'  => __( 'Enter your password.' ),
		);
		$form['form_name'] = array(
				'type'  => 'hidden',
				'value' => 'loginform-gigya-link-account',
		);
		$form['gigya_uid'] = array(
				'type'  => 'hidden',
				'value' => $gigya_uid,
		);

		// Render form elements.
		$output .= _gigya_form_render( $form );
		$output .= '<input type="submit" name="wp-submit" id="gigya-submit" class="button button-primary button-large" value="Log In" />';
		$output .= '</form>';

		return $output;
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
				'value' => getParam( $this->gigya_user['nickname'], '' )
		);
		$form['user_email'] = array(
				'type'  => 'text',
				'id'    => 'user_email',
				'label' => __( 'E-mail' ),
				'value' => getParam( $this->gigya_user['email'], '' )
		);
		$form['gigyaUID']   = array(
				'type'  => 'hidden',
				'value' => getParam( $this->gigya_user['UID'], '' )
		);

		// Render form elements.
		$output .= _gigya_form_render( $form );

		// Get other plugins register form implementation.
		$output .= do_action( 'register_form' );

		// Add submit button.
		$output .= '<input type="submit" name="wp-submit" id="gigya-submit" class="button button-primary button-large" value="' . $submit_value . '">';
		$output .= '</form>';

		// Tokens replace.
		do_shortcode( $output );

		// Set a return array.
		$ret = array(
				'type' => 'form',
				'html' => $output,
		);

		// Return JSON to client.
		wp_send_json_success( $ret );

		exit;

	}
}