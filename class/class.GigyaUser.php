<?php
/**
 * @file
 * class.GigyaUser.php
 * For socialized login user.
 * Provides a GigyaUser object type with associated methods.
 */

/**
 * An object to make the Gigya api calls for a user object(load, delete, get info etc...).
 */
class GigyaUser {

	/**
	 * The gigya user id.
	 * @var string
	 */
	public $uid;

	/**
	 * Constructs a GigyaUser object.
	 *
	 * @param $gigya_uid The gigya user id.
	 */
	public function __construct( $gigya_uid ) {
		$this->uid = $gigya_uid;
	}

	/**
	 * Get user info from Gigya
	 * @return array || false
	 *   the user info from Gigya.
	 */
	public function getUserInfo() {
		static $user_info = NULL;
		if ( $user_info === NULL ) {
			if ( ! empty( $this->uid ) ) {
				$params = array(
						'uid' => $this->uid,
				);

				$api = new GigyaApi( $this->uid );
				return $api->call( 'getUserInfo', $params );
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
	public function logout() {
		if ( ! empty( $this->uid ) ) {
			$params = array(
					'uid' => $this->uid,
			);

			$api = new GigyaApi( $this->uid );
			return $api->call( 'logout', $params );
		}

		return FALSE;
	}

	/**
	 * Fetches information about the user friends.
	 *
	 * @param array $params .
	 *                      an associative array of params to pass to Gigya
	 *
	 * @see http://developers.gigya.com/020_Client_API/020_Methods/socialize.getFriends
	 * @return array
	 *      the response from gigya.
	 */
	public function getFriends( $params = array() ) {
		if ( ! empty( $this->uid ) ) {
			$params += array(
					'uid' => $this->uid,
			);

			$api = new GigyaApi( $this->uid );
			return $api->call( 'logout', $params );
		}

		return FALSE;
	}

	/**
	 * Fetches information about the user capabilities.
	 * @return array
	 *   the response from gigya if we successfuly get the data from gigya or empty array if not.
	 */
	public function getCapabilities() {
		if ( $bio = $this->getUserInfo() ) {
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
	 * @param $capability
	 *    the capability we checking.
	 *
	 * @return boolean
	 *    TRUE if the user has the capability FALSE if not.
	 */
	public function hasCapability( $capability ) {
		$capabilities = $this->getCapabilities();
		if ( array_search( $capability, $capabilities ) === FALSE ) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Sets the Gigya UID to match the Drupal UID.
	 *
	 * @param $uid
	 *   The drupal uid to set.
	 *
	 * @return array
	 *   the response from gigya.
	 */
	public function setUID( $uid ) {
		if ( ! empty( $this->uid ) && ! empty( $uid ) ) {
			$params = array(
					'uid'     => $this->uid,
					'siteUID' => $uid,
			);

			$api = new GigyaApi( $this->uid );
			return $api->call( 'setUID', $params );
		}

		return FALSE;
	}

	/**
	 * Logs user in to Gigya's service and optionally registers them.
	 *
	 * @param string  $uid
	 *   The drupal User ID.
	 * @param boolean $is_new_user
	 *   Tell Gigya if we add a new user.
	 *
	 * @see      gigya_user_login()
	 *
	 * @return bool|null|string True if the notify login request succeeded or the error message from Gigya
	 */
	function serviceNotify( $uid, $is_new_user = FALSE ) {
		$account = get_userdata( $uid );

		$params['siteUID'] = $uid;

		// Set a new user flag if true.
		if ( ! empty( $is_new_user ) ) {
			$params['newUser'] = TRUE;
		}

		// Add user info.
		$params['userInfo'] = json_encode(
				array(
						'email'    => $account->data->user_email,
						'nickname' => $account->data->user_nickname,
				)
		);

		// Request.
		$api      = new GigyaApi( $this->uid );
		$response = $api->call( 'socialize.notifyLogin', $params );

		// If there an error, return error message.
		if ( $response->getErrorCode() !== 0 ) {
			return $response->getErrorMessage();
		}

		//Set  Gigya cookie.
		try {
			setcookie( $response->getString( "cookieName" ), $response->getString( "cookieValue" ), 0, $response->getString( "cookiePath" ), $response->getString( "cookieDomain" ) );
		} catch ( Exception $e ) {
			error_log( sprintf( 'error seting gigya cookie' ) );
			error_log( sprintf( 'error message : @error', array( '@error' => $e->getMessage() ) ) );
		}

		return TRUE;
	}


	/**
	 * Informs Gigya that this user has completed site registration
	 */
	public function notifyRegistration( $uid ) {
		if ( ! empty( $this->uid ) && ! empty( $uid ) ) {
			$params = array(
					'uid'     => $this->uid,
					'siteUID' => $uid,
			);

			$api = new GigyaApi( $this->uid );
			return $api->call( 'notifyRegistration', $params );
		}

		return FALSE;
	}

	/**
	 * Delete user from Gigya's DB
	 */
	public function deleteAccount() {
		if ( ! empty( $this->uid ) ) {
			$params = array(
					'uid' => $this->uid,
			);

			$api = new GigyaApi( $this->uid );
			$api->call( 'deleteAccount', $params );

			return TRUE;
		}
	}
}
