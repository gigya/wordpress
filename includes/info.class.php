<?php 
/** 
 * Holds various information about the plugin and it's environment.
 */
class GigyaInfo {

    /**
     * Contains the computed result of the HTTP location of the WP installs jQuery.  This is a URL like
     * http://myblog.com/wp-includes/js/jquery.js.  This is computed in the class constructor.
     * @var string
     */
    var $jQueryUrl;
    
    /**
     * Contains the computed result of the server location of this plugin's folder.  This is an absolute path
     * like /var/www/html/myblog.com/wp-content/plugins/gs-for-wordpress without the trailing slash.  This is computed in
     * the class constructor
     * @var string
     */
    var $pluginFolder;
    
    /**
     * Contains the computed result of the HTTP location of this plugin's folder.  This is a URL like
     * http://myblog.com/wp-content/plugins/gs-for-wordpress without the trailing slash.  This is computed in
     * the class constructor
     * @var string
     */
    var $pluginUrl;
    
    /**
     * The location of the Gigya Socialize JavaScript API.
     * @var string
     */
    var $socializeUrl = 'http://cdn.gigya.com/JS/gigya.js?services=socialize';
    
    /**
     * The current version of the plugin.  Always takes the form x.x.x.(beta|alpha)
     * @var string
     */
    var $version = '1.2.0';
    
    public function GigyaInfo() {
        $name = basename(dirname(dirname(__FILE__)));
        $this->pluginFolder = WP_PLUGIN_DIR."/$name";
        $this->pluginUrl = WP_PLUGIN_URL."/$name";
        $this->jQueryUrl = site_url('/wp-includes/js/jquery/jquery.js');
    }
}
?>
