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

        $api = new GigyaApi($this->uid);
        return $api->gigyaApiCall('getUserInfo', $params);
      }
    }
    return FALSE;
  }

  /**
   * Fetches information about user following a given Gigya account.
   *
   * @param mixed $gigya_uid
   *   The Gigya uid.
   *
   * @return array
   *   The response from Gigya.
   */
  function get_user_info( $gigya_uid ) {
    if (empty( $gigya_uid )) {
      return FALSE;
    }

    $params = array(
      'uid' => $gigya_uid,
    );

    return _gigya_api('getUserInfo', $params);
  }

  /**
   * Publishes a user action to the newsfeed stream on all the connected providers that support this feature.
   * @see gigya_publish_user_action()
   *
   * @param array $content.
   *   an associative array containing:
   *     - template :  the template.
   *     - action : the action.
   *     - title : the action title.
   *     - path : the path.
   *  @return the array response from gigya if the user has the Actions capability or FALSE if not.
   *
   */
  public function publishUserAction($content) {
    //if the user dont have the Actions capability return FALSE.
    if (!$this->hasCapability('Actions')) {
      return FALSE;
    }
    return gigya_publish_user_action($this->uid, $content);
  }

  /**
   * Attach the Gigya object to the user object.
   *
   * @param stdClass $account
   *   The user object we need to attache to.
   */
  public static function load(&$account) {
    //Attache to user if the user is logged in.
    $account->gigya = (isset($account->uid) ? new GigyaUser($account->uid): NULL);
  }

  /**
   * Redirects to a logout URL where JavaScript will be added to the page.
   */
  public function logout() {
    gigya_logout_uid($this->uid);
  }

  /**
   * Fetches information about the user friends.
   *
   * @param array $params.
   *   an associative array of params to pass to gigya
   *   @see http://developers.gigya.com/020_Client_API/020_Methods/socialize.getFriends
   *   @see gigya_get_friends_info()
   * @return array
   *   the response from gigya.
   */
  public function getFriends($params = array()) {
    return gigya_get_friends_info($this->uid, $params);
  }

  /**
   * Fetches information about the user capabilities.
   *
   * @return array
   *   the response from gigya if we successfuly get the data from gigya or empty array if not.
   */
  public function getCapabilities() {
    if ($bio = $this->getUserInfo()) {
      $capabilities = explode(', ', $bio['capabilities']);
      array_walk($capabilities, '_gigya_trim_value');
      return $capabilities;
    }
    else {
      return array();
    }
  }

  /**
   *  Check if the user has a specific capability.
   *
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
          user_save($account, $temp_edit, $category['name']);
        }
      }
    }
  }

  /**
   * Sets the Gigya UID to match the Drupal UID.
   * @see gigya_set_uid()
   *
   * @param $uid
   *   The drupal uid to set.
   * @return array
   *   the response from gigya.
  */
  public function setUID($uid) {
    return gigya_set_uid($this->uid, $uid);
  }


  /**
   * Informs Gigya that this user has completed site registration
   */
  public function notifyRegistration($uid) {
    return gigya_notify_registration($this->uid, $uid);
  }

  /**
   * Delete user from Gigya's DB
   * @see gigya_delete_account()
   */
  public function deleteAccount() {
    gigya_delete_account($this->uid);
    return TRUE;
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
        if ($user->uid == 1) {
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
