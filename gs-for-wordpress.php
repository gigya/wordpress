<?php
/*
Plugin Name: Gigya Socialize - Increase Registration and Engagement using Facebook Connect, Twitter and OpenID
Plugin URI: http://gigya.com
Description: Integrates a variety of features of the Gigya Socialize service into a WordPress blog.
Author: Nick Ohrn of Plugin-Developer.com
Version: 1.1.3
Author URI: http://plugin-developer.com
*/

if( !class_exists( 'GigyaSocializeForWordPress' ) ) {
	
	class GigyaSocializeForWordPress {

		// PROVIDER INFORMATION
		

		/**
		 * An array of login provider names that allow for the invitation of friends.
		 *
		 * @var array
		 */
		var $inviteFriendsValidNetworks = array( 'facebook', 'twitter' );

		/**
		 * An array of login provider names that allow for status updates.
		 *
		 * @var array
		 */
		var $updateStatusValidNetworks = array( 'myspace', 'facebook', 'twitter' );

		// META
		

		/**
		 * Meta name for usermeta boolean indicator showing whether the user connected with Gigya Socialize in the past.
		 *
		 * @var string
		 */
		var $_metaHasConnectedWithGigya = '_has_connected_with_gigya';

		/**
		 * Meta name for usermeta string indicator of which Gigya Socialize login provider the user is utilizing.
		 *
		 * @var string
		 */
		var $_metaSocializeLoginProvider = '_gigya_socialize_login_provider';

		/**
		 * Meta name for usermeta string indicator of what the thumbnail URL is for the user's profile photo.
		 *
		 * @var unknown_type
		 */
		var $_metaSocializeThumbnailUrl = '_gigya_socialize_thumbnail_url';

		/**
		 * Meta name for usermeta string indicator of what the last comment posted by the user was.
		 *
		 * @var string
		 */
		var $_metaRecentCommentPostedId = '_last_comment_post_id';

		// SETTINGS
		

		/**
		 * Default settings for the plugin.  These settings are used when no settings have yet been saved by the user.
		 *
		 * @var array
		 */
		var $defaults = array();

		// MISC
		

		/**
		 * Contains the computed result of the HTTP location of this plugin's folder.  This is a URL like 
		 * http://myblog.com/wp-content/gs-for-wordpress without the trailing slash.  This is computed in 
		 * the plugin constructor
		 *
		 * @var string
		 */
		var $pluginFolder;

		/**
		 * The computed value of the jQuery location for the currently active WordPress install.  This is computed in the plugin 
		 * constructor.
		 *
		 * @var string 
		 */
		var $jQueryLocation;

		/**
		 * The location of the Gigya Socialize JavaScript API.
		 *
		 * @var string
		 */
		var $socializeJsLocation = 'http://cdn.gigya.com/JS/gigya.js?services=socialize';

		/**
		 * A string containing the version for this plugin.  Always update this when releaseing a new version.
		 *
		 * @var string
		 */
		var $version = '1.1.3';

		/**
		 * Adds all the appropriate actions and filters.
		 *
		 * @return GigyaSocializeForWordPress
		 */
		function GigyaSocializeForWordPress() {
			add_action( 'admin_menu', array( &$this, 'addAdministrativePage' ) );
			add_action( 'comment_post', array( &$this, 'commentPost' ) );
			add_action( 'wp_set_comment_status', array( &$this, 'commentPost' ), 10, 2 );
			add_action( 'login_head', array( &$this, 'loginPageOutput' ) );
			add_action( 'wp_head', array( &$this, 'addGigyaScriptToBlog' ) );
			add_action( 'admin_head', array( &$this, 'addGigyaScriptToBlog' ) );
			add_action( 'profile_personal_options', array( &$this, 'inviteFriendsUI' ) );
			add_action( 'init', array( &$this, 'savePluginSettings' ) );
			add_action( 'init', array( &$this, 'loginUser' ) );
			add_action( 'widgets_init', array( &$this, 'registerWidget' ) );
			
			add_filter( 'get_avatar', array( &$this, 'changeAvatarImage' ), 10, 5 );
			
			$this->pluginFolder = WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) );
			$this->jQueryLocation = get_bloginfo( 'wpurl' ) . '/wp-includes/js/jquery/jquery.js';
		}

		/// CALLBACKS
		

		/**
		 * Registers a new administrative page which displays the settings panel.
		 *
		 */
		function addAdministrativePage() {
			add_options_page( __( 'Gigya Socialize' ), __( 'Gigya Socialize' ), 'manage_options', 'gigya-socialize', array( $this, 'displaySettingsPage' ) );
		}

		/**
		 * Outputs the gigya script link to the blog header.  Using this instead of wp_enqueue_script because the gigya script
		 * doesn't like getting called with a version parameter like WordPres appends.
		 *
		 */
		function addGigyaScriptToBlog() {
			$settings = $this->getSettings( );
			$apiKey = $settings[ 'gs-for-wordpress-api-key' ];
			echo "<script type='text/javascript' src='{$this->socializeJsLocation}'></script>\n";
			echo "<script type='text/javascript'>\nvar gsConf = { APIKey: '$apiKey' };\n</script>";
			echo "<link rel='stylesheet' href='{$this->pluginFolder}/resources/gs-for-wordpress.css?ver={$this->version}' type='text/css' media='' />";
			if( is_user_logged_in( ) && $this->userHasGigyaConnection( ) ) {
				$user = wp_get_current_user( );
				$commentId = get_usermeta( $user->ID, $this->_metaRecentCommentPostedId, true );
				if( !empty( $commentId ) ) {
					delete_usermeta( $user->ID, $this->_metaRecentCommentPostedId );
					$usersComment = get_comment( $commentId );
					$status = htmlentities( sprintf( __( 'I just commented on %s: %s (%s)' ), get_bloginfo( ), html_entity_decode( get_comment_link( $usersComment ) ), get_the_title( $usersComment->comment_post_ID ) ) );
					include ( 'views/comment-notification.php' );
				}
			}
		}

		/**
		 * Intercepts avatar output and replaces the default source with the one from the Gigya profile.
		 *
		 * @param string $avatar An image tag for output
		 * @param int|string $id_or_email User id or email address.
		 * @param int $size Size in pixels for the image
		 * @param string $default The location of the default image.
		 * @param string $alt The alt text to use.
		 */
		function changeAvatarImage( $avatar, $id_or_email, $size, $default, $alt ) {
			if( is_object( $id_or_email ) ) {
				$id_or_email = $id_or_email->user_id;
			}
			if( is_numeric( $id_or_email ) ) {
				$thumb = $this->getUserThumbnail( $id_or_email );
				if( !empty( $thumb ) ) {
					$avatar = preg_replace( "/src='*?'/", "src='$thumb'", $avatar );
				}
			}
			return $avatar;
		}

		/**
		 * If a user is associated with a Gigya ID and they are logged in to their account, then save some user meta indicating that 
		 * they should have a notification sent on the next page load. 
		 *
		 * @param int $commentId The comment ID for a comment in the WordPress system.
		 * @param mixed $approvalStatus The status of the comment 1/0 for approved/unapproved and spam for spam
		 */
		function commentPost( $commentId, $approval = null ) {
			$comment = get_comment( $commentId );
			$approvalStatus = $comment->comment_approved;
			if( $this->userHasGigyaConnection( $comment->user_id ) && $approvalStatus == 1 || $approval == 'approve' ) {
				update_usermeta( $comment->user_id, $this->_metaRecentCommentPostedId, $commentId );
			}
		}

		/**
		 * Includes the invite friends UI elements.
		 *
		 * @param object $user A WordPress user object.
		 */
		function inviteFriendsUI( $user ) {
			include ( 'views/admin/invite-friends.php' );
		}

		/**
		 * Prints out the appropriate script and style tags to the login page to create the Gigya login box. 
		 *
		 */
		function loginPageOutput() {
			$settings = $this->getSettings( );
			$loginComponentCodeIsEmpty = empty( $settings[ 'gs-for-wordpress-sign-in-component-ui' ] );
			include ( 'views/login.php' );
		}

		/**
		 * Tries to login the user after they have used the gigya login mechnism.
		 *
		 * If the authentication hash matches, then the user is either logged in or a new user is created.  If a user cannot be 
		 * 
		 */
		function loginUser() {
			if( 1 == $_POST[ 'gigya-authenticate' ] && isset( $_POST[ 'gigya-timestamp' ] ) && isset( $_POST[ 'gigya-uid' ] ) ) {
				$message = __( 'Unknown error.' );
				$redirect = '';
				
				$hash = $this->generateAuthenticationHash( $_POST[ 'gigya-timestamp' ], $_POST[ 'gigya-uid' ] );
				if( $hash === $_POST[ 'gigya-signature' ] ) {
					if( is_user_logged_in( ) ) {
						// Connect current user account
						$user = wp_get_current_user( );
						$this->editUserData( $_POST, $user->ID );
						$this->registerGigyaData( $_POST, $user->ID );
						$message = __( 'Your WordPress account has been connected to your Social Network account.' );
						$redirect = $_POST[ 'redirect-url' ];
					} else {
						$user = $this->userHasPreviouslyConnectedViaGigya( $_POST );
						if( false === $user ) {
							$allowRegistration = get_option( 'users_can_register' );
							if( '0' == $allowRegistration ) {
								$message = __( 'New user access is currently disabled for this site.' );
								$redirect = '';
							} else {
								$userData = $this->registerNewGigyaUser( $_POST );
								if( false === $userData ) {
									$message = __( 'Unable to log you in because that Social Network account has no identifying information associated with it.' );
									$redirect = '';
								} else {
									$user = $userData[ 'ID' ];
								}
							}
						}
						
						if( is_numeric( $user ) ) {
							$message = sprintf( __( 'You are being logged in and will be redirected within 10 seconds.  If you are not redirected, please <a href="%1$s">click here</a>.' ), $_POST[ 'redirect-url' ] );
							$redirect = $_POST[ 'redirect-url' ];
							set_current_user( $user );
							wp_set_auth_cookie( $user, true );
							do_action( 'wp_login', $user[ 'name' ] );
						}
					}
				} else {
					$message = sprintf( __( 'Experienced an unsuccessful authentication.  Please try again.' ) );
					$redirect = '';
				}
				header( 'Content-type: application/json' );
				echo json_encode( array( 'message' => $message, 'redirect' => $redirect ) );
				exit( );
			}
		}

		/**
		 * Registers the friend notifier widget. 
		 *
		 */
		function registerWidget() {
			wp_register_sidebar_widget( 'gs-for-wordpress-widget', __( 'Gigya Socialize Login/Status Updates' ), array( &$this, 'widgetOutput' ) );
			wp_register_widget_control( 'gs-for-wordpress-widget', __( 'Gigya Socialize Login/Status Updates' ), array( &$this, 'widgetControlOutput' ) );
		}

		/**
		 * Attempts to intercept a POST request that is saving the settings for the GS for WordPress plugin. 
		 *
		 */
		function savePluginSettings() {
			$settings = $this->getSettings( );
			if( is_admin( ) && isset( $_POST[ 'save-gs-for-wordpress-settings' ] ) && check_admin_referer( 'save-gs-for-wordpress-settings' ) ) {
				$settings[ 'gs-for-wordpress-api-key' ] = trim( htmlentities( strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-api-key' ] ) ) ) );
				$settings[ 'gs-for-wordpress-secret-key' ] = trim( htmlentities( strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-secret-key' ] ) ) ) );
				$settings[ 'gs-for-wordpress-friend-notification-title' ] = htmlentities( strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-friend-notification-title' ] ) ) );
				$settings[ 'gs-for-wordpress-friend-notification-content' ] = strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-friend-notification-content' ] ), '<a><em><strong>' );
				$settings[ 'gs-for-wordpress-friend-selector-component-ui' ] = stripslashes( $_POST[ 'gs-for-wordpress-friend-selector-component-ui' ] );
				$settings[ 'gs-for-wordpress-widget-sign-in-component-ui' ] = stripslashes( $_POST[ 'gs-for-wordpress-widget-sign-in-component-ui' ] );
				$settings[ 'gs-for-wordpress-sign-in-component-ui' ] = stripslashes( $_POST[ 'gs-for-wordpress-sign-in-component-ui' ] );
				$this->saveSettings( $settings );
				wp_redirect( 'options-general.php?page=gigya-socialize&updated=true' );
				exit( );
			}
			if( !is_admin( ) ) {
				wp_enqueue_script( 'gs-for-wordpress', $this->pluginFolder . '/resources/gs-for-wordpress.js', array( 'jquery' ), $this->version );
			}
		}

		/// WIDGET
		

		/**
		 * Handles the output for the widget for the GS for WordPress widget.
		 *
		 */
		function widgetOutput( $args ) {
			extract( $args, EXTR_SKIP );
			$settings = $this->getSettings( );
			$options = $settings[ 'widget' ];
			include ( 'views/widget/widget.php' );
		}

		/**
		 * Handles the output for the control for the GS for WordPress widget.
		 *
		 */
		function widgetControlOutput() {
			$settings = $this->getSettings( );
			if( isset( $_POST[ 'gs-for-wordpress-submit' ] ) ) {
				$settings[ 'widget' ][ 'title' ] = strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-title' ] ) );
				$settings[ 'widget' ][ 'wordpress-header' ] = strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-login-message' ] ) );
				$settings[ 'widget' ][ 'socialize-header' ] = strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-socialize-login-message' ] ) );
				$settings[ 'widget' ][ 'invite-friends' ] = strip_tags( stripslashes( $_POST[ 'gs-for-wordpress-invite-friends-message' ] ) );
			}
			$this->saveSettings( $settings );
			$title = attribute_escape( $settings[ 'widget' ][ 'title' ] );
			$wordpressHeader = attribute_escape( $settings[ 'widget' ][ 'wordpress-header' ] );
			$socializeHeader = attribute_escape( $settings[ 'widget' ][ 'socialize-header' ] );
			$inviteFriends = attribute_escape( $settings[ 'widget' ][ 'invite-friends' ] );
			include ( 'views/widget/widget-control.php' );
		}

		/**
		 * Outputs the correct HTML for the settings page.
		 *
		 */
		function displaySettingsPage() {
			if( $_GET[ 'help' ] == '1' ) {
				include ( 'views/admin/help.php' );
			} else {
				include ( 'views/admin/settings.php' );
			}
		}

		/// SETTINGS
		

		/**
		 * Removes the settings for the GS for WordPress plugin from the database.
		 *
		 */
		function deleteSettings() {
			delete_option( 'GS for WordPress Settings' );
		}

		/**
		 * Returns the settings for the GS for WordPress plugin.
		 *
		 * @return array An associative array of settings for the GS for WordPress plugin.
		 */
		function getSettings() {
			if( $this->settings === null ) {
				$this->settings = get_option( 'GS for WordPress Settings', $this->defaults );
			}
			return $this->settings;
		}

		/**
		 * Saves the settings for the GS for WordPress plugin.
		 *
		 * @param array $settings An array of settings for the GS for WordPress plugin.
		 */
		function saveSettings( $settings ) {
			$this->settings = $settings;
			update_option( 'GS for WordPress Settings', $this->settings );
		}

		/// UTILITIES
		

		/**
		 * Edits the user data for a user connecting via GS.
		 *
		 * @param array $gigyaData An associative array of data from the GS login process.
		 * @param int $userId The unique identifier for the user.
		 */
		function editUserData( $gigyaData, $userId ) {
			$_POST[ 'first_name' ] = 'null' == $gigyaData[ 'gigya-first-name' ] ? '' : $gigyaData[ 'gigya-first-name' ];
			$_POST[ 'last_name' ] = 'null' == $gigyaData[ 'gigya-last-name' ] ? '' : $gigyaData[ 'gigya-last-name' ];
			$_POST[ 'display_name' ] = 'null' == $gigyaData[ 'gigya-nickname' ] ? '' : $gigyaData[ 'gigya-nickname' ];
			$_POST[ 'url' ] = 'null' == $gigyaData[ 'gigya-profile-url' ] ? '' : $gigyaData[ 'gigya-profile-url' ];
			if( !function_exists( 'edit_user' ) ) {
				include ( ABSPATH . '/wp-admin/includes/user.php' );
			}
			edit_user( $userId );
			update_usermeta( $userId, $this->_metaSocializeThumbnailUrl, $gigyaData[ 'gigya-thumbnail-url' ] );
		}

		/**
		 * Generates an authentication hash from a timestamp and UID.
		 *
		 * @param int $timestamp The timestamp at which authentication was request.
		 * @param string $uid The identifier for a particular user.
		 * @return string An authentication hash based on the secret key stored.
		 */
		function generateAuthenticationHash( $timestamp, $uid ) {
			$settings = $this->getSettings( );
			$secretKey = $settings[ 'gs-for-wordpress-secret-key' ];
			$hash = $this->HMAC_SHA1( base64_decode( $secretKey ), $timestamp . '_' . $uid );
			return base64_encode( $hash );
		}

		function getMainLoginUIComponentCode() {
			$settings = $this->getSettings( );
			$default = "
			var conf = {
			     'APIKey': '{$settings['gs-for-wordpress-api-key']}',  
			     'enabledProviders': '*'  
			};
			var login_params = {
				showTermsLink:false,   
				headerText:'Or login using',
				height:163,   
				width:278,
				UIConfig:'<config><body><controls><snbuttons buttonsize=\"42\"></snbuttons></controls><background frame-color=\"#FFFFFF\"></background></body></config>',  
				containerID:'componentDiv'
			};";
			return empty( $settings[ 'gs-for-wordpress-sign-in-component-ui' ] ) ? $default : $settings[ 'gs-for-wordpress-sign-in-component-ui' ];
		}

		function getWidgetLoginUIComponentCode() {
			$settings = $this->getSettings( );
			$socializeHeader = empty( $settings[ 'widget' ][ 'socialize-header' ] ) ? __( 'Or login using' ) : $settings[ 'widget' ][ 'socialize-header' ];
			$default = "
			var conf = {
			     'APIKey': '{$settings['gs-for-wordpress-api-key']}',  
			     'enabledProviders': '*'  
			};
			var login_params = {
				showTermsLink:false,
				headerText:'{$socializeHeader}',
				height:100,
				width:190,
				UIConfig:'<config><body><controls><snbuttons buttonsize=\"28\"></snbuttons></controls><background frame-color=\"#FFFFFF\"></background></body></config>',
				containerID:'componentDiv'
			};";
			return empty( $settings[ 'gs-for-wordpress-widget-sign-in-component-ui' ] ) ? $default : $settings[ 'gs-for-wordpress-widget-sign-in-component-ui' ];
		}

		function getFriendSelectorComponentCode() {
			$settings = $this->getSettings( );
			$code = trim( $settings[ 'gs-for-wordpress-friend-selector-component-ui' ] );
			if( !empty( $code ) ) {
				return ",\n" . $code;
			} else {
				return '';
			}
		}

		/**
		 * Returns the meta name for a particular network (for the usermeta table).
		 *
		 * @param string $network The network to retreive information for.
		 */
		function getMetaNameForNetwork( $network ) {
			return preg_replace( '|[^a-z0-9_]|i', '', '_gs-for-wordpress-uid-' . sanitize_title_with_dashes( $network ) );
		}

		/**
		 * Returns a string indicating the location of the user's thumbnail picture on a SN.
		 *
		 * @param int $userId The unique identifier for a user.
		 * @return string Returns an absolute URL or an empty string if no thumbnail url is saved.
		 */
		function getUserThumbnail( $userId ) {
			return get_usermeta( $userId, $this->_metaSocializeThumbnailUrl );
		}

		/**
		 * Generates a valid HMAC_SHA1 hash without any PEAR dependencies.  Hat tip to http://laughingmeme.org/tag/hmac-sha1/
		 *
		 * @param string $key The secret key for the HMAC-SHA1 hashing.
		 * @param string $data The data to hash.
		 * @return string The computed HMAC-SHA1 hash.
		 */
		function HMAC_SHA1( $key, $data ) {
			$blocksize = 64;
			$hashfunc = 'sha1';
			if( strlen( $key ) > $blocksize ) {
				$key = pack( 'H*', $hashfunc( $key ) );
			}
			$key = str_pad( $key, $blocksize, chr( 0x00 ) );
			$ipad = str_repeat( chr( 0x36 ), $blocksize );
			$opad = str_repeat( chr( 0x5c ), $blocksize );
			$hmac = pack( 'H*', $hashfunc( ( $key ^ $opad ) . pack( 'H*', $hashfunc( ( $key ^ $ipad ) . $data ) ) ) );
			return $hmac;
		}

		/**
		 * Registers the fact that a user has connected via Gigya.
		 *
		 * @param array $gigyaData An associative array of data from GS.
		 * @param int $userId The unique identifier for the user.
		 */
		function registerGigyaData( $gigyaData, $userId ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES( %d, %s, %s )", $userId, $gigyaData[ 'gigya-uid' ], $this->getMetaNameForNetwork( $gigyaData[ 'gigya-login-provider' ] ) ) );
			update_usermeta( $userId, $this->_metaSocializeLoginProvider, $gigyaData[ 'gigya-login-provider' ] );
			update_usermeta( $userId, $this->_metaHasConnectedWithGigya, '1' );
			update_usermeta( $userId, $this->_metaSocializeThumbnailUrl, $gigyaData[ 'gigya-thumbnail-url' ] );
		}

		/**
		 * Registers a new user from the information provided via the Gigya login component.
		 *
		 * @param array $gigyaData An associative array of data from the login component.
		 */
		function registerNewGigyaUser( $gigyaData ) {
			require_once ( ABSPATH . WPINC . '/registration.php' );
			$nick = sanitize_user( $gigyaData[ 'gigya-nickname' ] );
			if( $nick === '' ) {
				$nick = ucfirst( $gigyaData[ 'gigya-login-provider' ] ) . 'User';
			}
			$user = $nick;
			$counter = 0;
			while( username_exists( $user ) ) {
				$user = $nick . $counter;
				$counter++;
			}
			$pass = wp_generate_password( );
			if( 'null' != $gigyaData[ 'gigya-email' ] ) {
				$email = $gigyaData[ 'gigya-email' ];
			} else {
				$email = '';
			}
			
			$userData = array( 'user_login' => $user, 'user_pass' => $pass, 'user_email' => $email );
			$userData[ 'user_nickname' ] = $gigyaData[ 'gigya-nickname' ];
			$userData[ 'first_name' ] = 'null' == $gigyaData[ 'gigya-first-name' ] ? '' : $gigyaData[ 'gigya-first-name' ];
			$userData[ 'last_name' ] = 'null' == $gigyaData[ 'gigya-last-name' ] ? '' : $gigyaData[ 'gigya-last-name' ];
			$userData[ 'display_name' ] = 'null' == $gigyaData[ 'gigya-nickname' ] ? '' : $gigyaData[ 'gigya-nickname' ];
			$userData[ 'user_url' ] = 'null' == $gigyaData[ 'gigya-profile-url' ] ? '' : $gigyaData[ 'gigya-profile-url' ];
			$userId = wp_insert_user( $userData );
			$userData = array( 'ID' => $userId, 'name' => $user, 'pass' => $pass );
			$this->registerGigyaData( $gigyaData, $userId );
			return $userData;
		}

		/**
		 * Returns a boolean indicating whether the current user has a gigya login provider UID.
		 *
		 * @param int|bool $userId The unique identifier for a user or false to use the current user.
		 * @return bool Whether or not the current user has a gigya login provider UID.
		 */
		function userHasGigyaConnection( $userId = false ) {
			if( !is_user_logged_in( ) ) {
				return false;
			}
			if( false === $userId ) {
				$user = wp_get_current_user( );
				$userId = $user->ID;
			}
			return get_usermeta( $userId, $this->_metaHasConnectedWithGigya, true ) == '1';
		}

		/**
		 * Returns a boolean value indicating whether a user has previously connected via Gigya Socialize.
		 *
		 * @uses $wpdb
		 * 
		 * @param array $gigyaData An array of all the pertinent data returned from the Gigya login call.
		 * @return int|bool False if the user has never connected and the user id of the user if they have.
		 */
		function userHasPreviouslyConnectedViaGigya( $gigyaData ) {
			$loginProvider = $this->getMetaNameForNetwork( $gigyaData[ 'gigya-login-provider' ] );
			global $wpdb;
			$userId = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", $gigyaData[ 'gigya-uid' ], $loginProvider ) );
			if( !$userId ) {
				return false;
			} else {
				$this->editUserData( $gigyaData, $userId );
				return (int) $userId;
			}
		}

		/**
		 * Returns the user's register Gigya login provider.
		 *
		 * @param int $userId The unique identifier for the user's login provider.
		 */
		function usersLoginProvider( $userId = false ) {
			if( !is_user_logged_in( ) ) {
				return '';
			}
			if( false === $userId ) {
				$user = wp_get_current_user( );
				$userId = $user->ID;
			}
			return get_usermeta( $userId, $this->_metaSocializeLoginProvider, true );
		}
	}
}

if( class_exists( 'GigyaSocializeForWordPress' ) ) {
	$gsfw = new GigyaSocializeForWordPress( );
}

if( !function_exists( 'json_encode' ) ) // Required to make this plugin compatible with PHP < 5.2
{

	function json_encode( $a = false ) {
		if( is_null( $a ) ) {
			return 'null';
		} 
		if( $a === false ) {
			return 'false';
		}
		if( $a === true ) {
			return 'true';
		}
		if( is_scalar( $a ) ) {
			if( is_float( $a ) ) {
				// Always use "." for floats.
				return floatval( str_replace( ",", ".", strval( $a ) ) );
			}
			
			if( is_string( $a ) ) {
				static $jsonReplaces = array( array( "\\", "/", "\n", "\t", "\r", "\b", "\f", '"' ), array( '\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"' ) );
				return '"' . str_replace( $jsonReplaces[ 0 ], $jsonReplaces[ 1 ], $a ) . '"';
			} else {
				return $a;
			}
		}
		$isList = true;
		for( $i = 0, reset( $a ); $i < count( $a ); $i++, next( $a ) ) {
			if( key( $a ) !== $i ) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if( $isList ) {
			foreach( $a as $v ) {
				$result[ ] = json_encode( $v );
			}
			return '[' . join( ',', $result ) . ']';
		} else {
			foreach( $a as $k => $v )
				$result[ ] = json_encode( $k ) . ':' . json_encode( $v );
			return '{' . join( ',', $result ) . '}';
		}
	}
}
