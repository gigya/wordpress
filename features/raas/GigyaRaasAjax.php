<?php

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

	public function __construct() {
		// Get settings variables.
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$this->session_options  = get_option( GIGYA__SETTINGS_SESSION );
	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {
		/* Get the data from the client (AJAX). */
		$data = $_POST['data'];

		/* Trap for login users */
		if ( is_user_logged_in() ) {
			$prm = array( 'msg' => __( 'You are already logged in' ) );
			wp_send_json_error( $prm );
		}

		/* Check Gigya's signature validation */
		$gigya_api_helper = new GigyaApiHelper(GIGYA__API_KEY, GIGYA__USER_KEY, GIGYA__API_SECRET, GIGYA__API_DOMAIN);
		$is_sig_validate = $gigya_api_helper->validateUid($data['UID'], $data['UIDSignature'], $data['signatureTimestamp']);

		/* Gigya user validate trap */
		if ( !( $is_sig_validate ) ) {
			$prm = array( 'msg' => __( 'RaaS: There is a problem validating your user' ) );
			wp_send_json_error( $prm );
		}

		/* Initialize Gigya account */
		$gigyaCMS            = new GigyaCMS();
		$this->gigya_account = $gigyaCMS->getAccount( $data['UID'] );
		if ( is_wp_error($this->gigya_account) ) {
			$prm = array( 'msg' => __( 'Oops! Something went wrong during your login process. Please try to login again.' ) );
			wp_send_json_error( $prm );
		}
		else
			$this->gigya_account = $this->gigya_account->getData()->serialize();

		/* Initialize cookie */
		$this->updateGltExpCookie();

		/* Check if there is already a WP user with the same UID. Failing that, checks by email for backwards compatibility. */
		$wp_user = get_users(array(
								'meta_key' => 'gigya_uid',
								'meta_value' => $data['UID'],
							 ));
		if (!empty($wp_user))
			$wp_user = $wp_user[0];
		else /* Comment this ELSE statement to verify *only* by UID */
			$wp_user = get_user_by( 'email', $this->gigya_account['profile']['email'] );

		if ( ! empty( $wp_user ) )
		{
			$is_primary_user = $gigyaCMS->isPrimaryUser( $this->gigya_account['loginIDs']['emails'], strtolower($wp_user->data->user_email) );

			// If this user is not the primary user account in Gigya
			// we delete the account (we don't want two different users with the same email)
			if ( !$is_primary_user ) {
				$msg =  __( 'We found your email in our system.<br />Please use your existing account to login to the site, or create a new account using a different email address.' );

				$prm = array( 'msg' => $msg );
				wp_send_json_error( $prm );
			}

			/* Log this user in */
			$this->login( $wp_user );
		}
		else
		{
			/* Register new user */
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
		_gigya_add_to_wp_user_meta($this->gigya_account, $wp_user->ID);
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

		/* Hook just before register new user from Gigya RaaS. */
		do_action( 'gigya_before_raas_register', $name, $email );

		$user_id = register_new_user( $name, $email );

		/* On registration error. */
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
		_gigya_add_to_wp_user_meta($this->gigya_account, $user_id);

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
					_gigya_add_to_wp_user_meta( $gigya_account, get_current_user_id() );
				}
			}
		}
	}

	public function updateGltExpCookie() {
		if (isset($_COOKIE['glt_'.GIGYA__API_KEY]))
		{
			if (!is_array($this->session_options))
				$this->session_options = array('session_type_numeric' => GIGYA__SESSION_SLIDING, 'session_duration' => GIGYA__DEFAULT_COOKIE_EXPIRATION);

			$session_type = intval($this->session_options['session_type_numeric']);
			$session_duration = $this->session_options['session_duration'];

			$glt_cookie = $_COOKIE['glt_'.GIGYA__API_KEY];
			$token = (!empty(explode('|', $glt_cookie)[0])) ? explode('|', $glt_cookie)[0] : null; /* PHP 5.4+ */

			$cookie_expiration = time() + (10 * YEAR_IN_SECONDS);
			switch ($session_type)
			{
				case GIGYA__SESSION_FOREVER: /* Keep session indefinitely */
					$expiration = strval(time() + (10 * YEAR_IN_SECONDS));
					break;
				case GIGYA__SESSION_DEFAULT: /* Remove GltExp */
					$expiration = 1;
					$cookie_expiration = 1;
					break;
				default: /* Session defined with expiration time */
					$expiration = strval($_SERVER['REQUEST_TIME'] + intval($session_duration));
					break;
			}

			$gltexp_cookie = isset($_COOKIE['gltexp_' . GIGYA__API_KEY]) ? $_COOKIE['gltexp_' . GIGYA__API_KEY] : '';
			$gltexp_cookie_timestamp = explode('_', $gltexp_cookie)[0]; /* PHP 5.4+ */
			if (!$host = $_SERVER['SERVER_NAME']) {
				$host = $_SERVER['SERVER_ADDR'];
			}
			if ((empty($gltexp_cookie_timestamp) and $session_type !== GIGYA__SESSION_DEFAULT) or (time() < $gltexp_cookie_timestamp and $session_type < 0))
			{
				if (!empty($token))
				{
					$session_sig = $this->calcDynamicSessionSig(
						$token, $expiration, GIGYA__API_KEY,
						GIGYA__API_SECRET
					);
					setrawcookie('gltexp_' . GIGYA__API_KEY, rawurlencode($session_sig), $cookie_expiration, '/', $host);
				}
			}
			elseif ($session_type === GIGYA__SESSION_DEFAULT)
				setrawcookie('gltexp_' . GIGYA__API_KEY, '', $cookie_expiration, '/', $host); /* Unset cookie */
		}
	}

	private function calcDynamicSessionSig($token, $expiration, $userKey, $secret) {
		$unsignedExpString = utf8_encode($token . "_" . $expiration . "_" . $userKey);
		$rawHmac = hash_hmac("sha1", utf8_encode($unsignedExpString), base64_decode($secret), true);
		$sig = base64_encode($rawHmac);
		return $expiration . '_' . $userKey . '_' . $sig;
	}
}