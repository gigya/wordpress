<?php 
/*
 Plugin Name: Gigya Socialize - Increase Registration and Engagement using Facebook Connect, Twitter and OpenID
 Plugin URI: http://gigya.com
 Description: Integrates a variety of features of the Gigya Socialize service into a WordPress blog.
 Author: Nick Ohrn of Plugin-Developer.com
 Version: 1.2.0alpha
 Author URI: http://plugin-developer.com
 */

if (!class_exists('GigyaSocialize')) {
    $thisDir = dirname(__FILE__);
    require_once ("{$thisDir}/includes/jsonencode.inc.php");
    require_once ("{$thisDir}/includes/data.class.php");
    require_once ("{$thisDir}/includes/info.class.php");
    require_once ("{$thisDir}/includes/network.class.php");
    require_once ("{$thisDir}/includes/user.class.php");
    
    /**
     * Main plugin class that contains most of the functionality.
     */
    class GigyaSocialize {
    
        /// COMPONENTS
        
        /**
         * @var GigyaData
         */
        var $data;
        
        /**
         * @var GigyaInfo
         */
        var $info;
        
        /**
         * @var GigyaNetwork
         */
        var $network;
        
        /**
         * @var GigyaUser
         */
        var $user;
        
        /**
         * Adds all the appropriate actions and filters.
         *
         * @return GigyaSocializeForWordPress
         */
        function GigyaSocialize() {
            $this->addActions();
            $this->addFilters();
            
            $this->data = new GigyaData();
            $this->info = new GigyaInfo();
            $this->network = new GigyaNetwork();
            $this->user = new GigyaUser();
        }
        
        /**
         * Adds all necessary actions for the plugin's correct operation.
         *
         * @return void
         */
        function addActions() {
            // ADMIN ONLY
            add_action('admin_head', array(&$this, 'header'));
            add_action('admin_init', array(&$this, 'savePluginSettings'));
            add_action('admin_menu', array(&$this, 'addAdministrativePage'));
            add_action('profile_personal_options', array(&$this, 'inviteFriendsUI'));
            add_action('wp_set_comment_status', array(&$this, 'commentPost'), 10, 2);
            
            // OTHER
            add_action('comment_form', array(&$this, 'includeCommentFormExtra'));
            add_action('comment_post', array(&$this, 'commentPost'));
            add_action('login_head', array(&$this, 'loginPageOutput'));
            add_action('parse_request', array(&$this, 'loginUser'));
            add_action('widgets_init', array(&$this, 'registerWidget'));
            add_action('wp_head', array(&$this, 'header'));
            add_action('wp_logout', array(&$this, 'logoutStorage'));
        }
        
        /**
         * Adds all necessary filters for the plugin's correct operation.
         *
         * @return void
         */
        function addFilters() {
            add_filter('login_redirect', array(&$this, 'changeLoginRedirect'));
            add_filter('get_avatar', array(&$this, 'changeAvatarImage'), 10, 5);
        }
        
        /// CALLBACKS

        
        /**
         * Registers a new administrative page which displays the settings panel.
         *
         */
        function addAdministrativePage() {
            add_options_page(__('Gigya Socialize'), __('Gigya Socialize'), 'manage_options', 'gigya-socialize', array($this, 'displaySettingsPage'));
        }
        
        /**
         * Outputs all the necessary stuff to the theme's header, if necessary.
         */
        function header() {
            include ($this->info->pluginFolder.'/views/general/header.php');
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
        function changeAvatarImage($avatar, $id_or_email, $size, $default, $alt) {
            if (is_object($id_or_email)) {
                $id_or_email = $id_or_email->user_id;
            }
            if (is_numeric($id_or_email)) {
                $thumb = $this->getUserThumbnail($id_or_email);
                if (! empty($thumb)) {
                    $avatar = preg_replace("/src='*?'/", "src='$thumb'", $avatar);
                }
            }
            return $avatar;
        }
        
        function changeLoginRedirect($url) {
            $settings = $this->data->getSettings();
            if (! empty($settings['gs-for-wordpress-post-login-redirect'])) {
                $url = $settings['gs-for-wordpress-post-login-redirect'];
            }
            return $url;
        }
        
        /**
         * If a user is associated with a Gigya ID and they are logged in to their account, then save some user meta indicating that
         * they should have a notification sent on the next page load.
         *
         * @param int $commentId The comment ID for a comment in the WordPress system.
         * @param mixed $approvalStatus The status of the comment 1/0 for approved/unapproved and spam for spam
         */
        function commentPost($commentId, $approval = null) {
            $comment = get_comment($commentId);
            $approvalStatus = $comment->comment_approved;
            if ($this->userHasGigyaConnection($comment->user_id) && $approvalStatus == 1 || $approval == 'approve') {
                update_usermeta($comment->user_id, $this->_metaRecentCommentPostedId, $commentId);
            }
        }
        
        function includeCommentFormExtra() {
            $settings = $this->data->getSettings();
            if ($settings['gs-for-wordpress-comment-extras']) {
                if (is_user_logged_in() && $this->userHasGigyaConnection()) {
                
                } elseif (!is_user_logged_in()) {
                
                } else {
                
                }
                include ('views/comments/not-connected.php');
            }
        }
        
        /**
         * Includes the invite friends UI elements.
         *
         * @param object $user A WordPress user object.
         */
        function inviteFriendsUI($user) {
            include ('views/admin/invite-friends.php');
        }
        
        /**
         * Prints out the appropriate script and style tags to the login page to create the Gigya login box.
         *
         */
        function loginPageOutput() {
            $settings = $this->data->getSettings();
            $loginComponentCodeIsEmpty = empty($settings['gs-for-wordpress-sign-in-component-ui']);
            include ('views/login.php');
        }
        
        /**
         * Tries to login the user after they have used the gigya login mechnism.
         *
         * If the authentication hash matches, then the user is either logged in or a new user is created.  If a user cannot be
         *
         */
        function loginUser() {
            if (1 == $_POST['gigya-authenticate'] && isset($_POST['gigya-timestamp']) && isset($_POST['gigya-uid'])) {
                $message = __('Unknown error.');
                $redirect = '';
                
                $hash = $this->generateAuthenticationHash($_POST['gigya-timestamp'], $_POST['gigya-uid']);
                if ($hash === $_POST['gigya-signature']) {
                    if (is_user_logged_in()) {
                        // Connect current user account
                        $user = wp_get_current_user();
                        $this->editUserData($_POST, $user->ID);
                        $this->registerGigyaData($_POST, $user->ID);
                        $message = __('Your WordPress account has been connected to your Social Network account.');
                        $redirect = $_POST['redirect-url'];
                    } else {
                        $user = $this->userHasPreviouslyConnectedViaGigya($_POST);
                        if (false === $user) {
                            $allowRegistration = get_option('users_can_register');
                            if ('0' == $allowRegistration) {
                                $message = __('New user access is currently disabled for this site.');
                                $redirect = '';
                            } else {
                                $userData = $this->registerNewGigyaUser($_POST);
                                $user = $userData['ID'];
                            }
                        }
                        
                        if (is_numeric($user)) {
                            $message = sprintf(__('You are being logged in and will be redirected within 10 seconds.  If you are not redirected, please <a href="%1$s">click here</a>.'), $_POST['redirect-url']);
                            $redirect = $_POST['redirect-url'];
                            set_current_user($user);
                            wp_set_auth_cookie($user, true);
                            do_action('wp_login', $user['name']);
                        }
                    }
                } else {
                    $message = sprintf(__('Experienced an unsuccessful authentication.  Please try again.'));
                    $redirect = '';
                }
                header('Content-type: application/json');
                
                if ($redirect == admin_url()) {
                    $redirect = apply_filters('login_redirect', $redirect);
                }
                
                echo json_encode(array('message'=>$message, 'redirect'=>$redirect));
                exit();
            }
        }
        
        /** 
         * Stores an indicator that the users has just logged out and should be logged out of Gigya Socialize.
         *
         * @param int $userId The unique identifier for a user.
         */
        function logoutStorage() {
            add_filter('wp_redirect', create_function('$x', 'return add_query_arg( "just-logged-out", 1, $x );'));
        }
        
        /** 
         * Registers the Gigya Socialize widget.  Please note that this uses the OLD (pre 2.8) widget registration method because of requirements regarding
         * compatibility with WP versions 2.6-2.8.
         */
        function registerWidget() {
            wp_register_sidebar_widget('gs-for-wordpress-widget', __('Gigya Socialize'), array(&$this, 'widgetOutput'));
            wp_register_widget_control('gs-for-wordpress-widget', __('Gigya Socialize'), array(&$this, 'widgetControlOutput'));
        }
        
        /**
         * Attempts to intercept a POST request that is saving the settings for the GS for WordPress plugin.
         *
         */
        function savePluginSettings() {
            $settings = $this->data->getSettings();
            if (isset($_POST['save-gs-for-wordpress-settings']) && check_admin_referer('save-gs-for-wordpress-settings') && current_user_can('manage_options')) {
                $settings['gs-for-wordpress-api-key'] = trim(htmlentities(strip_tags(stripslashes($_POST['gs-for-wordpress-api-key']))));
                $settings['gs-for-wordpress-secret-key'] = trim(htmlentities(strip_tags(stripslashes($_POST['gs-for-wordpress-secret-key']))));
                $settings['gs-for-wordpress-post-login-redirect'] = trim(htmlentities(strip_tags(stripslashes($_POST['gs-for-wordpress-post-login-redirect']))));
                $settings['gs-for-wordpress-comment-extras'] = $_POST['gs-for-wordpress-comment-extras'] == 1 ? true : false;
                $settings['gs-for-wordpress-status-update-via'] = trim(htmlentities(strip_tags(stripslashes($_POST['gs-for-wordpress-status-update-via']))));
                $settings['gs-for-wordpress-friend-notification-title'] = htmlentities(strip_tags(stripslashes($_POST['gs-for-wordpress-friend-notification-title'])));
                $settings['gs-for-wordpress-friend-notification-content'] = strip_tags(stripslashes($_POST['gs-for-wordpress-friend-notification-content']), '<a><em><strong>');
                $settings['gs-for-wordpress-friend-selector-component-ui'] = $this->sanitizeCodeForFriendSelector(stripslashes($_POST['gs-for-wordpress-friend-selector-component-ui']));
                $settings['gs-for-wordpress-widget-sign-in-component-ui'] = $this->sanitizeCodeForConfig(stripslashes($_POST['gs-for-wordpress-widget-sign-in-component-ui']));
                $settings['gs-for-wordpress-sign-in-component-ui'] = $this->sanitizeCodeForConfig(stripslashes($_POST['gs-for-wordpress-sign-in-component-ui']));
                $this->data->saveSettings($settings);
                wp_redirect('options-general.php?page=gigya-socialize&updated=true');
                exit();
            }
        }
        
        /// WIDGET

        
        /**
         * Handles the output for the widget for the GS for WordPress widget.
         *
         */
        function widgetOutput($args) {
            extract($args, EXTR_SKIP);
            $settings = $this->data->getSettings();
            $options = $settings['widget'];
            include ('views/widget/widget.php');
        }
        
        /**
         * Handles the output for the control for the GS for WordPress widget.
         *
         */
        function widgetControlOutput() {
            $settings = $this->data->getSettings();
            if (isset($_POST['gs-for-wordpress-submit'])) {
                $settings['widget']['title'] = strip_tags(stripslashes($_POST['gs-for-wordpress-title']));
                $settings['widget']['wordpress-header'] = strip_tags(stripslashes($_POST['gs-for-wordpress-login-message']));
                $settings['widget']['socialize-header'] = strip_tags(stripslashes($_POST['gs-for-wordpress-socialize-login-message']));
                $settings['widget']['invite-friends'] = strip_tags(stripslashes($_POST['gs-for-wordpress-invite-friends-message']));
            }
            $this->data->saveSettings($settings);
            $title = attribute_escape($settings['widget']['title']);
            $wordpressHeader = attribute_escape($settings['widget']['wordpress-header']);
            $socializeHeader = attribute_escape($settings['widget']['socialize-header']);
            $inviteFriends = attribute_escape($settings['widget']['invite-friends']);
            include ('views/widget/widget-control.php');
        }
        
        /**
         * Outputs the correct HTML for the settings page.
         *
         */
        function displaySettingsPage() {
            if ($_GET['help'] == '1') {
                include ('views/admin/help.php');
            } else {
                include ('views/admin/settings.php');
            }
        }
        
        /// UTILITIES

        
        /**
         * Edits the user data for a user connecting via GS.
         *
         * @param array $gigyaData An associative array of data from the GS login process.
         * @param int $userId The unique identifier for the user.
         */
        function editUserData($gigyaData, $userId) {
            $_POST['first_name'] = 'null' == $gigyaData['gigya-first-name'] ? '' : $gigyaData['gigya-first-name'];
            $_POST['last_name'] = 'null' == $gigyaData['gigya-last-name'] ? '' : $gigyaData['gigya-last-name'];
            $_POST['display_name'] = 'null' == $gigyaData['gigya-nickname'] ? '' : $gigyaData['gigya-nickname'];
            $_POST['url'] = 'null' == $gigyaData['gigya-profile-url'] ? '' : $gigyaData['gigya-profile-url'];
            if (!function_exists('edit_user')) {
                include (ABSPATH.'/wp-admin/includes/user.php');
            }
            edit_user($userId);
            update_usermeta($userId, $this->_metaSocializeThumbnailUrl, $gigyaData['gigya-thumbnail-url']);
        }
        
        function getMainLoginUIComponentCode() {
            $settings = $this->data->getSettings();
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
				useFacebookConnect: 'true',
				UIConfig:'<config><body><controls><snbuttons buttonsize=\"42\"></snbuttons></controls><background frame-color=\"#FFFFFF\"></background></body></config>',
				containerID:'componentDiv'
			};";
            return empty($settings['gs-for-wordpress-sign-in-component-ui']) ? $default : $settings['gs-for-wordpress-sign-in-component-ui'];
        }
        
        function getWidgetLoginUIComponentCode() {
            $settings = $this->data->getSettings();
            $socializeHeader = empty($settings['widget']['socialize-header']) ? __('Or login using') : $settings['widget']['socialize-header'];
            $default = "
			var conf = {
			     'APIKey': '{$settings['gs-for-wordpress-api-key']}',
			     'enabledProviders': '*'
			};
			var login_params = {
				showTermsLink:false,
				headerText:'{$socializeHeader}',
				height:120,
				width:140,
				useFacebookConnect: 'true',
				UIConfig:'<config><body><controls><snbuttons buttonsize=\"33\"></snbuttons></controls><background frame-color=\"Transparent\"></background></body></config>',
				containerID:'componentDiv'
			};";
            return empty($settings['gs-for-wordpress-widget-sign-in-component-ui']) ? $default : $settings['gs-for-wordpress-widget-sign-in-component-ui'];
        }
        
        function getCommentsExtraUIComponentCode() {
            $settings = $this->data->getSettings();
            $default = "
			var comment_conf = {
			     'APIKey': '{$settings['gs-for-wordpress-api-key']}',
			     'enabledProviders': '*'
			};
			var comment_params = {
				showTermsLink:false,
				headerText:'',
				height:120,
				width:240,
				useFacebookConnect: 'true',
				UIConfig:'<config><body><controls><snbuttons buttonsize=\"33\"></snbuttons></controls><background frame-color=\"Transparent\"></background></body></config>',
				containerID:'gigya-comment-social-network-area'
			};";
            return empty($settings['gs-for-wordpress-comments-sign-in-component-ui']) ? $default : $settings['gs-for-wordpress-comments-sign-in-component-ui'];
        }
        
        function getFriendSelectorComponentCode() {
            $settings = $this->data->getSettings();
            $code = trim($settings['gs-for-wordpress-friend-selector-component-ui']);
            if (! empty($code)) {
                return ",\n".$code;
            } else {
                return '';
            }
        }
        
        /**
         * Returns the meta name for a particular network (for the usermeta table).
         *
         * @param string $network The network to retreive information for.
         */
        function getMetaNameForNetwork($network) {
            return preg_replace('|[^a-z0-9_]|i', '', '_gs-for-wordpress-uid-'.sanitize_title_with_dashes($network));
        }

        
    }
    
    $gigyaSocialize = new GigyaSocialize();
}
