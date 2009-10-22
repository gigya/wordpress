<?php 
/**
 * Encapsulates user operations.
 */
class GigyaUser {
    /**
     * Meta name for usermeta boolean indicator showing whether the user connected with Gigya Socialize sometime in the past.
     * @var string
     */
    var $_meta_HasConnectedWithGigya = '_has_connected_with_gigya';
    var $_meta_SocializeLoginProviders = '_gigya_socialize_login_provider';
    var $_meta_SocializeThumbnailUrl = '_gigya_socialize_thumbnail_url';
    var $_meta_RecentCommentPostedId = '_last_comment_post_id';
    var $_meta_RecentLogoutPosted = '_recently_logged_out';
    
    function hasThumbnailUrl($userId) {
        $thumbUrl = get_usermeta($userId, $this->_meta_SocializeThumbnailUrl);
        return ! empty($thumbUrl);
    }
    
    function getThumbnailUrl($userId) {
        $thumbUrl = get_usermeta($userId, $this->_meta_SocializeThumbnailUrl);
        return $thumbUrl;
    }
    
    function setThumbnailUrl($userId, $thumbnailUrl) {
        update_usermeta($userId, $this->_meta_SocializeThumbnailUrl, $thumbnailUrl);
    }
    
    function setHasConnected($userId) {
        update_usermeta($userId, $this->_meta_HasConnectedWithGigya, '1');
    }
    
    function addLoginProvider($userId, $loginProvider) {
        $providers = $this->getLoginProviders($userId);
        if (!in_array($loginProvider, $providers)) {
            $providers[] = $loginProvider;
        }
        update_usermeta($userId, $this->_meta_SocializeLoginProviders, $providers);
    }
    
    /**
     * Returns an array of login provider strings for the specified user.
     * @param int $userId The unique ID for the user.
     * @return array An array of network identifier strings.
     */
    function getLoginProviders($userId) {
        $providers = get_usermeta($userId, $this->_meta_SocializeLoginProviders);
        if ( empty($providers)) {
            $providers = array();
        } elseif (!is_array($providers)) {
            $providers = array($providers);
        }
        return $providers;
    }
    
    /**
     * @return array An array of network identifier strings.
     */
    function getCurrentUserLoginProviders() {
        if (!is_user_logged_in()) {
            return array();
        }
        $user = wp_get_current_user();
        return $this->getLoginProviders($user->ID);
    }
    
    /**
     * Returns a boolean indicating whether the current user has a gigya login provider UID.
     *
     * @param int|bool $userId The unique identifier for a user or false to use the current user.
     * @return bool Whether or not the current user has a gigya login provider UID.
     */
    function hasGigyaConnection($userId = false) {
        if ($false === $userId) {
            $user = get_userdata($userId);
        }
        $user = wp_get_current_user();
        return get_usermeta($user->ID, $this->_meta_HasConnectedWithGigya, true) == '1';
    }
    
    /**
     * @return int 0 if the user has never connected with Gigya or the user's ID if they have.
     */
    function hasPreviouslyConnected($uid, $network) {
        global $wpdb;
        $userId = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", $uid, $network));
        return $userId ? (int) $userId : 0;
    }
    
    function registerData($data, $userId) {
        global $wpdb;
        /// HAVE TO DO IT THIS WAY BECAUSE OF API FAULTS
        $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES( %d, %s, %s )", $userId, $data['gigya-uid'], GigyaData::getMetaNameForNetwork($data['gigya-login-provider'])));
        
        $this->addLoginProvider($userId, $data['gigya-login-provider']);
        $this->setHasConnected($userId);
        $this->setThumbnailUrl($userId, $data['gigya-thumbnail-url']);
    }

    
    function editData($data, $userId) {
        $this->setThumbnailUrl($userId, $data['gigya-thumbnail-url']);
        
        $_POST['first_name'] = $this->sanitizeNullString($data['gigya-first-name']);
        $_POST['last_name'] = $this->sanitizeNullString($data['gigya-last-name']);
        $_POST['display_name'] = $this->sanitizeNullString($data['gigya-nickname']);
        $_POST['url'] = $this->sanitizeNullString($data['gigya-profile-url']);
        if (!function_exists('edit_user')) {
            include (ABSPATH.'/wp-admin/includes/user.php');
        }
        edit_user($userId);
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
        $email = $this->sanitizeEmail($data['gigya-email']);
        $password = wp_generate_password();
        
        $userData = array('user_login'=>$username, 'user_pass'=>$password, 'user_email'=>$email);
        $userData['user_nickname'] = $username;
        $userData['first_name'] = $this->sanitizeNullString($data['gigya-first-name']);
        $userData['last_name'] = $this->sanitizeNullString($data['gigya-last-name']);
        $userData['display_name'] = $this->sanitizeNullString($data['gigya-nickname']);
        $userData['user_url'] = $this->sanitizeNullString($data['gigya-profile-url']);

        
        $userId = wp_insert_user($userData);
        $this->registerData($data, $userId);
        $this->editData($data, $userId);
        
        $user = get_userdata($userId);
        return $user;
    }
    
    /**
     * Checks to ensure that the email address is valid for registration.  If it is not, then return an empty string, otherwise
     * return the original email address.
     * @param string $email The email address to verify.
     * @return An empty string if the original email was invalid and the original email address otherwise.
     */
    function sanitizeEmail($email) {
        if (!is_email($email)) {
            $email = '';
        }
        return $email;
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
     * Returns a username that has not yet been used by a previous user and can be used to register a new user.
     * @param string $nickName The nick name to use as a base when deciding the valid nick name that can be used.
     * @return string A nick name that has yet to be used by a user.
     */
    function getUnusedUserName($userName, $loginProvider) {
        if ($userName === '') {
            $userName = ucfirst(sanitize_user($loginProvider)).'User';
        }
        $toTry = sanitize_user($userName);
        $counter = 0;
        while (username_exists($toTry)) {
            $toTry = $userName.$counter;
            $counter++;
        }
        return $toTry;
    }
    
    function setCredentials($user) {
        set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login);
    }
}
?>
