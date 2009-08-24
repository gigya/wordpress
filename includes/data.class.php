<?php 
/**
 * Encapsulates general data saving and what not.
 */
class GigyaData {

    var $settings = null;
    
    /** 
     * Generates an authentication hash from a timestamp and UID.
     *
     * @param int $timestamp The timestamp at which authentication was request.
     * @param string $uid The identifier for a particular user (from Gigya's services).
     * @return string An authentication hash based on the secret key stored.
     */
    function generateAuthenticationHash($timestamp, $uid) {
        $settings = $this->getSettings();
        $secretKey = $settings['gs-for-wordpress-secret-key'];
        $hash = $this->HMAC_SHA1(base64_decode($secretKey), $timestamp.'_'.$uid);
        return base64_encode($hash);
    }
    
    /**
     * Generates a valid HMAC_SHA1 hash without any PEAR dependencies.  Hat tip to http://laughingmeme.org/tag/hmac-sha1/
     *
     * @param string $key The secret key for the HMAC-SHA1 hashing.
     * @param string $data The data to hash.
     * @return string The computed HMAC-SHA1 hash.
     */
    function HMAC_SHA1($key, $data) {
        $blocksize = 64;
        $hashfunc = 'sha1';
        if (strlen($key) > $blocksize) {
            $key = pack('H*', $hashfunc($key));
        }
        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack('H*', $hashfunc(($key ^ $opad).pack('H*', $hashfunc(($key ^ $ipad).$data))));
        return $hmac;
    }
    
    /**
     * Returns sanitized configuration code for the friend selector component.  If the user copies code directly from the
     * Gigya website, than the plugin will not work.  This method attempts to remove extra stuff from that code for users who
     * don't follow the HELP directions.
     *
     * @param string $configuration A string that should be sanitized (if possible) for the friend selector configuration field.
     * @return string Reworked configuration code for the friend selector.  If the code could be sanitized, then the return value will be sanitized.
     */
    function sanitizeConfigurationForFriendSelector($configuration) {
        $matchingRegularExpression = '/"UIConfig":"(.*)"/';
        $numberMatches = preg_match($matchingRegularExpression, $configuration, $matches);
        if (isset($matches[1])) {
            return '"UIConfig":"'.$matches[1].'"';
        }
        return $configuration;
    }
    
    /**
     * This method sanitizes a raw string inserted entered by the user to attempt to strip out unneeded stuff that the user entered by mistake.
     *
     * @param string $configuration A string that contains the user's attempt at entering configuration code for a connect widget.
     * @return string Reworked configuration code that has been sanitized to account for incorrect user input.
     */
    function sanitizeConfigurationForConnectWidget($configuration) {
        $emptyString = '';
        
        $beginScriptTagRegularExpression = '/<script.*?>/';
        $endScriptTagRegularExpression = '/<\/script>/';
        $sanitized = preg_replace(array($beginScriptTagRegularExpression, $endScriptTagRegularExpression), $emptyString, $configuration);
        
        $sanitized = str_replace(array('gigya.services.socialize.showLoginUI(conf,login_params);', '<div id="componentDiv"></div>'), $emptyString, $sanitized);
        
        // Adds a semicolon to finish the variable definition for the params object
        $sanitized = preg_replace('/}(\r)/', '};$1', $sanitized);
        
        return trim($sanitized);
    }
    
    /// GETTERS/SETTERS
    
    /**
     * Returns the settings for the Gigya Socialize for WordPress plugin.
     *
     * @return An associative array of settings.
     */
    function getSettings() {
        if (is_null($this->settings)) {
            $this->settings = get_option('GS for WordPress Settings', array());
        }
        if (!is_array($this->settings)) {
            $this->settings = array();
        }
        return $this->settings;
    }
    
    /**
     * Saves the settings for the Gigya Socialize for WordPress plugin to the database.
     *
     * @param array $settings An associative array of settings for the plugin.  Expected to contain the elements declared in the documentation for getSettings.
     * @return void
     */
    function saveSettings($settings) {
        if (!is_array($settings)) {
            return new WP_Error(__('Settings must be an associative array.'));
        }
        $this->settings = $settings;
        update_option('GS for WordPress Settings', $this->settings);
    }
    
}
?>
