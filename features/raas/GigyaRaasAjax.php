<?php

namespace Gigya\WordPress;

use Exception;
use Firebase\JWT\JWT;
use Gigya\CMSKit\GigyaApiHelper;
use Gigya\CMSKit\GigyaCMS;
use Gigya\CMSKit\GSApiException;
use Gigya\PHP\GSException;
use Gigya\PHP\SigUtils;

/**
 * @file
 * GigyaRaasAjax.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaRaasAjax {

	private $gigya_account;
	private $global_options;
	private $login_options;
	private $session_options;
	private $logger;

	public function __construct() {
		/* Get settings variables */
		$this->global_options  = get_option( GIGYA__SETTINGS_GLOBAL );
		$this->login_options   = get_option( GIGYA__SETTINGS_LOGIN );
		$this->session_options = get_option( GIGYA__SETTINGS_SESSION );
		$this->logger = new GigyaLogger();
	}

	/**
	 * This is SAP CDC login AJAX callback
	 *
	 * @throws Exception In cases where getAccountInfo returns an error
	 */
	public function init() {
		/* Get the data from the client (AJAX). */
		$data = $_POST['data'];

		/* Trap for login users */
		if ( is_user_logged_in() and ( ! is_multisite() ) ) {
			$getAccountInfoError = array( 'msg' => __( 'You are already logged in' ) );
			$this->logger->debug( 'Login failed: ' . $getAccountInfoError, $data['UID'] );
			wp_send_json_error( $getAccountInfoError );
		}


		/* Check Gigya's signature validation */
		$raas_validate_error = array( 'msg' => __( 'RaaS: There is a problem validating your user' ) );
		$is_sig_valid     = false;

		if ( ! empty( $data['id_token'] ) and $this->global_options['auth_mode'] === 'user_rsa' ) {
			$gigya_api_helper = new GigyaApiHelper( GIGYA__API_KEY, GIGYA__USER_KEY, GIGYA__AUTH_KEY, GIGYA__API_DOMAIN, 'user_rsa' );
			try {
				$is_sig_valid = $gigya_api_helper->validateJwtAuth( $data['UID'], $data['id_token'] );
			} catch ( Exception $e ) {
				$this->logger->debug( 'Login failed: ' . $raas_validate_error['msg'], $data['UID'] );
				wp_send_json_error( $raas_validate_error );
			}
		} else {
			$gigya_api_helper = new GigyaApiHelper( GIGYA__API_KEY, GIGYA__USER_KEY, GIGYA__AUTH_KEY, GIGYA__API_DOMAIN );
			try {
				$is_sig_valid = $gigya_api_helper->validateUid( $data['UID'], $data['UIDSignature'], $data['signatureTimestamp'], 'raas' );
			} catch ( Exception $e ) {
				$this->logger->debug( 'Login failed: ' . $raas_validate_error['msg'], $data['UID'] );
				wp_send_json_error( $raas_validate_error );
			}
		}

		/* Gigya user validate trap */
		if ( !( $is_sig_valid ) ) {
			$this->logger->debug( 'Login failed: ' . $raas_validate_error['msg'], $data['UID'] );
			wp_send_json_error( $raas_validate_error );
		}

		/* Initialize Gigya account */
		$gigyaCMS            = new GigyaCMS();
		$getAccountInfoError = array( 'msg' => __( 'Oops! Something went wrong during your login process. Please try to login again.' ) );
		try {
			$this->gigya_account = $gigyaCMS->getAccount( $data['UID'] );
		} catch (Exception $e) {
			$this->logger->debug( 'Login failed: ' . $getAccountInfoError['msg'], $data['UID'] );
			wp_send_json_error( $getAccountInfoError );
		}
		if ( is_wp_error( $this->gigya_account ) ) {
			$this->logger->debug( 'Login failed: ' . $getAccountInfoError['msg'], $data['UID'] );
			wp_send_json_error( $getAccountInfoError );
		} else {
			$this->gigya_account = $this->gigya_account->getData()->serialize();
		}

		/* Initialize cookie */
		$this->updateGltExpCookie();

		/* Check if there is already a WP user with the same UID. Failing that, checks by email for backwards compatibility. */
		$wp_user = get_users( array(
			                      'meta_key'   => 'gigya_uid',
			                      'meta_value' => $data['UID'],
		) );
		if ( ! empty( $wp_user ) ) {
			$wp_user = $wp_user[0];
		} else {
			$login_setting              = get_option( GIGYA__SETTINGS_LOGIN );
			$uid_and_email_verification = array_key_exists( 'login_verification_mode', $login_setting ) ? ( $login_setting['login_verification_mode'] == 'uid_and_email' ) : true;
			if ( $uid_and_email_verification ) {
				$wp_user = get_user_by( 'email', $this->gigya_account['profile']['email'] );
			};
		};
		if ( ! empty( $wp_user ) )
		{
			$is_primary_user = $gigyaCMS->isPrimaryUser( $this->gigya_account['loginIDs']['emails'], strtolower( $wp_user->data->user_email ) );

			/*	If this user is not the primary user account in Gigya
				we delete the account (we don't want two different users with the same email) */
			if ( ! $is_primary_user ) {
				$msg = __( 'We found your email in our system.<br />Please use your existing account to login to the site, or create a new account using a different email address.' );

				$getAccountInfoError = array( 'msg' => $msg );
				$this->logger->debug( 'Login failed: We found your email in our system. Please use your existing account to login to the site, or create a new account using a different email address.', $data['UID'] );
				wp_send_json_error( $getAccountInfoError );
			}

			/* Log this user in */
			try {
				$this->login( $wp_user );
			} catch ( Exception $e ) {
				$this->logger->debug( 'Login failed: ' . 'Unable to log in.', $data['UID'] );
				$getAccountInfoError = array( 'msg' => __( 'Unable to log in.' ) );
				wp_send_json_error( $getAccountInfoError );
			}
		}
		else
		{
			$this->logger->debug( "The user is registered at SAP CDC, and will now be registered to WordPress.", $data['UID'] );
			/* Register new user */
			$this->register();
		}
		$this->logger->debug( "The user was logged in.", $data['UID'] );
		wp_send_json_success();
	}

	/**
	 * Login existing WP user.
	 *
	 * @param $wp_user
	 *
	 * @throws Exception
	 */
	public function login( $wp_user ) {
		/* Initialize Remember Me mode */
		$is_remember_me = ( ! empty( $_POST['data']['remember'] ) and filter_var( $_POST['data']['remember'], FILTER_VALIDATE_BOOLEAN ) );
		if ( $is_remember_me ) {
			_gigya_set_session_remember( filter_var( $_POST['data']['remember'], FILTER_VALIDATE_BOOLEAN ) );
		}

		/* Login procedure */
		wp_clear_auth_cookie();
		wp_set_current_user( $wp_user->ID );
		wp_set_auth_cookie( $wp_user->ID, $is_remember_me );
		_gigya_add_to_wp_user_meta( $this->gigya_account, $wp_user->ID );

		/* Hook for changing WP user metadata from Gigya's user */
		do_action( 'gigya_after_raas_login', $this->gigya_account, $wp_user );

		/* Do other login Implementations */
		do_action( 'wp_login', $wp_user->data->user_login, $wp_user );
	}

	/**
	 * Register new WP user from Gigya user
	 *
	 * @throws Exception
	 */
	private function register() {
		/* Register a new user to WP with params from Gigya */
		if ( isset( $this->gigya_account['profile']['username'] ) ) {
			$display_name = $this->gigya_account['profile']['username'];
			$name         = sanitize_user( $display_name, true );
		} else {
			$display_name = $this->gigya_account['profile']['firstName'] . '_' . $this->gigya_account['profile']['lastName'];
			$name         = sanitize_user( $display_name, true );
		}
		$email = $this->gigya_account['profile']['email'];

		/*	If the name of the new user already exists in the system,
			WP will reject the registration and return an error. to prevent this
			we attach an extra value to the name to make it unique. */
		$username_exist = username_exists( $name );
		if ( ! empty( $username_exist ) ) {
			$name .= uniqid( '-' );
		}

		/* Hook just before register new user from Gigya RaaS. */
		do_action( 'gigya_before_raas_register', $name, $email );

		$user_id = register_new_user( $name, $email );

		/* On registration error. */
		if ( ! empty( $user_id->errors ) ) {
			$msg = '';
			$log_message = '';

			foreach ( $user_id->errors as $error ) {
				foreach ( $error as $err ) {
					$msg .= $err . "\n";
					$log_message .= $err . ' ';
				}
			}
			$this->logger->debug( 'The user can\'t register: ' . strip_tags( $log_message ), $this->gigya_account['UID'] );
			/* Return JSON to client */
			wp_send_json_error( array( 'msg' => $msg ) );
		}
		wp_update_user( (object) array(
			'ID'           => $user_id,
			'display_name' => $display_name
		) ); /* If non-Latin characters are used in the first/last name, it will still use the correct display name */
		_gigya_add_to_wp_user_meta( $this->gigya_account, $user_id );

		/* Log the user in */
		$wp_user = get_userdata( $user_id );
		try {
			$this->login( $wp_user );
		} catch ( Exception $e ) {
			$prm = array( 'msg' => __( 'Unable to log in.' ) );
			$this->logger->debug( 'Login failed: ' . 'Unable to log in', $this->gigya_account['UID'] );
			wp_send_json_error( $prm );
		}
	}

	/**
	 * @param $data
	 *
	 * @throws GSApiException
	 * @throws GSException
	 * @throws Exception
	 */
	public function updateProfile( $data ) {
		if ( is_user_logged_in() ) {
			if ( ! array_key_exists( 'UIDSignature', $data ) ) {
				$this->logger->debug( "Updating profile failed: The UIDSignature is invalid.", $this->gigya_account['UID'] );
				wp_send_json_error();

			}
			$is_sig_validate = SigUtils::validateUserSignature(
				$data['UID'],
				$data['signatureTimestamp'],
				GIGYA__API_SECRET,
				$data['UIDSignature']
			);

			if ( $is_sig_validate ) {
				$gigyaCMS      = new GigyaCMS();
				$gigya_account = $gigyaCMS->getAccount( $data['UID'] );
				if ( ! is_wp_error( $gigya_account ) ) {
					_gigya_add_to_wp_user_meta( $gigya_account, get_current_user_id() );
				}
				$this->logger->debug( "Updating profile failed: " . $gigya_account->getErrorMessage(), $data['UID'] );

			}
			$this->logger->debug( "This user has updated their profile.", $data['UID'] );
		} else {

			$this->logger->debug( "A user tried to update a profile without having logged in.", $data['UID'] );
		}

	}

	public function getGltExpCookieExpiration( $session_type, $session_duration ) {
		switch ( $session_type ) {
			case GIGYA__SESSION_FOREVER: /* Keep session indefinitely */
				$expiration = strval( time() + ( 10 * YEAR_IN_SECONDS ) );
				break;
			case GIGYA__SESSION_DEFAULT: /* Remove GltExp */
				$expiration = 1;
				break;
			default: /* Session fixed or defined with expiration time */
				$expiration = strval( $_SERVER['REQUEST_TIME'] + intval( $session_duration ) );
				break;
		}

		return $expiration;
	}

	public function updateGltExpCookie() {
		if ( isset( $_COOKIE[ 'glt_' . GIGYA__API_KEY ] ) ) {
			if ( isset( $this->session_options['session_type_numeric'] ) ) {
				$session_type = ( _gigya_get_session_remember() ) ? intval( $this->session_options['remember_session_type_numeric'] ) : intval( $this->session_options['session_type_numeric'] );
			} else {
				$session_type = GIGYA__SESSION_SLIDING;
			}
			if ( isset( $this->session_options['session_duration'] ) ) {
				$session_duration = ( _gigya_get_session_remember() ) ? intval( $this->session_options['remember_session_duration'] ) : intval( $this->session_options['session_duration'] );
			} else {
				$session_duration = GIGYA__DEFAULT_COOKIE_EXPIRATION;
			}

			$glt_cookie = $_COOKIE[ 'glt_' . GIGYA__API_KEY ];
			$token      = ( ! empty( explode( '|', $glt_cookie )[0] ) ) ? explode( '|', $glt_cookie )[0] : null;

			$cookie_expiration = time() + ( 10 * YEAR_IN_SECONDS );
			if ( $session_type == GIGYA__SESSION_DEFAULT ) {
				$cookie_expiration = 1;
			}
			$expiration = $this->getGltExpCookieExpiration( $session_type, $session_duration );

			$gltexp_cookie           = isset( $_COOKIE[ 'gltexp_' . GIGYA__API_KEY ] ) ? $_COOKIE[ 'gltexp_' . GIGYA__API_KEY ] : '';
			$gltexp_cookie_timestamp = explode( '_', $gltexp_cookie )[0]; /* PHP 5.4+ */

			if ( ! $host = $_SERVER['SERVER_NAME'] ) {
				$host = $_SERVER['SERVER_ADDR'];
			}

			if ( ( empty( $gltexp_cookie_timestamp ) and $session_type === GIGYA__SESSION_SLIDING ) or ( time() < $gltexp_cookie_timestamp and $session_type < 0 ) ) {
				if ( ! empty( $token ) ) {
					$session_sig = $this->calcDynamicSessionSig(
						$token,
						$expiration,
						GIGYA__AUTH_MODE ?? 'user_secret',
						GIGYA__USER_KEY,
						GigyaApiHelper::decrypt( GIGYA__AUTH_KEY, SECURE_AUTH_KEY )
					);

					setrawcookie( 'gltexp_' . GIGYA__API_KEY, rawurlencode( $session_sig ), $cookie_expiration, '/', $host );
				}
			} elseif ( $session_type === GIGYA__SESSION_DEFAULT ) {
				setrawcookie( 'gltexp_' . GIGYA__API_KEY, '', $cookie_expiration, '/', $host );
			} /* Unset cookie */
		}
	}

	private function calcDynamicSessionSig( $token, $expiration, $auth_mode, $user_key, $auth_key ) {
		if ( $auth_mode and $auth_mode == 'user_rsa' ) {
			$payload = [
				'sub' => $token,
				'iat' => time(),
				'exp' => intval( $expiration ),
				'aud' => 'gltexp',
			];

			return JWT::encode( $payload, $auth_key, 'RS256', $user_key );
		} else {
			$unsigned_exp_string = utf8_encode( $token . "_" . $expiration . "_" . $user_key );
			$rawHmac             = hash_hmac( "sha1", utf8_encode( $unsigned_exp_string ), base64_decode( $auth_key ), true );
			$sig                 = base64_encode( $rawHmac );

			return $expiration . '_' . $user_key . '_' . $sig;
		}

	}
}
