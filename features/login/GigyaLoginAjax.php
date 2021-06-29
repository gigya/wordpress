<?php

namespace Gigya\WordPress;

use Exception;
use Gigya\CMSKit\GigyaApiHelper;

/**
 * @file
 * GigyaLogin
 *Ajax.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaLoginAjax {

	protected $global_options;
	protected $login_options;
	protected $gigya_user;
	protected $logger;

	public function __construct() {
		/* Get settings variables */
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$this->logger = new GigyaLogger();

	}

	/**
	 * This is Gigya login AJAX callback
	 *
	 * @throws Exception
	 */
	public function init() {

		/* Get the data from the client (AJAX) */
		$data = $_POST['data'];

		/* Trap for login users */
		if ( is_user_logged_in() ) {
			$this->logger->debug( 'Login failed: There is already a logged in user', $data['UID'] );
			wp_send_json_error( array( 'msg' => __( 'There is already a logged in user' ) ) );
		}

		/* Check Gigya's signature validation */
		$login_validate_error = 'Login: There was a problem validating your user';
		$gigya_api_helper     = new GigyaApiHelper( GIGYA__API_KEY, GIGYA__USER_KEY, GIGYA__AUTH_KEY, GIGYA__API_DOMAIN );
		$is_sig_validate      = false;

		try
		{
			$is_sig_validate = $gigya_api_helper->validateUid( $data['UID'], $data['UIDSignature'], $data['signatureTimestamp'], 'login' );
		}
		catch ( Exception $e )
		{
			$this->logger->debug( 'Login failed: ' . $login_validate_error, $data['UID'] );

			wp_send_json_error( array( 'msg' => __( $login_validate_error ) ) );
		}

		/* Gigya user validate trap */
		if ( !( $is_sig_validate ) ) {
			$this->logger->debug( 'Login failed: ' . $login_validate_error, $data['UID'] );
			wp_send_json_error( array( 'msg' => __( $login_validate_error ) ) );
		}

		/* Initialize Gigya user */
		$this->gigya_user = $data['user'];

		/* Checking if the Gigya UID is a number.
		* When the Gigya UID is a number, it means
		* we already notifyRegistration for Gigya
		* and the Gigya UID is the WP UID. */
		if ( is_numeric( $this->gigya_user['UID'] ) && $this->gigya_user['isSiteUID'] == true && ( is_object( $wp_user = get_userdata( $this->gigya_user['UID'] ) ) ) ) {
			/* Log the user in */
			$this->login( $wp_user );
		} else {
			/* There might be a user who never verified his email.
			 * So we are looking for a user who has 'gigya_uid' meta
			 * with the value of the original (NOT-number) Gigya UID. */
			$users = get_users( 'meta_key=gigya_uid&meta_value=' . $this->gigya_user['UID'] );

			if ( ! empty( $users ) ) {
				$this->logger->debug( "The user was logged in.", $this->gigya_user['UID'] );
				/* If there one we return the login form to client */
				wp_send_json_success( array(
					'type' => 'form',
					'html' => $this->emailVerifyForm()
				) );
			} else {
				/* We now sure there no user in WP records connected
				 * to this Gigya's UID. Lets try to register the user. */
				$this->logger->debug( "The user is registered at SAP CDC, and will now be registered to WordPress.", $this->gigya_user['UID'] );
				$this->register();
			}
		}
		$this->logger->debug( "The user was logged in.", $this->gigya_user['UID'] );
		wp_send_json_success();
	}

	/**
	 * Login existing WP user.
	 *
	 * @param $wp_user
	 */
	public function login( $wp_user ) {
		/* Login procedure */
		wp_clear_auth_cookie();
		wp_set_current_user( $wp_user->ID );
		wp_set_auth_cookie( $wp_user->ID );

		/* Hook for changing WP user metadata from Gigya's user */
		do_action( 'gigya_after_social_login', $this->gigya_user, $wp_user );

		/* Do others login implementations */
		do_action( 'wp_login', $wp_user->data->user_login, $wp_user );
	}

	/**
	 * Register new WP user from Gigya user.
	 *
	 * @throws Exception
	 */
	private function register() {
		// Before we insert new user to the system, we check
		// if there is a user with the same email in our DB.
		// When there is we ask the user login in the
		// previous account and link it to the new one.
		$email_exists = email_exists( $this->gigya_user['email'] );
		if ( ! empty( $email_exists ) ) {

			// Return JSON with login form to client.
			wp_send_json_success( array(
					'type' => 'form',
					'html' => $this->linkAccountForm( $email_exists )
			) );
		}

		// If the name of the new user already exists in the system,
		// WP will reject the registration and return an error. to prevent this
		// we attach an extra value to the name to make it unique.
		$username_exist = username_exists( $this->gigya_user['nickname'] );
		if ( ! empty( $username_exist ) ) {
			$this->gigya_user['nickname'] .= uniqid( '-' );
		}

		// When the admin checked to
		// show the entire registration form to the user.
		if ( ! empty( $this->login_options['registerExtra'] ) ) {
			$this->registerExtraForm();
		}

		// Register a new user to WP with params from Gigya.
		$name  = $this->gigya_user['nickname'];
		$email = $this->gigya_user['email'];

		// Hook just before register new user from Gigya Social Login.
		do_action( 'gigya_before_social_register', $name, $email );

		$user_id = register_new_user( $name, $email );

		// On registration error.
		if ( ! empty( $user_id->errors ) ) {
			$msg         = '';
			$log_message = '';
			foreach ( $user_id->errors as $error ) {
				foreach ( $error as $err ) {
					$log_message .= $err . ' ';
					$msg         .= $err . PHP_EOL;
				}
			}
			$this->logger->debug( 'The user can\'t register: ' . strip_tags( $log_message ), $this->gigya_user['UID'] );
			// Return JSON to client.
			wp_send_json_error( array( 'msg' => $msg ) );
		}
		// map user social fields to WordPress user
		_gigya_add_to_wp_user_meta($this->{"gigya_user"}, $user_id);

		$wp_user = get_userdata( $user_id );

		// If we got here, the user is already registered.
		// But if we have the 'email_not_verified' flag turned on,
		// we can't auto login, and we need to verify the email first.
		if ( ! empty( $this->gigya_user['email_not_verified'] ) ) {
			// Return JSON with login form to client.
			$this->logger->debug( 'The user has been registered.', $this->gigya_user['UID']);

			wp_send_json_success( array(
				'type' => 'form',
				'html' => $this->emailVerifyForm()
			) );
		}

		// Finally, let's login the user.
		$this->login( $wp_user );
	}

	/**
	 * AJAX submission of custom login forms.
	 */
	public static function customLogin() {
		$logger = new GigyaLogger();
		parse_str( $_POST['data'], $data );

		$creds = array(
			'user_login'    => $data['log'],
			'user_password' => $data['pwd']
		);

		$user = wp_signon( $creds );

		// On login error.
		if ( isset( $user->errors ) ) {
			$msg = '';
			$error_log = '';
			foreach ( $user->errors as $error ) {
				foreach ( $error as $err ) {
					$error_log .= $err . ' ';
					$msg .= $err . "\n";
				}
			}
			$logger->debug( "Login failed: " . $error_log );
			// Return JSON to client.
			wp_send_json_error( array( 'msg' => $msg ) );
		} else {

			$logger->debug( "Current user was logged in." );
			wp_send_json_success();
		}
	}

	/*************************/
	//         Forms         //
	/*************************/

	/**
	 * Generate form for email verify.
	 *
	 * @return string
	 */
	private function emailVerifyForm() {
		$output = '';
		$output .= '<form id="email-verify-form">';

		// Set form elements.
		$form            = array();
		$form['message'] = array(
				'markup' => __( 'A verification email has been sent to you with a password to activate your account.' ) . '<br>' . __( 'Please enter below the password you have received in the verification email.' ) . '<br><br>'
		);
		$form['log']     = array(
				'type'  => 'hidden',
				'label' => __( 'Username' ),
				'value' => $this->gigya_user['nickname'],
		);

		$form['pwd'] = array(
				'type'  => 'password',
				'label' => __( 'Password' ),
		);

		$form['form_name'] = array(
				'type'  => 'hidden',
				'value' => 'loginform-gigya-email-verify',
		);

		$form['gigyaUID'] = array(
				'type'  => 'hidden',
				'value' => $this->gigya_user['UID'],
		);

		// Render form elements.
		$output .= _gigya_form_render( $form );
		$output .= '<input type="button" id="gigya-submit" class="button button-primary button-large" value="Submit" />';
		$output .= '</form>';

		return $output;
	}

	/**
	 * Generate form for link accounts.
	 *
	 * @param $uid
	 *
	 * @return string
	 */
	private function linkAccountForm( $uid ) {
		$wp_user      = get_userdata( $uid );
		$wp_user_name = $wp_user->data->user_login;
        $forgot_pass_link =  wp_lostpassword_url( get_permalink() );

		$output = '';
		$output .= '<form id="link-accounts-form">';

		// Set form elements.
		$form            = array();
		$form['message'] = array(
				'markup' => __( "<h3>Already a Member:</h3>
								<p>We found your email: <strong>{$this->gigya_user['email']}</strong> in our system</p>
								<p>Please provide your site password to link to your existing account</p><br><br>" )
        );
		$form['log']     = array(
				'type'  => 'hidden',
				'label' => __( 'Username' ),
				'value' => $wp_user_name,
				'desc'  => __( 'Enter your' ) . ' ' . get_option( 'blogname' ) . ' ' . __( 'username' )
		);
		$form['pwd']       = array(
				'type'  => 'password',
				'label' => __( 'Password' ),
				'desc'  => __( 'Enter your password.' ),
		);
		$form['form_name'] = array(
				'type'  => 'hidden',
				'value' => 'loginform-gigya-link-account',
		);
		$form['gigyaUID']  = array(
				'type'  => 'hidden',
				'value' => $this->gigya_user['UID'],
		);

		// Render form elements.
		$output .= _gigya_form_render( $form );
        $output .= "<a class='forgot_pass' href={$forgot_pass_link}>Forgot password</a>";
		$output .= '<input type="button" id="gigya-submit" class="button button-primary button-large" value="Log In" />';
		$output .= '</form>';

		return $output;
	}

	/**
	 * Deal with missing fields on registration.
	 */
	private function registerExtraForm() {

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
				'value' => _gigParam( $this->gigya_user, 'nickname', '' )
		);
		$form['user_email'] = array(
				'type'  => 'text',
				'id'    => 'user_email',
				'label' => __( 'E-mail' ),
				'value' => _gigParam( $this->gigya_user, 'email', '' )
		);
		$form['form_name']  = array(
				'type'  => 'hidden',
				'value' => 'registerform-gigya-extra',
		);
		$form['gigyaUID']   = array(
				'type'  => 'hidden',
				'value' => _gigParam( $this->gigya_user, 'UID', '' )
		);

		// Render form elements.
		$output .= _gigya_form_render( $form );

		// Get other plugins register form implementation.
		do_action( 'register_form' );
		$extra_fields = ob_get_clean();
		if ( ! empty( $extra_fields ) ) {
			$output .= $extra_fields;
		}

		// Add submit button.
		$output .= '<input type="submit" id="gigya-submit" class="button button-primary button-large" value="' . $submit_value . '">';
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

	}
}