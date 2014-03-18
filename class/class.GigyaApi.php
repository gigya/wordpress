<?php
/**
 * @file
 * class.GigyaAoi.php
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
   * @param $gigya_uid The gigya user id.
   */
  public function __construct( $gigya_uid ) {
    $this->uid = $gigya_uid;
  }

  /**
   * Helper function that handles Gigya API calls.
   * @param mixed $method
   *   The Gigya API method.
   * @param mixed $params
   *   The method parameters.
   * @param bool $return_code
   * @return array
   *   The Gigya response.
   */
  public function gigyaApiCall( $method, $params, $return_code = FALSE ) {
    $user_key = get_option( 'gigya_user_key', 'AJtmU0HdPU8N' );
    $secret_key = get_option( 'gigya_secret_key', 'V87D7eKWe0R2vukr/rq9/PrKhm24jRtM' );
    $request = new GSRequest( $user_key, $secret_key, $method );
    $user_info = NULL;
    if (!empty($params)) {
      foreach ( $params as $param => $val ) {
        $request->setParam( $param, $val );
      }

      $user_info = in_array( 'getUserInfo', $params );// @todo check if this is the right check.
    }
    $request->setAPIDomain( get_option( 'gigya_data_center', 'us1.gigya.com' ) );
    $response = $request->send();

    $err_code = $this->gigyaApiResponseValidate( $response, $user_info, $return_code);
    if ( !empty( $err_code ) ) {
      return $err_code;
    }

    return _gigya_convert_json_to_array( $response->getResponseText() );
  }

  /**
   * Internal helper function to deal cleanly with various HTTP response codes.
   *
   * @param mixed $response
   *   the gigya response.
   * @param boolean $user_info
   *   tell if the request has the user info parm.
   *
   * @param bool $return_error
   * @return boolean
   *   true if we have errors false if not.
   */
  private function gigyaApiResponseValidate( $response, $user_info = NULL, $return_error = FALSE ) {
    $code = $response->getErrorCode();

    switch ( $code ) {
      case '0':
        if ( !empty( $user_info ) ) {
          if ( $this->gigyaApiSigValidate( $response ) ) {
            return FALSE;
          }
        }
        break;

      case '403005':
        if (get_option( 'gigya_validate', FALSE ) ) {
          return FALSE;
        }
        break;
    }

    return ( !empty( $return_error ) ) ? $code : TRUE;
  }

  /**
   * Helper function that validates the SDK response.
   * @param mixed $response
   *   The SDK response.
   * @return bool True if response signature is valid false if not.
   */
  private function gigyaApiSigValidate( $response ) {
    $secret_key = get_option( 'gigya_secret_key', 'V87D7eKWe0R2vukr/rq9/PrKhm24jRtM' );
    $valid = SigUtils::validateUserSignature(
      $response->getString("UID", ""),
      $response->getString("signatureTimestamp", ""),
      $secret_key,
      $response->getString("UIDSignature", "")
    );

    return !empty($valid);
  }
}