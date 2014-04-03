<?php
/**
 * Plugin Name: Gigya - Make Your Site Social
 * Plugin URI: http://gigya.com
 * Description: Allows sites to utilize the Gigya API for authentication and social network updates.
 * Version: 5.0
 * Author: Gigya
 * Author URI: http://gigya.com
 * License: GPL2+
 */

// --------------------------------------------------------------------

/**
 * Global constants.
 */
define( 'GIGYA__MINIMUM_WP_VERSION', '3.5' );
define( 'GIGYA__MINIMUM_PHP_VERSION', '5.2' );
define( 'GIGYA__VERSION', '5.0' );
define( 'GIGYA__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIGYA__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// TODO: Make sure we can load the script via https
define( 'GIGYA__JS_CDN', 'http://cdn.gigya.com/JS/socialize.js?apiKey=' );


/**
 * Gigya constants for admin settings sections.
 */
define( 'GIGYA__SETTINGS_GLOBAL', 'gigya_global_settings' );
define( 'GIGYA__SETTINGS_LOGIN', 'gigya_login_settings' );
define( 'GIGYA__SETTINGS_SHARE', 'gigya_share_settings' );
define( 'GIGYA__SETTINGS_COMMENTS', 'gigya_comments_settings' );
define( 'GIGYA__SETTINGS_REACTIONS', 'gigya_reactions_settings' );
define( 'GIGYA__SETTINGS_GM', 'gigya_gm_settings' );

new GigyaAction;

/**
 * class.GigyaAction.php
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
		define( 'GIGYA__API_KEY', $this->global_options['global_api_key'] );
		define( 'GIGYA__API_SECRET', $this->global_options['global_api_secret'] );
		define( 'GIGYA__API_DOMAIN', $this->global_options['global_data_center'] );
		define( 'GIGYA__API_DEBUG', $this->global_options['login_gigya_debug'] );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_action_update', array( $this, 'adminActionUpdate' ) );
		add_action( 'wp_ajax_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_gigya_raas', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_raas', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_login', array( $this, 'wpLogin' ), 10, 2 );
		add_action( 'user_register', array( $this, 'userRegister' ), 10, 1 );
		add_action( 'wp_logout', array( $this, 'wpLogout' ) );
		add_action( 'deleted_user', array( $this, 'deletedUser' ) );
		add_action( 'widgets_init', array( $this, 'widgetsInit' ) );
		add_shortcode( 'gigya_user_info', array( $this, 'shortcodeUserInfo' ) );
		add_filter( 'the_content', array( $this, 'theContent' ) );

	}

	/**
	 * Initialize hook.
	 */
	public function init() {
		// TODO: Load only if in front end.

		require_once( GIGYA__PLUGIN_DIR . 'sdk/GSSDK.php' );
		require_once( GIGYA__PLUGIN_DIR . 'sdk/gigyaCMS.php' );

		// Load jQuery and jQueryUI from WP..
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'gigya_js', GIGYA__PLUGIN_URL . 'gigya.js' );

		// Share widget.
		// TODO: load files only if enabled
		require_once( GIGYA__PLUGIN_DIR . 'features/share/GigyaShareSet.php' );
		$share = new GigyaShareSet();
		$share->init();

		// Parameters to be sent to the DOM.
		$params = array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'logoutUrl' => wp_logout_url(),
		);

		// Load params to be available to client-side script.
		wp_localize_script( 'gigya_js', 'gigyaParams', $params );

		// Checking that we have an API key and Gigya's plugin is turn on.
		$api_key = GIGYA__API_KEY;
		if ( ! empty( $api_key ) ) {

			// Loads requirements for any Gigya's login.
			if ( $this->login_options['login_mode'] != 'wp_only' ) {
				// Load Gigya's socialize.js from CDN.
				$lang = getParam( $this->global_options['global_lang'], 'en' );
				wp_enqueue_script( 'gigya', GIGYA__JS_CDN . GIGYA__API_KEY . '&lang=' . $lang );
			}

			// Loads requirements for any Gigya's social login.
			if ( $this->login_options['login_mode'] == 'wp_sl' ) {
				require_once( GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginSet.php' );
				$gigyaLoginSet = new GigyaLoginSet;
				$gigyaLoginSet->init();
			}

			// Loads requirements for any Gigya's RaaS login.
			if ( $this->login_options['login_mode'] == 'raas' ) {
				// Loads RaaS links class.
				require_once( GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasSet.php' );
				$gigyaRaasSet = new GigyaRaasSet;
				$gigyaRaasSet->init();
			}

			// Loads requirements for any Gigya's Google-Analytics integration.
			if ( ! empty( $this->global_options['global_google_analytics'] ) ) {
				$uri_prefix = ! empty( $_SERVER['HTTPS'] ) ? 'https://cdns' : 'http://cdn';
				wp_enqueue_script( 'gigya', $uri_prefix . '.gigya.com/js/gigyaGAIntegration.js' );
			}
		}

		if ( is_admin() ) {
			// Loads requirements for the admin settings section.
			require_once( GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php' );
			new GigyaSettings;
		}
	}

	/**
	 * admin_action_ hook.
	 * Fires when an 'action' REQUEST variable is sent.
	 */
	public function adminActionUpdate() {
		require_once( GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php' );
		GigyaSettings::onSave($_POST);
	}

	/**
	 * Hook AJAX login.
	 */
	public function ajaxLogin() {

		// Loads Gigya's social login class.
		require_once( GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginAjax.php' );
		$gigyaLoginAjax = new GigyaLoginAjax;
		$gigyaLoginAjax->init();

	}

	/**
	 * Hook AJAX RAAS login.
	 */
	public function ajaxRaasLogin() {

		// Loads Gigya's RaaS class.
		require_once( GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasAjax.php' );
		$gigyaLoginAjax = new GigyaRaasAjax;
		$gigyaLoginAjax->init();

	}

	/**
	 * Hook user login.
	 *
	 * @param $user_login
	 * @param $account
	 */
	public function wpLogin( $user_login, $account ) {

		// Login through WP form.
		if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {

			// Trap for non-admin user how try to
			// login through WP form on RaaS mode.
			if ( $this->login_options['login_mode'] == 'raas' && ! in_array( 'administrator', $account->roles ) ) {
				wp_logout();
				wp_safe_redirect( $_SERVER['REQUEST_URI'] );
				exit;
			}

			// Notify Gigya socialize.notifyLogin
			// for a return user logged in from WP login form.
			$gigyaCMS = new GigyaCMS();
			$gigyaCMS->notifyLogin( $account->ID );
		}

		// This post is when there is the same email on the site,
		// with the one who try to register and we want to link-accounts
		// after the user is logged in with password.
		if ( $_POST['form_name'] == 'loginform-gigya-link-account' && ! empty ( $_POST['gigyaUID'] ) ) {
			$gigyaCMS = new GigyaCMS();
			$gigyaCMS->notifyRegistration( $_POST['gigyaUID'], $account->ID );
		}
	}

	/**
	 * Hook user register.
	 *
	 * @param $uid
	 */
	public function userRegister( $uid ) {

		// New user was register through our custom extra-details form.
		if ( $_POST['form_name'] == 'registerform-gigya-extra' && ! empty( $_POST['gigyaUID'] ) ) {
			// We make a login.
//			$wp_user = get_userdata( $uid );
//			require_once( GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginAjax.php' );
//			GigyaLoginAjax::login( $wp_user );

			// Connect IDs.
			$gigyaCMS = new GigyaCMS();
			$gigyaCMS->notifyRegistration( $_POST['gigyaUID'], $uid );
		}

		// New user was register through Gigya social login.
		// $_POST['action'] == 'gigya_login';
		if ( ! empty( $_POST['data'] ) && !empty($_POST['data']['UID']) ) {
			if ( $this->login_options['login_mode'] == 'wp_sl' ) {
				// Connect IDs.
				$gigyaCMS = new GigyaCMS();
				$gigyaCMS->notifyRegistration( $_POST['data']['UID'], $uid );
			}
		}

		// New user was register through WP form.
		if ( isset( $_POST['user_login'] ) && isset( $_POST['user_email'] ) ) {
			// We notify to Gigya's 'socialize.notifyLogin'
			// with a 'is_new_user' flag.
			$gigyaCMS = new GigyaCMS();
			$gigyaCMS->notifyLogin( $uid, TRUE );
		}
	}

	/**
	 * Hook user logout
	 */
	public function wpLogout() {

		if ( $this->login_options['login_mode'] == 'wp_sl' ) {
			// Get the current user.
			$account = wp_get_current_user();

			if ( ! empty ( $account->ID ) ) {
				// We using Gigya's account-linking (best practice).
				// So the siteUID is the same as Gigya's UID.
				$gigyaCMS = new GigyaCMS();
				$gigyaCMS->logout( $account->ID );
			}
		}
	}

	/**
	 * Hook delete user.
	 *
	 * @param $user_id
	 */
	public function deletedUser( $user_id ) {

		$gigyaCMS = new GigyaCMS();

		if ( $this->login_options['login_mode'] == 'wp_sl' ) {
			$gigyaCMS->deleteUser( $user_id );
		} elseif ( $this->login_options['login_mode'] == 'raas' ) {
			$gigyaCMS->deleteAccount( $user_id );
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

		require_once( GIGYA__PLUGIN_DIR . 'features/share/GigyaShareWidget.php' );
		register_widget( 'GigyaShare_Widget' );
	}

	/**
	 * Hook content alter.
	 */
	public function theContent( $content ) {
		$share_options = get_option( GIGYA__SETTINGS_SHARE );
		$position      = $share_options['share_position'];
		if ( ! empty( $position ) && $position != 'none' ) {

			// Get the share widget content.
//			$widget = the_widget( "GigyaShare_Widget" );
			$args   = array( 'before_widget' => '<div class="widget">', 'after_widget' => "</div>", 'before_title' => '<h2 class="widgettitle">', 'after_title' => '</h2>' );
			$widget = GigyaShare_Widget::getContent( $args, array() );

			// Set share widget position on post page.
			switch ( $share_options['share_position'] ) {
				case 'top':
					$content = $widget . $content;
					break;
				case 'bottom':
					$content = $content . $widget;
					break;
//				case 'both':
//					$content    = $widget . $content . $widget;
//					break;
			}
		}

		return $content;
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
function _gigya_render_tpl( $template_file, $variables ) {

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

			if ( ! empty( $name_prefix ) ) {

				// In cases like on admin multipage the element
				// name is build from the section and the ID.
				// This tells WP under which option to save this field value.
				$el['name'] = $name_prefix . '[' . $id . ']';

			} else {

				// Usually the element name is just the ID.
				$el['name'] = $id;

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

/**
 * Helper
 */
function getParam( $param, $default = null ) {
	return ! empty( $param ) ? $param : $default;
}
// --------------------------------------------------------------------