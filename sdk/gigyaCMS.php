<?php

/**
 * Class GigyaCMS
 */
class GigyaCMS {

	/**
	 * Constructs a GigyaApi object.
	 */
	public function __construct() {

		$this->api_key    = GIGYA__API_KEY;
		$this->api_secret = GIGYA__API_SECRET;

	}

	/**
	 * Helper function that handles Gigya API calls.
	 *
	 * @param mixed $method
	 *   The Gigya API method.
	 * @param mixed $params
	 *   The method parameters.
	 *
	 * @return array
	 *   The Gigya response.
	 */
	public function call( $method, $params ) {

		// Initialize new request.
		$request   = new GSRequest( $this->api_key, $this->api_secret, $method );
		$user_info = NULL;
		if ( ! empty( $params ) ) {
			foreach ( $params as $param => $val ) {
				$request->setParam( $param, $val );
			}

			$user_info = in_array( 'getUserInfo', $params );
		}

		// To be define on CMS code (or not).
		$api_domain = GIGYA__API_DOMAIN;

		// Set the request path.
		$domain = ! empty( $api_domain ) ? $api_domain : 'us1.gigya.com';
		$request->setAPIDomain( $domain );

		// Make the request.
		$response = $request->send();

		// Check for errors
		$err_code = $response->getErrorCode();
		if ( $err_code != 0 ) {

			// Set global debug on the CMS
			$gigya_debug = GIGYA__API_DEBUG;

			if ( ! empty( $gigya_debug ) ) {
				error_log( $response->getLog() );
			}
		}
		else {
			if ( ! empty( $user_info ) ) {

				// Check validation in the response.
				$valid = SigUtils::validateUserSignature(
						$response->getString( "UID", "" ),
						$response->getString( "signatureTimestamp", "" ),
						$this->api_secret,
						$response->getString( "UIDSignature", "" )
				);

				if ( ! empty( $valid ) ) {
					return $err_code;
				}
			}
		}

		return $this->jsonToArray( $response->getResponseText() );
	}

	/**
	 * Convert JSON response to a PHP array.
	 *
	 * @param $data
	 *   The JSON data.
	 * @param $data
	 *
	 * @return array
	 *   The converted array from the JSON.
	 */
	public static function jsonToArray( $data ) {
		return json_decode( $data, TRUE );
	}

	/**
	 * Get user info from Gigya
	 *
	 * @param $guid
	 *
	 * @return array || false
	 *   the user info from Gigya.
	 */
	public function getUserInfo( $guid ) {
		static $user_info = NULL;
		if ( $user_info === NULL ) {
			if ( ! empty( $guid ) ) {
				$params = array(
						'uid' => $guid,
				);

				return $this->call( 'getUserInfo', $params );
			}
		}

		return FALSE;
	}

	/**
	 * Attach the Gigya object to the user object.
	 *
	 * @param stdClass $account
	 *   The user object we need to attache to.
	 */
	public static function load( &$account ) {
		//Attache to user if the user is logged in.
		$account->gigya = ( isset( $account->uid ) ? new GigyaUser( $account->uid ) : NULL );
	}

	/**
	 * Redirects to a logout URL where JavaScript will be added to the page.
	 */
	public function logout( $guid ) {
		if ( ! empty( $guid ) ) {
			$params = array(
					'uid' => $guid,
			);

			return $this->call( 'socialize.logout', $params );
		}

		return FALSE;
	}

	/**
	 * Fetches information about the user friends.
	 *
	 * @param       $guid
	 * @param array $params .
	 *                      an associative array of params to pass to Gigya
	 *
	 * @see http://developers.gigya.com/020_Client_API/020_Methods/socialize.getFriends
	 * @return array
	 *      the response from gigya.
	 */
	public function getFriends( $guid, $params = array() ) {
		if ( ! empty( $guid ) ) {
			$params += array(
					'uid' => $guid,
			);

			return $this->call( 'logout', $params );
		}

		return FALSE;
	}

	/**
	 * Fetches information about the user capabilities.
	 *
	 * @param $guid
	 *
	 * @return array
	 *   the response from gigya if we successfuly get the data from gigya or empty array if not.
	 */
	public function getCapabilities( $guid ) {
		if ( $bio = $this->getUserInfo( $guid ) ) {
			$capabilities = explode( ', ', $bio['capabilities'] );
			array_walk( $capabilities, array( $this, 'trimValue' ) );
			return $capabilities;
		}

		return array();
	}

	/**
	 * Callback for array_walk.
	 * Helper function for trimming.
	 */
	private function trimValue( &$value ) {
		$value = trim( $value );
	}

	/**
	 *  Check if the user has a specific capability.
	 *
	 * @param $guid
	 * @param $capability
	 *    the capability we checking.
	 *
	 * @return boolean
	 *    TRUE if the user has the capability FALSE if not.
	 */
	public function hasCapability( $guid, $capability ) {
		$capabilities = $this->getCapabilities( $guid );
		if ( array_search( $capability, $capabilities ) === FALSE ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Logs user in to Gigya's service and optionally registers them.
	 *
	 * @param string  $uid
	 *   The CMS User ID.
	 * @param boolean $is_new_user
	 *   Tell Gigya if we add a new user.
	 *
	 * @param null    $user_info
	 *
	 * @see      gigya_user_login()
	 *
	 * @return bool|null|string True if the notify login request succeeded or the error message from Gigya
	 */
	function notifyLogin( $uid, $is_new_user = FALSE, $user_info = NULL ) {

		$params['siteUID'] = $uid;

		// Set a new user flag if true.
		if ( ! empty( $is_new_user ) ) {
			$params['newUser'] = TRUE;
		}

		// Add user info.
		if ( ! empty( $user_info ) ) {
			$params['userInfo'] = json_encode( $user_info );
		}

		// Request.
		$response = $this->call( 'socialize.notifyLogin', $params );

		//Set  Gigya cookie.
		try {
			setcookie( $response["cookieName"], $response["cookieValue"], 0, $response["cookiePath"], $response["cookieDomain"] );
		} catch ( Exception $e ) {
			error_log( sprintf( 'error string gigya cookie' ) );
			error_log( sprintf( 'error message : @error', array( '@error' => $e->getMessage() ) ) );
		}

		return TRUE;
	}


	/**
	 * Informs Gigya that this user has completed site registration
	 *
	 * @param        $guid
	 * @param string $uid
	 *   The CMS User ID.
	 *
	 * @return array|bool
	 */
	public function notifyRegistration( $guid, $uid ) {
		if ( ! empty( $guid ) && ! empty( $uid ) ) {
			$params = array(
					'uid'     => $guid,
					'siteUID' => $uid,
			);

			return $this->call( 'socialize.notifyRegistration', $params );
		}

		return FALSE;
	}

	/**
	 * Delete user from Gigya's DB
	 *
	 * @param string $uid
	 *   The CMS User ID.
	 *
	 * @return bool
	 */
	public function deleteUser( $uid ) {
		if ( ! empty( $uid ) ) {
			$params = array(
					'uid' => $uid,
			);

			$this->call( 'socialize.deleteAccount', $params );

			return TRUE;
		}
	}

/////////////////////////////////
//            RaaS             //
/////////////////////////////////

	/**
	 * @param $guid
	 *
	 * @return mixed
	 */
	public function getAccount( $guid ) {

		$req_params = array(
				'UID'     => $guid,
				'include' => 'profile, data, loginIDs'
		);

		// Because we can only trust the UID parameter from the origin object,
		// We'll ask Gigya's API for account-info straight from the server.
		return $this->call( 'accounts.getAccountInfo', $req_params );

	}

	/**
	 * @param $uid
	 */
	public function deleteAccount( $uid ) {

		$this->call( 'accounts.deleteAccount', array( 'UID' => $uid ) );

	}

	/**
	 * @param $account
	 * Gigya's RaaS account as we get from:
	 *
	 * @See getAccount
	 *
	 * @return array
	 */
	public function getProviders( $account ) {

		// Get info about the primary account.
		$query = 'select loginProvider from accounts where loginIDs.emails = ' . $account['profile']['email'];

		$search_res = $this->call( 'accounts.search', array( 'query' => $query ) );

		// Returns the primary provider, and the secondary (current).
		return array(
				'primary'   => $search_res['results'][0]['loginProvider'],
				'secondary' => $account['loginProvider']
		);
	}

	/**
	 * Checks if this email is the primary user email
	 *
	 * @param $gigya_emails
	 * @param $wp_email The email from WP DB.
	 *
	 * @internal param \The $userInfo user info from accounts.getUserInfo api call
	 * @return bool
	 */
	public static function isPrimaryUser( $gigya_emails, $wp_email ) {

		if ( in_array( $wp_email, $gigya_emails ) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Helper function to convert a text field key|value to an array.
	 *
	 * @param string $values
	 *
	 * @return array
	 */
	public static function advancedValuesParser($values) {
		$lines = array();
		$values = explode("\n", $values);

		// Clean up values.
		$values = array_map('trim', $values);
		$values = array_filter($values, 'strlen');

		foreach ($values as $value) {
			preg_match('/(.*)\|(.*)/', $value, $matches);
			$lines[$matches[1]] = $matches[2];
		}

		return $lines;
	}
}