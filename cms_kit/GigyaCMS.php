<?php

namespace Gigya\CMSKit;

use Exception;
use Gigya\PHP\GSException;
use Gigya\PHP\GSKeyNotFoundException;
use Gigya\PHP\GSRequest;
use Gigya\PHP\GSResponse;
use Gigya\PHP\SigUtils;
use Gigya\WordPress\GigyaLogger;
use stdClass;
use WP_Error;

/**
 * Class GigyaCMS
 */
class GigyaCMS
{
	protected $api_key;
	protected $user_key;
	protected $api_secret;
	protected $rsa_private_key;
	protected $auth_mode;
	protected $logger;

	/**
	 * Constructs a GigyaApi object.
	 */
	public function __construct() {
		$this->api_key         = GIGYA__API_KEY;
		$this->user_key        = GIGYA__USER_KEY ?: ''; /* For backwards compatibility--in the past the user key had not been required */
		$this->api_secret      = GigyaApiHelper::decrypt( GIGYA__API_SECRET, SECURE_AUTH_KEY );
		$this->rsa_private_key = ( ! empty( GIGYA__PRIVATE_KEY ) ) ? GigyaApiHelper::decrypt( GIGYA__PRIVATE_KEY, SECURE_AUTH_KEY ) : '';
		$this->auth_mode       = GIGYA__AUTH_MODE;
		$this->logger          = new GigyaLogger();
	}

	/**
	 * Helper function that handles Gigya API calls.
	 *
	 * @param mixed $method
	 *   The Gigya API method.
	 * @param mixed $params
	 *   The method parameters.
	 *
	 * @return array | WP_Error | integer
	 *   The Gigya response.
	 *
	 * @throws GSException
	 */
	public function call( $method, $params ) {
		// Initialize new request.
		$request   = ( isset( $this->user_key ) ) ? new GSRequest( $this->api_key, $this->api_secret, $method, null, true, $this->user_key ) : new GSRequest( $this->api_key, $this->api_secret, $method );
		$user_info = null;

		if ( ! empty( $params ) ) {
			foreach ( $params as $param => $val ) {
				$request->setParam( $param, $val );
			}

			$user_info = in_array( 'getUserInfo', $params );
		}
		$request->setParam( 'environment',
			'{"cms_name":"WordPress","cms_version":"WordPress_' . get_bloginfo( 'version' ) . '","gigya_version":"Gigya_module_' . GIGYA__VERSION . '","php_version":"' . phpversion()
			. '"}' );

		// To be define on CMS code (or not).
		$api_domain = GIGYA__API_DOMAIN;
		$uid        = $request->getParams()->getString( 'UID', '' );

		// Set the request path.
		$domain = ! empty( $api_domain ) ? $api_domain : 'us1.gigya.com';
		$request->setAPIDomain( $domain );

		// Make the request.
		ini_set( 'arg_separator.output', '&' );
		$response = $request->send();

		ini_restore( 'arg_separator.output' );

		// Check for errors
		$err_code = $response->getErrorCode();
		if ( $err_code != 0 ) {
			$this->logger->debug( 'SAP CDC API called. Endpoint: ' . $method . ', call ID:' . $response->getString( "callId", "N/A" ) . ',  failed: ' . $response->getErrorMessage() . ' - ' . $response->getErrorMessage(), $uid );


			return new WP_Error( $err_code, $response->getErrorMessage() );

		} else {
			if ( ! empty( $user_info ) ) {
				/* Check validation in the response */
				$valid = SigUtils::validateUserSignature(
					$response->getString( "UID", "" ),
					$response->getString( "signatureTimestamp", "" ),
					$this->api_secret,
					$response->getString( "UIDSignature", "" )
				);

				if ( ! empty( $valid ) ) {
					$this->logger->debug( 'SAP CDC API called. Endpoint: ' . $method . ', call ID:' . $response->getString( "callId", "N/A" ) . ',  failed: ' . $response->getErrorMessage() . ' - ' . $response->getErrorMessage(), $uid );

					return $err_code;
				}
			}
		}

		$this->logger->debug( 'SAP CDC API called. Endpoint: ' . $method . ', call ID:' . $response->getString( "callId", "N/A" ) . ', was successful.', $uid );

		return $this->jsonToArray( $response->getResponseText() );
	}

	/**
	 *  get gigya Screen-Sets id's
	 *
	 * @return array|false
	 */
	public function getScreenSetsIdList() {
		$gigya_api_helper = new GigyaApiHelper( GIGYA__API_KEY, GIGYA__USER_KEY, GIGYA__AUTH_KEY, GIGYA__API_DOMAIN, GIGYA__AUTH_MODE );

		try {
			$res = $gigya_api_helper->sendGetScreenSetsCall();
		} catch ( GSApiException $e ) {
			$this->logger->error( 'Error fetching SAP Customer Data Cloud Screen-Sets: ' . $e->getErrorCode() . ': ' . $e->getMessage() . '. Call ID: ' . $e->getCallId() );

			return false;
		} catch ( GSException $e ) {
			$this->logger->error( 'Error fetching SAP Customer Data Cloud Screen-Sets: ' . $e->getMessage() );

			return false;
		}

		array_walk( $res['screenSets'], function ( &$el ) {
			$el['label'] = $el['screenSetID'];
			unset( $el['screenSetID'] );
		} );

		return $res['screenSets'];
	}

	/**
	 * Convert JSON response to a PHP array.
	 *
	 * @param $data
	 *   The JSON data.
	 *
	 * @return array
	 *   The converted array from the JSON.
	 */
	public static function jsonToArray( $data ) {
		return json_decode( $data, true );
	}

	/**
	 * @param $auth_mode
	 * @param $api_key
	 * @param $user_key
	 * @param $api_secret
	 * @param $api_domain
	 *
	 * @return GSResponse
	 *
	 * @throws GSKeyNotFoundException
	 * @throws Exception
	 */
	public function apiValidate( $auth_mode, $api_key, $user_key, $api_secret, $api_domain ) {
		return ($auth_mode === 'user_rsa')
			? $this->apiValidateWithPrivateKey( $api_key, $user_key, $api_secret, $api_domain )
			: $this->apiValidateWithUserSecret( $api_key, $user_key, $api_secret, $api_domain );
	}

	/**
	 * Check that the SAP CDC credentials are valid with a call to SAP CDC, using the user key / secret key authentication method
	 *
	 * @param string $api_key
	 * @param string $user_key
	 * @param string $api_secret
	 * @param string $api_domain
	 *
	 * @return GSResponse
	 *
	 * @throws Exception
	 */
	public function apiValidateWithUserSecret( $api_key, $user_key, $api_secret, $api_domain ) {
		$request = new GSRequest( $api_key, $api_secret, 'socialize.getProvidersConfig', null, true, $user_key );

		$request->setAPIDomain( $api_domain );
		ini_set( 'arg_separator.output', '&' );
		$res = $request->send();
		ini_restore ( 'arg_separator.output' );

		return $res;
	}

	/**
	 * @param string $api_key
	 * @param string $user_key
	 * @param string $rsa_private_key
	 * @param string $api_domain
	 *
	 * @return GSResponse
	 *
	 * @throws GSKeyNotFoundException
	 */
	public function apiValidateWithPrivateKey( $api_key, $user_key, $rsa_private_key, $api_domain ) {
		$request = new GSRequest( $api_key, null, 'socialize.getProvidersConfig', null, true, $user_key, $rsa_private_key );

		$request->setAPIDomain( $api_domain );
		ini_set( 'arg_separator.output', '&' );
		$res = $request->send();
		ini_restore ( 'arg_separator.output' );

		return $res;
	}

	/**
	 * Get user info from Gigya
	 *
	 * @param $guid
	 *
	 * @return array | false
	 *   the user info from Gigya.
	 *
	 * @throws Exception
	 */
	public function getUserInfo( $guid ) {
		static $user_info = null;
		if ( $user_info === null ) {
			if ( ! empty( $guid ) ) {
				$params = array(
					'uid' => $guid,
				);

				return $this->call( 'getUserInfo', $params );
			}
		}

		return false;
	}

	/**
	 * Attach the Gigya object to the user object.
	 *
	 * @param stdClass $account
	 *   The user object we need to attache to.
	 */
	public static function load( &$account ) {
		// Attache to user if the user is logged in.
		$account->gigya = ( isset( $account->uid ) ? new GigyaUser( $account->uid ) : null );
	}

	/**
	 * Social logout.
	 *
	 * @param    $guid
	 *
	 * @return    array|WP_Error|integer|boolean
	 *
	 * @throws Exception
	 */
	public function userLogout( $guid ) {
		if ( ! empty( $guid ) ) {
			$params = array(
					'uid' => $guid,
			);

			return $this->call( 'socialize.logout', $params );
		}

		return false;
	}

	/**
	 * Fetches information about the user friends.
	 *
	 * @param       $guid
	 * @param array $params .
	 *                      an associative array of params to pass to Gigya
	 *
	 * @see https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/4174b27f70b21014bbc5a10ce4041860.html
	 * @return array | false
	 *      the response from gigya.
	 *
	 * @throws Exception
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
	 *
	 * @throws Exception
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
	 *
	 * @param	$value
	 */
	private function trimValue( &$value ) {
		$value = trim( $value );
	}

	/**
	 * Check if the user has a specific capability.
	 *
	 * @param $guid
	 * @param $capability
	 *    the capability we checking.
	 *
	 * @return boolean
	 *    TRUE if the user has the capability FALSE if not.
	 * @throws Exception
	 */
	public function hasCapability( $guid, $capability ) {
		$capabilities = $this->getCapabilities( $guid );
		if ( array_search( $capability, $capabilities ) === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Logs user in to Gigya's service and optionally registers them.
	 *
	 * @param string $uid
	 *   The CMS User ID.
	 * @param boolean $is_new_user
	 *   Tell Gigya if we add a new user.
	 *
	 * @param $user_info
	 *
	 * @see      gigya_user_login()
	 *
	 * @return bool|null|string True if the notify login request succeeded or the error message from Gigya
	 *
	 * @throws Exception
	 */
	public function notifyLogin( $uid, $is_new_user = false, $user_info = null ) {
		$params['siteUID'] = $uid;

		// Set a new user flag if true.
		if ( ! empty( $is_new_user ) ) {
			$params['newUser'] = true;
		}

		// Add user info.
		if ( ! empty( $user_info ) ) {
			$params['userInfo'] = json_encode( $user_info );
		}

		// Request.
		$response = $this->call( 'socialize.notifyLogin', $params );
		// If error return message
		if ( is_wp_error($response)) {
			return $response->get_error_message();
		}

		/* Set SAP CDC cookie */
		try {
			setcookie( $response["cookieName"], $response["cookieValue"], 0, $response["cookiePath"], $response["cookieDomain"] );
		} catch ( Exception $e ) {
			$this->logger->error( 'Login to SAP CDC failed: error SAP CDC cookie message: ' . $e->getMessage(), $uid );
		}

		return true;
	}

	/**
	 * Informs Gigya that this user has completed site registration
	 *
	 * @param        $guid
	 * @param string $uid
	 *   The CMS User ID.
	 *
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public function notifyRegistration( $guid, $uid ) {
		if ( ! empty( $guid ) && ! empty( $uid ) ) {
			$params = array(
					'uid'     => $guid,
					'siteUID' => $uid,
			);

			return $this->call( 'socialize.notifyRegistration', $params );
		}

		return false;
	}

	/**
	 * Delete user from Gigya's DB
	 *
	 * @param string $uid
	 *   The CMS User ID.
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function deleteUser( $uid ) {
		if ( ! empty( $uid ) ) {
			$params = array(
					'uid' => $uid,
			);

			$this->call( 'socialize.deleteAccount', $params );

			return true;
		}
		return false;
	}

/*******************************/
//            RaaS             //
/*******************************/

	/**
	 * @return bool
	 *
	 * @throws GSException
	 */
	public function isRaaS() {
		$res = $this->call( 'accounts.getSchema', array() );
		if ( is_wp_error( $res )) {
			if ( $res->get_error_code() === GIGYA__ERROR_UNAUTHORIZED_PARTNER) {
				return false;
			}

			throw new GSException( $res->get_error_code() . ': ' . $res->get_error_message() );
		}

		return true;
	}

	/**
	 * @param string $guid  Gigya UID
	 *
	 * @return GSResponse
	 *
	 * @throws GSKeyNotFoundException
	 * @throws GSApiException
	 * @throws GSException
	 */
	public function getAccount( $guid ) {
		$req_params = array(
			'UID'                => $guid,
			'include'            => 'profile,data,preferences,subscriptions,loginIDs',
			'extraProfileFields' => "languages,address,phones,education,honors,publications,patents,certifications,professionalHeadline,bio,industry,specialties,work,skills,religion,politicalView,interestedIn,relationshipStatus,hometown,favorites,followersCount,followingCount,username,locale,verified,timezone,likes"
		);

		// Because we can only trust the UID parameter from the origin object,
		// We'll ask Gigya's API for account-info straight from the server.
		$gigya_api_helper = new GigyaApiHelper( GIGYA__API_KEY, GIGYA__USER_KEY, GIGYA__AUTH_KEY, GIGYA__API_DOMAIN, GIGYA__AUTH_MODE );

		return $gigya_api_helper->sendApiCall( 'accounts.getAccountInfo', $req_params );
	}

	/**
	 * Queries Gigya with the accounts.search call
	 *
	 * @param array $params Full parameters of the call. Usually in the form of [ 'query' => 'SELECT ...', 'openCursor' => true ], but can be [ 'cursorId' => ... ]
	 * @param string|null $required_field The field (if any) that every searched user must include, in order to appear in the result. For example: 'profile', 'identities'.
	 * If the field is missing in the user record, it will be filtered out.
	 * @param int $max_page optional, gets the first $max_page pages. Useful if the number of records is high and we only need some of them, similar to LIMIT in some SQL systems.
	 *
	 * @return array
	 *
	 * @throws GSApiException
	 * @throws GSException
	 */
	public function searchGigyaUsers( $params, $required_field = null, $max_page = - 1 ) {
		$gigya_users = [];

		$gigya_api_helper = new GigyaApiHelper( GIGYA__API_KEY, GIGYA__USER_KEY, GIGYA__AUTH_KEY, GIGYA__API_DOMAIN, GIGYA__AUTH_MODE );
		$gigya_data       = $gigya_api_helper->sendApiCall( 'accounts.search', $params )->getData()->serialize();

		if ( $required_field !== null ) {
			foreach ( $gigya_data['results'] as $user_data ) {
				if ( isset( $user_data[ $required_field ] ) ) {
					$gigya_users[] = $user_data;
				}
			}
		} else {
			$gigya_users = $gigya_data['results'];
		}

		if ( ! empty( $gigya_data['nextCursorId'] ) and $max_page != 0 ) {
			$cursorId = $gigya_data['nextCursorId'];

			return array_merge( $gigya_users, $this->searchGigyaUsers( [ 'cursorId' => $cursorId ], $required_field, -- $max_page ) );
		}

		return $gigya_users;
	}
	
	/**
	 * @param $email
	 *
	 * @throws Exception
	 */
	public function deleteAccountByEmail( $email ) {
		/* Get info about the primary account */
		$email = $this->cleanEmail( $email );
		$query = "select UID from accounts where loginIDs.emails = '{$email}'";

		/* Get the UID from Email */
		$res = $this->call( 'accounts.search', array( 'query' => $query ) );

		/* Delete the user */
		if ( ! is_wp_error( $res ) )
			$this->call( 'accounts.deleteAccount', array( 'UID' => $res['results'][0]['UID'] ) );
	}

	/**
	 * @param $guid
	 *
	 * @throws Exception
	 */
	public function deleteAccountByGUID( $guid ) {
		/* Delete the user */
		$this->call( 'accounts.deleteAccount', array( 'UID' => $guid ) );
	}

	/**
	 * Checks if this email is the primary user email
	 *
	 * @param array $gigya_emails
	 * @param string $wp_email email from WP DB.
	 *
	 * @internal param \The $userInfo user info from accounts.getUserInfo api call
	 * @return bool
	 */
	public static function isPrimaryUser( $gigya_emails, $wp_email ) {
		return ( in_array( $wp_email, $gigya_emails ) );
	}

	/**
	 * Helper function to convert & validate JSON.
	 *
	 * @param $json
	 *
	 * @return array|mixed|string
	 */
	public static function parseJSON( $json ) {

		/* Decode the JSON data */
		$result = json_decode( $json, true );

		$err = json_last_error();
		if ( $err != JSON_ERROR_NONE )
		{
			/* switch and check possible JSON errors */
			switch ( json_last_error() )
			{
				case JSON_ERROR_DEPTH:
					$msg = 'Maximum stack depth exceeded.';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$msg = 'Underflow or the modes mismatch.';
					break;
				case JSON_ERROR_CTRL_CHAR:
					$msg = 'Unexpected control character found.';
					break;
				case JSON_ERROR_SYNTAX:
					$msg = 'Syntax error, malformed JSON.';
					break;
				case JSON_ERROR_UTF8:
					$msg = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
					break;
				default:
					$msg = 'Unknown JSON error occurred.';
					break;
			}

			return $msg;
		}

		/* Everything is OK. Return associative array. */
		return $result;
	}

	/**
	 * Checks whether the user agent is a search engine crawler
	 *
	 * @return bool
	 */
	public static function isSpider() {
		// Add as many spiders you want in this array
		$spiders = array( 'Googlebot', 'Yammybot', 'Openbot', 'Yahoo', 'Slurp', 'msnbot', 'ia_archiver', 'Lycos', 'Scooter', 'AltaVista', 'Teoma', 'Gigabot', 'Googlebot-Mobile' );

		// Loop through each spider and check if it appears in
		// the User Agent
		foreach ( $spiders as $spider ) {
			if ( strpos( $_SERVER['HTTP_USER_AGENT'], $spider ) !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Prepare email string to be sent via HTTP
	 *
	 * @param string $email
	 *
	 * @return string
	 */
	protected function cleanEmail( $email ) {
		$email = str_replace(' ', '', $email);
		$clean_email = htmlspecialchars($email);
		return $clean_email;
	}
}
