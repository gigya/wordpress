<?php 
/*
 Plugin Name: Gigya Socialize - Increase Registration and Engagement
 Plugin URI: http://gigya.com
 Description: Integrates a variety of features of the Gigya Socialize service into a WordPress blog.
 Author: Nick Ohrn of Plugin-Developer.com
 Version: 1.2.1
 Author URI: http://plugin-developer.com
 */

if (!class_exists('GigyaSocialize')) 
{
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
        
        function GigyaSocialize() {
            $this->addActions();
            $this->addFilters();
            
            $this->data = new GigyaData();
            $this->info = new GigyaInfo();
            $this->network = new GigyaNetwork();
            $this->user = new GigyaUser();
        }
        
        function addActions() {
            // ADMIN ONLY
            add_action('admin_head', array(&$this, 'header'));
            add_action('admin_init', array(&$this, 'savePluginSettings'));
            add_action('admin_menu', array(&$this, 'addAdministrativePage'));
            add_action('profile_personal_options', array(&$this, 'inviteFriendsUI'));
            add_action('wp_set_comment_status', array(&$this, 'commentPost'), 10, 2);
            
            // OTHER
            add_action('gs_comment_form', array(&$this, 'includeCommentFormExtra'));
            add_action('comment_form', array(&$this, 'includeCommentFormExtra'));
            add_action('comment_post', array(&$this, 'commentPost'));
            add_action('init', array(&$this, 'enqueueResources'));
            add_action('login_head', array(&$this, 'loginPageOutput'));
            add_action('parse_request', array(&$this, 'loginUser'));
            add_action('widgets_init', array(&$this, 'registerWidget'));
            add_action('wp_head', array(&$this, 'header'));
            add_action('wp_logout', array(&$this, 'logoutStorage'));
        }
        
        function addFilters() {
            add_filter('login_redirect', array(&$this, 'changeLoginRedirect'));
            add_filter('get_avatar', array(&$this, 'changeAvatarImage'), 10, 5);
        }
        
        /// CALLBACKS
        
        function addAdministrativePage() {
            add_options_page(__('Gigya Socialize'), __('Gigya Socialize'), 'manage_options', 'gigya-socialize', array($this, 'displaySettingsPage'));
        }
        
        function enqueueResources() {
            wp_enqueue_script('gigya-socialize', "{$this->info->pluginUrl}/resources/gs-for-wordpress.js", array('jquery'), $this->info->version);
        }
        
        function header() {
            include ($this->info->pluginFolder.'/views/general/header.php');
        }
        
        function changeAvatarImage($avatar, $id_or_email, $size, $default, $alt) {
            if (is_object($id_or_email)) {
                // Comment object
                $id_or_email = $id_or_email->user_id;
            }
            if (is_numeric($id_or_email) && $this->user->hasThumbnailUrl($id_or_email)) {
                $thumb = $this->user->getThumbnailUrl($id_or_email);
                $avatar = preg_replace("/src='*?'/", "src='$thumb'", $avatar);
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
            if ($this->user->hasGigyaConnection($comment->user_id) && $approvalStatus == 1 || $approval == 'approve') {
                update_usermeta($comment->user_id, $this->user->_meta_RecentCommentPostedId, $commentId);
            }
        }
        
        function includeCommentFormExtra($force = false) {
            $settings = $this->data->getSettings();
            if ($settings['gs-for-wordpress-comment-extras'] || true === $force) {
                if (is_user_logged_in()) {
                    include ('views/comments/connected.php');
                } else {
                    include ('views/comments/not-connected.php');
                }
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
         * Attempts to log a user in after they have used the Gigya authentication mechanism.
         */
        function loginUser() {
            if ($this->isGigyaAuthenticationAttempt($_POST)) {
                $loginResults = $this->loginViaGigya($_POST);
                $isCommenting = $_POST['commenting'] == 1;
                
                if ($isCommenting) {
                    ob_start();
                    include ('views/comments/connected.php');
                    $loginResults['message'] = ob_get_clean();
                }
                
                header('Content-type: application/json');
                echo json_encode($loginResults);
                exit();
            }
        }
        
        function isGigyaAuthenticationAttempt($data) {
            return (1 == $data['gigya-authenticate']) && isset($data['gigya-timestamp']) && isset($data['gigya-uid']);
        }
        
        function loginViaGigya($data) {
            $message = __('Unknown Error');
            $redirectUrl = '';
            
            if ($this->data->hashIsValid($data['gigya-timestamp'], $data['gigya-uid'], $data['gigya-signature'])) {
                if (is_user_logged_in()) {
                    // Connect current user account
                    $user = wp_get_current_user();
                    $this->user->editData($data, $user->ID);
                    $this->user->registerData($data, $user->ID);
                    $message = __('Your WordPress account has been connected to your Social Network account.');
                    $redirect = $data['redirect-url'];
                } else {
                    $userId = $this->user->hasPreviouslyConnected($data['gigya-uid'], GigyaData::getMetaNameForNetwork($data['gigya-login-provider']));
                    
                    if ($userId < 1) {
                        $allowRegistration = get_option('users_can_register');
                        if ('0' == $allowRegistration) {
                            $message = __('New user access is currently disabled for this site.');
                            $redirect = '';
                        } else {
                            $user = $this->user->registerUser($data);
                            $userId = $user->ID;
                        }
                    }
                    
                    if (is_numeric($userId) && $userId > 0) {
                        $user = get_userdata($userId);
                        $message = sprintf(__('You are being logged in and will be redirected within 10 seconds.  If you are not redirected, please <a href="%1$s">click here</a>.'), $data['redirect-url']);
                        $redirect = $data['redirect-url'];
                        
                        $this->user->setCredentials($user);
                    }
                }
            } else {
                $message = sprintf(__('Experienced an unsuccessful authentication.  Please try again.'));
                $redirect = '';
            }
            
            if ($redirect == admin_url()) {
                $redirect = apply_filters('login_redirect', $redirect);
            }
            return array('message'=>$message, 'redirect'=>$redirect);
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
                $settings['gs-for-wordpress-friend-selector-component-ui'] = $this->data->sanitizeConfigurationForFriendSelector(stripslashes($_POST['gs-for-wordpress-friend-selector-component-ui']));
                $settings['gs-for-wordpress-widget-sign-in-component-ui'] = $this->data->sanitizeConfigurationForConnectWidget(stripslashes($_POST['gs-for-wordpress-widget-sign-in-component-ui']));
				$settings['gs-for-wordpress-comments-sign-in-component-ui'] = str_replace('var conf', 'var comment_conf',$this->data->sanitizeConfigurationForConnectWidget(stripslashes($_POST['gs-for-wordpress-comments-sign-in-component-ui'])));
                $settings['gs-for-wordpress-sign-in-component-ui'] = $this->data->sanitizeConfigurationForConnectWidget(stripslashes($_POST['gs-for-wordpress-sign-in-component-ui']));
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
				width:70,
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
    }
    $gigyaSocialize = new GigyaSocialize();
    include ('library/template-tags.php');
}


