<?php
/**
 * @file
 * class.GigyaUser.php
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
      if ( !empty( $this->uid ) ) {
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
   * Publishes a user action to the newsfeed stream on all the connected providers that support this feature.
   * @param array $content.
   *   an associative array containing:
   *     - template :  the template.
   *     - action : the action.
   *     - title : the action title.
   *     - path : the path.
   *  @return the array response from gigya if the user has the Actions capability or FALSE if not.
   */
  public function publishUserAction( $content ) {
    // if the user don't have the Actions capability return FALSE.
    if ( !$this->hasCapability( 'Actions' ) ) {
      return FALSE;
    }

    if ( !empty( $this->uid ) ) {
      $params = array(
        'uid' => $this->uid,
        'userAction' => _gigya_get_useraction_xml( $content ), // @todo this function not exist.
      );

      $api = new GigyaApi( $this->uid );
      return $api->call( 'publishUserAction', $params );
    }

    return FALSE;
  }

  /**
   * Attach the Gigya object to the user object.
   * @param stdClass $account
   *   The user object we need to attache to.
   */
  public static function load( &$account ) {
    //Attache to user if the user is logged in.
    $account->gigya = ( isset( $account->uid ) ? new GigyaUser( $account->uid ) : NULL);
  }

  /**
   * Redirects to a logout URL where JavaScript will be added to the page.
   */
  public function logout() {
    if ( !empty( $this->uid ) ) {
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
   * @param array $params.
   *   an associative array of params to pass to Gigya
   *   @see http://developers.gigya.com/020_Client_API/020_Methods/socialize.getFriends
   * @return array
   *   the response from gigya.
   */
  public function getFriends($params = array()) {
    if ( !empty( $this->uid ) ) {
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
      array_walk($capabilities, array( $this, 'trimValue' ) );
      return $capabilities;
    }

    return array();
  }

  /**
   * Callback for array_walk.
   * Helper function for trimming.
   */
  private function trimValue(&$value) {
    $value = trim($value);
  }

  /**
   *  Check if the user has a specific capability.
   *  @param $capability
   *    the capability we checking.
   *  @return boolean
   *    TRUE if the user has the capability FALSE if not.
   */
  public function hasCapability($capability) {
    $capabilities = $this->getCapabilities();
    if (array_search($capability, $capabilities) === FALSE) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Sets the Gigya UID to match the Drupal UID.
   * @param $uid
   *   The drupal uid to set.
   * @return array
   *   the response from gigya.
   */
  public function setUID($uid) {
    if( !empty( $this->uid ) && !empty( $uid )) {
      $params = array(
        'uid' => $this->uid,
        'siteUID' => $uid,
      );

      $api = new GigyaApi( $this->uid );
      return $api->call( 'setUID', $params );
    }

    return FALSE;
  }


  /**
   * Informs Gigya that this user has completed site registration
   */
  public function notifyRegistration( $uid ) {
    if( !empty( $this->uid ) && !empty( $uid )) {
      $params = array(
        'uid' => $this->uid,
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
    if ( !empty( $this->uid ) ) {
      $params = array(
        'uid' => $this->uid,
      );

      $api = new GigyaApi( $this->uid );
      $api->call( 'deleteAccount', $params );

      return TRUE;
    }
  }

  /**
   * Maps extended profile fields that weren't on the registration form.
   *
   * @param array $edit
   *   The array of form values submitted by the user.
   *   @see gigya_user_insert()
   */
  public function mapExtendedProfileFields($edit) {
    $bio = $this->getUserInfo();

    if (module_exists('profile')) {
      $temp_edit = array();
      if ($profile_categories = profile_categories()) {

        foreach ($profile_categories as $category) {
          $result = _profile_get_fields($category['name']);
          foreach ($result as $field) {
            // Only attempt to set this variable if we've mapped it and
            // it isn't already set elsewhere.
            if (variable_get('gigya_profile_' . $field->name, '') != '0' && !isset($edit[$field->name])) {
              $bio_assoc = variable_get('gigya_profile_' . $field->name, '');
              $temp_edit[$field->name] = $bio[$bio_assoc];
            }
          }
          /*
           * This could potentially cause conflicts with other modules not
           * expecting that this will be called. Disable mapping of
           * extended profile fields if this causes a problem. This could
           * probably be replaced by profile_save_profile but I haven't
           * fully investigated it.
           */
          user_save($account, $temp_edit, $category['name']); // @todo what account.
        }
      }
    }
  }


  /**
   * Get a gigya user object from the url query string.
   * @retrun
   *   A GigyaUser object if the signature is correct , false if not.
   */
  public static function userFromUrl() {
    if (!empty($_GET['signature']) && !empty($_GET['timestamp']) && !empty($_GET['UID'])) {
      // First, verify signature.
      $localkey = _gigya_calculate_signature($_GET['timestamp'], $_GET['UID']);
      if ($localkey != $_GET['signature']) {
        drupal_set_message(t('Unable to authenticate. Gigya signature does not match.'), 'error');
        if ($user->uid == 1) { //  @todo What user what form_values
          drupal_set_message(t('Signature is %gigya, Site sig is %site.', array('%gigya' => $form_values['signature'], '%site' => $localkey)), 'error');
        }
        return FALSE;
      }
      else {
        return new GigyaUser($_GET['UID']);
      }
    }
    else {
      return FALSE;
    }
  }
}
