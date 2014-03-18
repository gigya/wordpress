<?php
/**
 * Plugin Name: Gigya
 * Plugin URI: http://gigya.com
 * Description: Allows sites to utilize the Gigya API for authentication and social network updates.
 * Version: 5.0.0
 * Author: Gigya
 * Author URI: http://gigya.com
 * License: GPL2+
 */
define( 'GIGYA__MINIMUM_WP_VERSION',  '3.5'                       );
define( 'GIGYA__MINIMUM_PHP_VERSION', '5.2'                       );
define( 'GIGYA__VERSION',             '5.0.0'                     );
define( 'GIGYA__PLUGIN_DIR',          plugin_dir_path( __FILE__ ) );

require_once( GIGYA__PLUGIN_DIR . 'sdk/GSSDK.php'               );
require_once( GIGYA__PLUGIN_DIR . 'class/class.GigyaUser.php'   );