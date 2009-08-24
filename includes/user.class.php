<?php 
/**
 * Encapsulates user operations.
 */
class GigyaUser {
    /**
     * Meta name for usermeta boolean indicator showing whether the user connected with Gigya Socialize sometime in the past.
     * @var string
     */
    var $_meta__HasConnectedWithGigya = '_has_connected_with_gigya';
    
    /**
     * Meta name for usermeta string indicator of which Gigya Socialize login providers the user is utilizing.
     * @var string
     */
    var $_meta__SocializeLoginProvider = '_gigya_socialize_login_provider';
    
    /**
     * Meta name for usermeta string indicator of what the thumbnail URL is for the user's profile photo.
     * @var unknown_type
     */
    var $_meta__SocializeThumbnailUrl = '_gigya_socialize_thumbnail_url';
    
    /**
     * Meta name for usermeta string indicator of what the last comment posted by the user was.
     * @var string
     */
    var $_meta__RecentCommentPostedId = '_last_comment_post_id';
    
    /** 
     * Meta name for usermeta string indicator that the user recently logged out.
     * @var string
     */
    var $_meta__RecentLogoutPosted = '_recently_logged_out';
    
    /**
     * Returns a string indicating the location of the user's thumbnail picture on a SN.
     *
     * @param int $userId The unique identifier for a user.
     * @return string Returns an absolute URL or an empty string if no thumbnail url is saved.
     */
    function getUserThumbnail($userId) {
        return get_usermeta($userId, $this->_meta_SocializeThumbnailUrl);
    }

    
    /**
     * Registers the fact that a user has connected via Gigya.
     *
     * @param array $data An associative array of data from GS.
     * @param int $userId The unique identifier for the user.
     */
    function registerData($data, $userId) {
        global $wpdb;
        $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES( %d, %s, %s )", $userId, $data['gigya-uid'], $this->getMetaNameForNetwork($data['gigya-login-provider'])));
        update_usermeta($userId, $this->_meta_SocializeLoginProvider, $data['gigya-login-provider']);
        update_usermeta($userId, $this->_meta_HasConnectedWithGigya, '1');
        update_usermeta($userId, $this->_meta_SocializeThumbnailUrl, $data['gigya-thumbnail-url']);
    }
    
    /**
     * Registers a new user from the information provided via the Gigya login component.
     *
     * @param array $data An associative array of data from the login component.
     * @return The data for the newly register user (if valid).
     */
    function registerUser($data) {
        require_once (ABSPATH.WPINC.'/registration.php');
        $username = $this->getUnusedUserName(sanitize_user($data['gigya-nickname']), $data['gigya-login-provider']);
        $email = $this->getEmailFromGigyaData($data['email']);
        $password = wp_generate_password();

        
        $userData = array('user_login'=>$username, 'user_pass'=>$password, 'user_email'=>$email);
        $userData['user_nickname'] = $username;
        $userData['first_name'] = $this->sanitizeNullString($data['gigya-first-name']);
        $userData['last_name'] = $this->sanitizeNullString($data['gigya-last-name']);
        $userData['display_name'] = $this->sanitizeNullString($data['gigya-nickname']);
        $userData['user_url'] = $this->sanitizeNullString($data['gigya-profile-url']);
		
        $userId = wp_insert_user($userData);
		$this->registerData($data, $userId);
		
        $user = get_userdata($userId);
        return $user;
    }
    
    /**
     * Checks to ensure that the email address is valid for registration.  If it is not, then return an empty string, otherwise
     * return the original email address.
     * @param string $email The email address to verify.
     * @return An empty string if the original email was invalid and the original email address otherwise.
     */
    function getEmailFromGigyaData($email) {
        if (!is_email($email)) {
            $email = '';
        }
        return $email;
    }
    
    /**
     * Returns a username that has not yet been used by a previous user and can be used to register a new user.
     * @param string $nickName The nick name to use as a base when deciding the valid nick name that can be used.
     * @return string A nick name that has yet to be used by a user.
     */
    function getUnusedUserName($userName, $loginProvider) {
        if ($userName === '') {
            $userName = ucfirst($loginProvider).'User';
        }
        $toTry = $userName;
        $counter = 0;
        while (username_exists($toTry)) {
            $toTry = $userName.$counter;
            $counter++;
        }
        return $toTry;
    }
    
    /**
     * Checks a string to see if it is equal to 'null'.  If it is, then an empty string is returned.  Otherwise, the original string is returned.
     * @param string $possibleNull The string to check for equality to 'null'.
     * @return An empty string if the original string was 'null' or the original string.
     */
    function sanitizeNullString($possibleNull) {
        return $possibleNull == 'null' ? '' : $possibleNull;
    }
    
    /**
     * Returns a boolean indicating whether the current user has a gigya login provider UID.
     *
     * @param int|bool $userId The unique identifier for a user or false to use the current user.
     * @return bool Whether or not the current user has a gigya login provider UID.
     */
    function userHasGigyaConnection($userId = false) {
        if (!is_user_logged_in()) {
            return false;
        }
        if (false === $userId) {
            $user = wp_get_current_user();
            $userId = $user->ID;
        }
        return get_usermeta($userId, $this->_meta_HasConnectedWithGigya, true) == '1';
    }
    
    /**
     * Returns a boolean value indicating whether a user has previously connected via Gigya Socialize.
     *
     * @uses $wpdb
     *
     * @param array $gigyaData An array of all the pertinent data returned from the Gigya login call.
     * @return int|bool False if the user has never connected and the user id of the user if they have.
     */
    function userHasPreviouslyConnectedViaGigya($gigyaData) {
        $loginProvider = $this->getMetaNameForNetwork($gigyaData['gigya-login-provider']);
        global $wpdb;
        $userId = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", $gigyaData['gigya-uid'], $loginProvider));
        if (!$userId) {
            return false;
        } else {
            $this->editUserData($gigyaData, $userId);
            return (int) $userId;
        }
    }
    
    /**
     * Returns the user's register Gigya login provider.
     *
     * @param int $userId The unique identifier for the user's login provider.
     */
    function usersLoginProvider($userId = false) {
        if (!is_user_logged_in()) {
            return '';
        }
        if (false === $userId) {
            $user = wp_get_current_user();
            $userId = $user->ID;
        }
        return get_usermeta($userId, $this->_meta_SocializeLoginProvider, true);
    }
}
?>
