<?php
/**
 * Plugin Name: Gigya - Make Your Site Social
 * Plugin URI: http://gigya.com
 * Description: Allows sites to utilize the Gigya API for authentication and social network updates.
 * Version: 5.1
 * Author: Gigya
 * Author URI: http://gigya.com
 * License: GPL2+
 */
//
// --------------------------------------------------------------------

/**
 * Global constants.
 */
define( 'GIGYA__MINIMUM_WP_VERSION', '3.5' );
define( 'GIGYA__MINIMUM_PHP_VERSION', '5.2' );
define( 'GIGYA__VERSION', '5.1' );
define( 'GIGYA__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIGYA__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GIGYA__CDN_PROTOCOL', ! empty( $_SERVER['HTTPS'] ) ? 'https://cdns' : 'http://cdn' );
define( 'GIGYA__JS_CDN', GIGYA__CDN_PROTOCOL . '.gigya.com/js/socialize.js?apiKey=' );
define( 'GIGYA__LOG_LIMIT', 50 );

/**
 * Gigya constants for admin settings sections.
 */
define( 'GIGYA__SETTINGS_GLOBAL', 'gigya_global_settings' );
define( 'GIGYA__SETTINGS_LOGIN', 'gigya_login_settings' );
define( 'GIGYA__SETTINGS_SHARE', 'gigya_share_settings' );
define( 'GIGYA__SETTINGS_FOLLOW', 'gigya_follow_settings' );
define( 'GIGYA__SETTINGS_COMMENTS', 'gigya_comments_settings' );
define( 'GIGYA__SETTINGS_REACTIONS', 'gigya_reactions_settings' );
define( 'GIGYA__SETTINGS_GM', 'gigya_gm_settings' );
define( 'GIGYA__SETTINGS_FEED', 'gigya_feed_settings' );

/**
 * Register activation hook
 */
register_activation_hook( __FILE__, 'registerActivationHook' );
function registerActivationHook() {
	require_once GIGYA__PLUGIN_DIR . 'install.php';
	$install = new GigyaInstall();
	$install->init();
}

/**
 * Lets start.
 */
new GigyaAction;

/**
 * The main plugin class.
 */
class GigyaAction {

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Gigya configuration values.
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );

		// Gigya CMS
		define( 'GIGYA__API_KEY', $this->global_options['api_key'] );
		define( 'GIGYA__API_SECRET', $this->global_options['api_secret'] );
		define( 'GIGYA__API_DOMAIN', $this->global_options['data_center'] );
		define( 'GIGYA__API_DEBUG', $this->global_options['debug'] );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_action_update', array( $this, 'adminActionUpdate' ) );
		add_action( 'wp_ajax_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_gigya_raas', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_raas', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_ajax_custom_login', array( $this, 'ajaxCustomLogin' ) );
		add_action( 'wp_ajax_nopriv_custom_login', array( $this, 'ajaxCustomLogin' ) );
		add_action( 'wp_ajax_debug_log', array( $this, 'ajaxDebugLog' ) );
		add_action( 'wp_ajax_clean_db', array( $this, 'ajaxCleanDB' ) );
		add_action( 'wp_ajax_gigya_logout', array( $this, 'ajaxLogout' ) );
		add_action( 'wp_ajax_raas_update_profile', array( $this, 'ajaxUpdateProfile' ) );
		add_action( 'wp_login', array( $this, 'wpLogin' ), 10, 2 );
		add_action( 'user_register', array( $this, 'userRegister' ), 10, 1 );
		add_action( 'wp_logout', array( $this, 'wpLogout' ) );
		add_action( 'delete_user', array( $this, 'deleteUser' ) );
		add_action( 'widgets_init', array( $this, 'widgetsInit' ) );
		add_shortcode( 'gigya_user_info', array( $this, 'shortcodeUserInfo' ) );
		add_filter( 'the_content', array( $this, 'theContent' ) );
		add_filter( 'comments_template', array( $this, 'commentsTemplate' ) );
		add_filter( 'get_avatar', array( $this, 'getGigyaAvatar'), 10, 5);
		add_filter( 'login_message', array( $this, 'rass_wp_login_custom_message') );

		// Plugins shortcode activation switches
		require_once GIGYA__PLUGIN_DIR . 'features/gigyaPluginsShortcodes.php';
		$shortcodes_class = new gigyaPluginsShortcodes();

		add_shortcode( 'gigya-raas-login',  array( $shortcodes_class, 'gigyaRaas'));
		add_shortcode( 'gigya-raas-profile',  array( $shortcodes_class, 'gigyaRaas'));
		add_shortcode( 'gigya-social-login',  array( $shortcodes_class, 'gigyaSocialLoginScode'));

		$comments_switch = get_option(GIGYA__SETTINGS_COMMENTS);
		if ( $comments_switch['on'] == true || $comments_switch['on'] == '1') {
			add_shortcode( 'gigya-comments', array( $shortcodes_class, 'gigyaCommentsScode' ) );
		}
		$feed_switch = get_option(GIGYA__SETTINGS_FEED);
		if ( $feed_switch['on'] == true || $feed_switch['on'] == '1' ) {
			add_shortcode( 'gigya-activity-feed', array( $shortcodes_class, 'gigyaFeedScode' ) );
		}
		$follow_bar_switch = get_option(GIGYA__SETTINGS_FOLLOW);
		if ( $follow_bar_switch['on'] == true  || $follow_bar_switch['on'] == '1' ) {
			add_shortcode( 'gigya-follow-bar',  array( $shortcodes_class, 'gigyaFollowBarScode'));
		}
		$reaction_switch = get_option(GIGYA__SETTINGS_REACTIONS);
		if ( $reaction_switch['on'] == true || $reaction_switch['on'] == '1' ) {
			add_shortcode( 'gigya-reactions',  array( $shortcodes_class, 'gigyaReactionsScode'));
		}
		$share_switch = get_option(GIGYA__SETTINGS_SHARE);
		if ( $share_switch['on'] == true || $share_switch['on'] == '1' ) {
			add_shortcode( 'gigya-share-bar',  array( $shortcodes_class, 'gigyaShareBarScode'));
		}
		$gm_switch = get_option(GIGYA__SETTINGS_GM);
		if ( $gm_switch['on'] == true || $gm_switch['on'] == '1' ) {
			add_shortcode( 'gigya-gm-achievements',  array( $shortcodes_class, 'gigyaGmScode'));
			add_shortcode( 'gigya-gm-challenge-status',  array( $shortcodes_class, 'gigyaGmScode'));
			add_shortcode( 'gigya-gm-leaderboard',  array( $shortcodes_class, 'gigyaGmScode'));
			add_shortcode( 'gigya-gm-user-status',  array( $shortcodes_class, 'gigyaGmScode'));
		}
		// End plugins shortcodes activation switches
	}

	/**
	 * Initialize hook.
	 */
	public function init() {
		require_once GIGYA__PLUGIN_DIR . 'sdk/GSSDK.php';
		require_once GIGYA__PLUGIN_DIR . 'sdk/gigyaCMS.php';

		// Load jQuery and jQueryUI from WP.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'gigya_js', GIGYA__PLUGIN_URL . 'gigya.js' );
		wp_enqueue_style( 'gigya_css', GIGYA__PLUGIN_URL . 'gigya.css' );

		// Parameters to be sent to the DOM.
		$params = array(
				'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
				'logoutUrl'                   => wp_logout_url(),
				'connectWithoutLoginBehavior' => _gigParam( $this->login_options, 'connectWithoutLoginBehavior', 'loginExistingUser' ),
				'jsonExampleURL'              => GIGYA__PLUGIN_URL . 'admin/forms/json/advance_example.json',
				'enabledProviders'            => _gigParam( $this->global_options, 'enabledProviders', '*' ),
				'lang'                        => _gigParam( $this->global_options, 'lang', 'en' ),
				'sessionExpiration'           => gigyaSyncLoginSession()
		);

		// Add advanced parameters if exist.
		if ( ! empty( $this->global_options['advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( _gigParam( $this->global_options, 'advanced', '' ) );
			$params   = array_merge( $params, $advanced );
		}

		// Let others plugins to modify the global parameters.
		$params = apply_filters( 'gigya_global_params', $params );

		// Load params to be available to client-side script.
		wp_localize_script( 'gigya_js', 'gigyaParams', $params );

		// Checking that we have an API key and Gigya's plugin is turn on.
		$api_key = GIGYA__API_KEY;
		if ( ! empty( $api_key ) ) {
			// Loads requirements for any Gigya's login.
			if ( $this->login_options['mode'] != 'wp_only' ) {
				// Load Gigya's socialize.js from CDN.
				wp_enqueue_script( 'gigya_cdn', GIGYA__JS_CDN . GIGYA__API_KEY . '&lang=' . $params['lang'] );
			}

			// Loads requirements for any Gigya's social login.
			if ( $this->login_options['mode'] == 'wp_sl' ) {
				require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginSet.php';
				$gigyaLoginSet = new GigyaLoginSet;
				$gigyaLoginSet->init();
			}

			// Loads requirements for any Gigya's RaaS login.
			if ( $this->login_options['mode'] == 'raas' ) {
				// Loads RaaS links class.
				require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasSet.php';
				$gigyaRaasSet = new GigyaRaasSet;
				$gigyaRaasSet->init();
			}

			// Loads requirements for any Gigya's Google-Analytics integration.
			if ( ! empty( $this->global_options['google_analytics'] ) ) {
				wp_enqueue_script( 'gigya_ga', GIGYA__CDN_PROTOCOL . '.gigya.com/js/gigyaGAIntegration.js' );
			}
		}

		if ( is_admin() ) {
			// Loads requirements for the admin settings section.
			require_once GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php';
			new GigyaSettings;
		}
	}

	/**
	 * admin_action_ hook.
	 * Fires when an 'action' REQUEST variable is sent.
	 */
	public function adminActionUpdate() {
		require_once GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php';
		GigyaSettings::onSave();
	}

	/**
	 * Hook AJAX login.
	 */
	public function ajaxLogin() {

		// Loads Gigya's social login class.
		require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginAjax.php';
		$gigyaLoginAjax = new GigyaLoginAjax;
		$gigyaLoginAjax->init();
	}

	/**
	 * Hook AJAX RAAS login.
	 */
	public function ajaxRaasLogin() {

		// Loads Gigya's RaaS class.
		require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasAjax.php';
		$gigyaLoginAjax = new GigyaRaasAjax;
		$gigyaLoginAjax->init();
	}

	public  function ajaxUpdateProfile() {

		// Loads Gigya's RaaS class.
		require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasAjax.php';
		$gigyaAjax = new GigyaRaasAjax;
		$data = $_POST['data'];
		$gigyaAjax->updateProfile($data);
	}

	/**
	 * Hook AJAX Custom forms login.
	 */
	public function ajaxCustomLogin() {

		// Loads Gigya's social login class.
		require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginAjax.php';
		GigyaLoginAjax::customLogin();
	}

	/**
	 * Hook AJAX Debug Log.
	 */
	public function ajaxDebugLog() {
		if ( current_user_can( 'manage_options' ) ) {
			wp_send_json_success( array( 'data' => get_option( 'gigya_log' ) ) );
		}

		wp_send_json_error();
	}

	public function ajaxLogout() {
		wp_logout();
		wp_send_json_success();
	}

	/**
	 * Hook to wp user login.
	 * If user logs in with wp form, check if raas is enabled,
	 * if so check if user has allowed capabilities
	 * if not log user out, if yes notify gigya.
	 *
	 * @param $user_login
	 * @param $account
	 */
	public function wpLogin( $user_login, $account ) {

		// Login through WP form.
		if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {

			// Trap for non-admin user who tries to login through WP form on RaaS mode.

			$_is_allowed_user = $this->check_raas_allowed_user_role($account->roles);
			if ( $this->login_options['mode'] == 'raas' && (!$_is_allowed_user) ) {
				wp_logout();
				wp_safe_redirect( $_SERVER['REQUEST_URI'].'?rperm=1' ); // rperm used to create custom error message in wp login screen
				exit;
			}

			// Notify Gigya socialize.notifyLogin
			// for a return user logged in from WP login form.
			$gigyaCMS = new GigyaCMS();
			$gigyaCMS->notifyLogin( $account->ID );

		}

		// This post vars available when there is the same email on the site,
		// with the one who try to register and we want to link-accounts
		// after the user is logged in with password. Or login after email verify.
		if ( ( $_POST['action'] == 'link_accounts' || $_POST['action'] == 'custom_login' ) && ! empty ( $_POST['data'] ) ) {
			parse_str( $_POST['data'], $data );
			if ( ! empty( $data['gigyaUID'] ) ) {
				$gigyaCMS = new GigyaCMS();
				$gigyaCMS->notifyRegistration( $data['gigyaUID'], $account->ID );
				delete_user_meta( $account->ID, 'gigya_uid' );
			}
		}
	}

	/*  Raas admin login
	 *  Check if user role is marked by admin as allowed role for wp login access
	 *  For unified comparison transform values to _lowercase_
	 *  admin roles are auto allowed, subscriber role is auto denied.
	 *
	 * @param string $user_role
	 * @return bool $allowed
	 */
	public function check_raas_allowed_user_role($user_roles) {

		$allowed = false;
		$login_options = array_change_key_case( get_option( GIGYA__SETTINGS_LOGIN ) );

		foreach ( $user_roles as $role ) {

			$role = strtolower($role);
			$role = str_replace(' ', '_', $role);
			// first auto allow Administrator or Super Admin roles
			if ( $role == "administrator" || $role == "super_admin" ) {
				$allowed = true;
				continue;
			} elseif ( $role == "subscriber" ) {
				$allowed = false;
				continue;
			} else {
				// if this is not an Admin or super admin
				$user_role = "raas_allowed_admin_{$role}";

				// find if user role key exists and positive in options array
                foreach ( $login_options as $key => $value ) {
					$key = str_replace(' ', '_', $key);
                    if ( $user_role ==  $key ) {
                        if ( $value == "1" || $value == true ) {
							$allowed = true;
                            continue;
                        }
                    }
                }
			}
		}
		// if no role match then the user is not allowed login
		return $allowed;
	}

	/*
	 * Custom error message in case raas user tries to log in via wordpress wp-login screen.
	 * Used by hook wp_login
	 *
	 * @return string $message
	 */
	public function rass_wp_login_custom_message() {
		if (isset($_GET['rperm']) ) {
			$message = "<div id='login_error'><strong>Access denied: </strong>
			this login requires administrator permission. <br/>Click <a href='/wp-login.php'>here</a> to login to the site.</div>";
			return $message;
		}
	}

	/**
	 * Hook user register.
	 *
	 * @param $uid
	 */
	public function userRegister( $uid ) {

		// New user was registered through our custom extra-details form.
		if ( $_POST['form_name'] == 'registerform-gigya-extra' && ! empty( $_POST['gigyaUID'] ) ) {
			add_user_meta( $uid, 'gigya_uid', $_POST['gigyaUID'] );
		}

		// New user was registered through Gigya social login.
		// $_POST['action'] == 'gigya_login';
		if ( $this->login_options['mode'] == 'wp_sl' ) {
			if ( ! empty( $_POST['data'] ) && ! empty( $_POST['data']['UID'] ) ) {

				// We check if we can count on the email.
				if ( $_POST['data']['user']['email_not_verified'] == true ) {
					// The mail is NOT verified, so we save Gigya's UID to DB
					// and do nothing.
					add_user_meta( $uid, 'gigya_uid', $_POST['data']['UID'] );
				} else {
					// The mail is verified, so we can merge IDs.
					$gigyaCMS = new GigyaCMS();
					$gigyaCMS->notifyRegistration( $_POST['data']['UID'], $uid );
				}
			}
		}

		// New user was registered through WP form.
		if ( isset( $_POST['user_login'] ) && isset( $_POST['user_email'] ) ) {
			// We notify to Gigya's 'socialize.notifyLogin'
			// with a 'is_new_user' flag.
			$gigyaCMS = new GigyaCMS();
			$result = $gigyaCMS->notifyLogin( $uid, TRUE );
		}
	}

	/**
	 * Hook user logout
	 */
	public function wpLogout() {

		// Get the current user.
		$account = wp_get_current_user();
		if ( ! empty ( $account ) ) {

			// Social logout
			if ( $this->login_options['mode'] == 'wp_sl' ) {
				$gigyaCMS = new GigyaCMS();
				$gigyaCMS->userLogout( $account->ID );
			} elseif ( $this->login_options['mode'] == 'raas' ) {
				$gigyaCMS = new GigyaCMS();
				$gigyaCMS->accountLogout( $account );
			}
		}
	}

	/**
	 * Hook delete user.
	 *
	 * @param $user_id
	 */
	public function deleteUser( $user_id ) {

		$gigyaCMS = new GigyaCMS();

		if ( $this->login_options['mode'] == 'wp_sl' ) {
			$gigyaCMS->deleteUser( $user_id );
		} elseif ( $this->login_options['mode'] == 'raas' ) {
			$account = get_userdata( $user_id );
			$gigyaCMS->deleteAccount( $account );
		}
	}

	/**
	 * shortcode for UserInfo.
	 */
	private function shortcodeUserInfo( $atts, $info = NULL ) {

		$wp_user = wp_get_current_user();

		if ( $info == NULL ) {
			$gigyaCMS  = new GigyaCMS();
			$user_info = $gigyaCMS->getUserInfo( $wp_user->UID );
		}

		return $user_info->getString( key( $atts ), current( $atts ) );
	}

	/**
	 * Register widgets.
	 */
	public function widgetsInit() {

		// RasS Widget.
		$raas_on = $this->login_options['mode'] == 'raas';
		if ( ! empty( $raas_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasWidget.php';
			register_widget( 'GigyaRaas_Widget' );
		}

		// Login Widget.
		$login_on = $this->login_options['mode'] == 'wp_sl';
		if ( ! empty( $login_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginWidget.php';
			register_widget( 'GigyaLogin_Widget' );
		}

		// Share Widget.
		$share_options = get_option( GIGYA__SETTINGS_SHARE );
		$share_on      = _gigParamDefaultOn( $share_options, 'on' );
		if ( ! empty( $share_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/share/GigyaShareWidget.php';
			register_widget( 'GigyaShare_Widget' );
		}

		// Comment Widget.
		$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
		$comments_on      = _gigParamDefaultOn( $comments_options, 'on' );
		if ( ! empty( $comments_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/comments/GigyaCommentsWidget.php';
			register_widget( 'GigyaComments_Widget' );
		}

		// Reactions Widget.
		$reactions_options = get_option( GIGYA__SETTINGS_REACTIONS );
		$reactions_on      = _gigParamDefaultOn( $reactions_options, 'on' );
		if ( ! empty( $reactions_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/reactions/GigyaReactionsWidget.php';
			register_widget( 'GigyaReactions_Widget' );
		}

		// Gamification Widget.
		$gm_options = get_option( GIGYA__SETTINGS_GM );
		$gm_on      = _gigParamDefaultOn( $gm_options, 'on' );
		if ( ! empty( $gm_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/gamification/GigyaGamificationWidget.php';
			register_widget( 'GigyaGamification_Widget' );
		}

		// Activity Feed Widget.
		$feed_options = get_option( GIGYA__SETTINGS_FEED );
		$feed_on      = _gigParamDefaultOn( $feed_options, 'on' );
		if ( ! empty( $feed_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/feed/GigyaFeedWidget.php';
			register_widget( 'GigyaFeed_Widget' );
		}

		// Follow Bar Widget.
		require_once GIGYA__PLUGIN_DIR . 'features/follow/GigyaFollowWidget.php';
		register_widget( 'GigyaFollow_Widget' );
	}

	/**
	 * Hook content alter.
	 */
	public function theContent( $content ) {
		// Share plugin.
		$share_options = get_option( GIGYA__SETTINGS_SHARE );
		$share_on      = _gigParamDefaultOn( $share_options, 'on' );
		if ( ! empty( $share_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/share/GigyaShareSet.php';
			$share   = new GigyaShareSet();
			$content = $share->setDefaultPosition( $content );
		}

		// Reactions plugin.
		$reactions_options = get_option( GIGYA__SETTINGS_REACTIONS );
		$reactions_on      = _gigParamDefaultOn( $reactions_options, 'on' );
		if ( ! empty( $reactions_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/reactions/GigyaReactionsSet.php';
			$reactions = new GigyaReactionsSet();
			$content   = $reactions->setDefaultPosition( $content );
		}

		return $content;
	}

	/**
	 * Hook comments_template.
	 *
	 * @param $comment_template
	 *
	 * @return string
	 */
	public function commentsTemplate( $comment_template ) {

		// Comments plugin.
		$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
		$comments_on      = _gigParamDefaultOn( $comments_options, 'on' );
		if ( ! empty( $comments_on ) ) {

			// Spider trap.
			// When a spider detect we render the comment in the HTML for SEO
			$is_spider = gigyaCMS::isSpider();
			if ( ! empty( $is_spider ) ) {
				// Override default WP comments template with comment spider.
				return GIGYA__PLUGIN_DIR . 'admin/tpl/comments-spider.tpl.php';
			}

			// Override default WP comments template.
			return GIGYA__PLUGIN_DIR . 'admin/tpl/comments.tpl.php';
		}
	}

	/**
	 * Hook AJAX Clean DB.
	 */
	public function ajaxCleanDB() {
		if ( current_user_can( 'manage_options' ) ) {
			require_once GIGYA__PLUGIN_DIR . 'install.php';
			GigyaInstall::cleanDB();
		}
	}

	public  function getGigyaAvatar($avatar, $id_or_email, $size, $default, $alt) {
		if ( empty($id_or_email) ) {
			$id = get_current_user_id();
		} else {
			if ( is_numeric( $id_or_email ) ) {
				$id = $id_or_email;
			} elseif(is_string( $id_or_email)) {
				$user = get_user_by( 'email', $id_or_email );
				$id   = $user->ID;
			} else {
				return $avatar;
			}
		}
		$url = get_user_meta($id, "profile_image", true);
		if ( empty($url) ) {
			return $avatar;
		}
		$alt = empty( $alt ) ? get_user_meta($id, "first_name", true) : $alt;
		return "<img src='{$url}' alt='{$alt}' width='{$size}' height='{$size}'>";
	}

}

 if ( ! function_exists( 'wp_new_user_notification' ) ) {
	$login_opts = get_option( GIGYA__SETTINGS_LOGIN );
	if ( $login_opts['mode'] == 'raas' ) {
		/**
		 * If we're on raas mode we disabled new user notifications from WP.
		 *
		 * @param        $user_id
		 * @param string $plaintext_pass
		 */
		function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
			// Set default_password_nag to false, for prevent a user asked to change his password.
			update_user_option( $user_id, 'default_password_nag', false, true );
			return;
		}
	}
}

/**
 * Renders a default template.
 *
 * @param $template_file
 *   The filename of the template to render.
 * @param $variables
 *   A keyed array of variables that will appear in the output.
 *
 * @return void The output generated by the template.
 */
function _gigya_render_tpl( $template_file, $variables = array() ) {

	// Extract the variables to a local namespace
	extract( $variables, EXTR_SKIP );

	// Start output buffering
	ob_start();

	// Include the template file
	include GIGYA__PLUGIN_DIR . '/' . $template_file;

	// End buffering and return its contents
	return ob_get_clean();

}

// --------------------------------------------------------------------

/**
 * Render a form.
 *
 * @param        $form
 *
 * @param string $name_prefix
 *
 * @return string
 */
function _gigya_form_render( $form, $name_prefix = '' ) {

	$render = '';

	foreach ( $form as $id => $el ) {

		if ( empty( $el['type'] ) || $el['type'] == 'markup' ) {

			$render .= $el['markup'];

		} else {

			if ( empty( $el['name'] ) ) {
				if ( ! empty( $name_prefix ) ) {

					// In cases like on admin multipage the element
					// name is build from the section and the ID.
					// This tells WP under which option to save this field value.
					$el['name'] = $name_prefix . '[' . $id . ']';

				} else {

					// Usually the element name is just the ID.
					$el['name'] = $id;

				}
			}

			// Add the ID value to the array.
			$el['id'] = $id;

			// Render each element.
			$render .= _gigya_render_tpl( 'admin/tpl/formEl-' . $el['type'] . '.tpl.php', $el );

		}
	}

	return $render;

}

// --------------------------------------------------------------------

/**
 * Loads JSON string from file.
 *
 * @param $file | relative path from Gigya plugin root.
 *              DO NOT include filename extension.
 *
 * @return bool|string
 */
function _gigya_get_json( $file ) {
	$path = GIGYA__PLUGIN_DIR . $file . '.json';
	$json = file_get_contents( $path );
	return ! empty( $json ) ? $json : FALSE;
}

// --------------------------------------------------------------------

/**
 * Helper
 * return value for given key in input array or object
 *
 * @param array, object $array
 * @param string $key
 * @param string, int $default
 *
 * @return $default - $array value (if $array is not empty)
 */
function _gigParam( $array, $key, $default = null ) {
	if ( is_array( $array ) ) {
		return ! empty( $array[$key] ) || $array[$key] === "0" ? $array[$key] : $default;
	} elseif ( is_object( $array ) ) {
		return ! empty( $array->$key ) || $array->key === "0" ? $array->$key : $default;
	}
	return $default;
}

// --------------------------------------------------------------------

/**
 * Helper
 */
function _gigParamDefaultOn( $array, $key ) {
	return ( isset( $array[$key] ) && $array[$key] === '0' ) ? '0' : '1';
}

// --------------------------------------------------------------------

/*
 * Helper for form formatting, check for default values and set selected values
 *     check if role belongs to default. if so set default value to checked, for all other roles set default to not-checked.
 *	   set selected value (using _gigparam )
 *
 * @param array $values - gigya login settings
 * @param string $role
 * @param string $setting_role_name
 *
 * @return bool $value
 */
function _DefaultAdminValue( $values, $role, $settings_role_name ) {
	if ( $role == 'Editor' || $role == 'Author' || $role == 'Contributor' ) {
		$value = _gigParam( $values, $settings_role_name, 1 );
	} else {
		$value = _gigParam( $values, $settings_role_name, 0 );
	}
	return $value;
}

// --------------------------------------------------------------------

/**
 * Implements _gigya_error_log from gigyaCMS().
 *
 * @param $new_log
 *
 * @internal param $log
 */
function _gigya_error_log( $new_log ) {
	// Get global debug.
	$gigya_debug = GIGYA__API_DEBUG;
	if ( ! empty( $gigya_debug ) && is_array( $new_log ) ) {

		// Get to existing log from DB.
		$exist_log = get_option( 'gigya_log' );

		// Initialize if there is no existing one.
		if ( ! is_array( $exist_log ) ) {
			$exist_log = array();
		}

		// Push new entries to the beginning of the array.
		foreach ( $new_log as $entry ) {
			array_unshift( $exist_log, $entry );
		}

		// Cut the array to max limit log entries.
		$log = array_slice( $exist_log, 0, GIGYA__LOG_LIMIT );

		// Update the DB with new entries.
		update_option( 'gigya_log', $log );
	}
}

// --------------------------------------------------------------------

/**
 * Get Login sesssion time from wordpress to set in gigya
 */

function gigyaSyncLoginSession() {
    return (int) apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, 777, false );
}

// --------------------------------------------------------------------

/**
 * Map social user fields to worpdress user fields
 * @param $gigya_object
 * @param $user_id
 */
function _gigya_add_to_wp_user_meta($gigya_object, $user_id) {
	$login_opts = get_option( GIGYA__SETTINGS_LOGIN );
	if ($login_opts['mode'] == "wp_sl") {
		$prefix = "map_social_";
	} elseif ($login_opts['mode'] == "raas") {
		$prefix = "map_raas_";
	} else {
		return;
	}
	// Get all mapping options
	foreach ( $login_opts as $key => $opt ) {
		if (strpos($key, $prefix) === 0 && $opt == 1) {
			$k = str_replace($prefix, "",$key);
			$gigya_key = _wp_key_to_gigya_key($k);
			update_user_meta($user_id, $k, sanitize_text_field($gigya_object[$gigya_key]));
		}
	}

}

function _wp_key_to_gigya_key( $wp_key ) {
	$convert = array(
		'first_name'   => 'firstName',
		'last_name'    => 'lastName',
		'display_name' => 'nickname',
		'description'  => 'bio',
	    'profile_image' => 'photoURL'
	);
	return empty($convert[$wp_key]) ? $wp_key : $convert[$wp_key];
}

function _underscore_to_camelcase( $str ) {
	$parts = explode('_', $str);
	$string = $parts[0];
	for ( $i = 1; $i <= count($parts); $i ++ ) {
		if ($parts[$i] == 'id') {
			$string .= strtoupper($parts[$i]);
		} else {
			$string .= ucfirst( $parts[ $i ] );
		}
	}
	return $string;
}