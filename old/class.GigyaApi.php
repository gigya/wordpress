<?php
/**
 * @file
 * GigyaApi.php
 * Provides a GigyaApi object type with associated methods.
 */

/**
 * An object to make the Gigya api calls for a user object(load, delete, get info etc...).
 */
class GigyaApi {

	/**
	 * The gigya user id.
	 * @var string
	 */
	public $uid;

	/**
	 * Constructs a GigyaApi object.
	 *
	 * @param $gigya_uid The gigya user id.
	 */
	public function __construct( $gigya_uid ) {
		$this->uid = $gigya_uid;
	}

	/**
	 * Helper function that handles Gigya API calls.
	 *
	 * @param mixed $method
	 *   The Gigya API method.
	 * @param mixed $params
	 *   The method parameters.
	 * @param bool  $return_code
	 *
	 * @return array
	 *   The Gigya response.
	 */
	public function call( $method, $params, $return_code = FALSE ) {
		$user_key   = GIGYA__API_KEY;
		$secret_key = GIGYA__API_SECRET;

		// Initialize new request.
		$request   = new GSRequest( $user_key, $secret_key, $method );
		$user_info = NULL;
		if ( ! empty( $params ) ) {
			foreach ( $params as $param => $val ) {
				$request->setParam( $param, $val );
			}

			$user_info = in_array( 'getUserInfo', $params ); // @todo check if this is the right check.
		}

		// Set the request path.
		$request->setAPIDomain( get_option( 'gigya_data_center', 'us1.gigya.com' ) );

		// Make the request.
		$response = $request->send();

		// Check for errors in the response.
		$err_code = $this->responseValidate( $response, $user_info, $return_code );
		if ( ! empty( $err_code ) ) {
			return $err_code;
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
	 * Internal helper function to deal cleanly with various HTTP response codes.
	 *
	 * @param mixed   $response
	 *   the Gigya response.
	 * @param boolean $user_info
	 *   tell if the request has the user info parm.
	 *
	 * @param bool    $return_error
	 *
	 * @return boolean
	 *   true if we have errors false if not.
	 */
	private function responseValidate( $response, $user_info = NULL, $return_error = FALSE ) {
		$code = $response->getErrorCode();

		switch ( $code ) {
			case '0':
				if ( ! empty( $user_info ) ) {
					if ( $this->sigValidate( $response ) ) {
						return FALSE;
					}
				}

				return ( ! empty( $return_error ) ) ? $code : FALSE;

				break;

			case '403005':
				if ( get_option( 'gigya_validate', FALSE ) ) {
					return FALSE;
				}
				break;
		}

		return ( ! empty( $return_error ) ) ? $code : TRUE;
	}

	/**
	 * Helper function that validates the SDK response.
	 *
	 * @param mixed $response
	 *   The SDK response.
	 *
	 * @return bool True if response signature is valid false if not.
	 */
	public static function sigValidate( $response ) {
		$global_options = get_option( GIGYA__SETTINGS_GLOBAL );

		$valid = SigUtils::validateUserSignature(
				$response->getString( "UID", "" ),
				$response->getString( "signatureTimestamp", "" ),
				GIGYA__API_SECRET,
				$response->getString( "UIDSignature", "" )
		);

		return $valid;
	}
}