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

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_action_update', array( $this, 'adminActionUpdate' ) );
		add_action( 'login_form', array( $this, 'loginForm' ) );
		add_action( 'register_form', array( $this, 'loginForm' ) );
		add_action( 'wp_ajax_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_gigya_raas_login', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_raas_login', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_login', array( $this, 'wpLogin', 10, 2 ) );
		add_action( 'user_register', array( $this, 'userRegister', 10, 1 ) );
		add_action( 'wp_logout', array( $this, 'wpLogout' ) );
		add_action( 'deleted_user', array( $this, 'deletedUser' ) );
		add_shortcode( 'gigya_user_info', array( $this, 'gigyaUserInfo' ) );
		add_filter( 'get_avatar', 'updateAvatarImage', 10, 5 );

	}

	/**
	 * Initialize hook.
	 */
	public function init() {

		require_once( GIGYA__PLUGIN_DIR . 'sdk/GSSDK.php' );
		require_once( GIGYA__PLUGIN_DIR . 'class/login/class.GigyaUser.php' );
		require_once( GIGYA__PLUGIN_DIR . 'class/api/class.GigyaApi.php' );

		// Load jQuery and jQueryUI from WP..
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'gigya_js', GIGYA__PLUGIN_URL . 'assets/scripts/gigya.js' );

// Parameters to be sent to the DOM.
		$params = array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'logoutUrl' => wp_logout_url(),
		);
		// Load params to be available to client-side script.
		wp_localize_script( 'gigya_login_js', 'gigyaParams', $params );

		// Checking that we have an API key and Gigya's plugin is turn on.
		if ( ! empty( $this->global_options['global_api_key'] ) ) {

			if ( $this->login_options['login_mode'] != 'wp_only' ) {

				// Load Gigya's socialize.js CDN.
				wp_enqueue_script( 'gigya', GIGYA__JS_CDN . $this->global_options['global_api_key'] );

			}

			if ( $this->login_options['login_mode'] == 'raas' ) {

				require_once( GIGYA__PLUGIN_DIR . 'class/raas/class.GigyaRaasLinks.php' );
				$gigyaRaasLinks = new GigyaRaasLinks;
				$gigyaRaasLinks->init();

//				require_once( GIGYA__PLUGIN_DIR . 'class/raas/class.GigyaRaasAction.php' );
//				$gigyaRaasAction = new GigyaRaasAction;
//				$gigyaRaasAction->init();

			}

		}

		if ( is_admin() ) {

			// Load the settings.
			require_once( GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php' );
			new GigyaSettings;

		}
	}

	/**
	 * admin_action_ hook.
	 * Fires when an 'action' REQUEST variable is sent.
	 */
	public function adminActionUpdate() {
		if ( isset( $_POST['gigya_login_settings'] ) ) {

			// When we turn on the Gigya's social login plugin,
			// We also turn on the WP 'Membership: Anyone can register' option.
			if ( $_POST['gigya_login_settings']['login_mode'] != 'wp_only' ) {

				update_option( 'users_can_register', 1 );

			}

		}
	}

	/**
	 * Hook login form.
	 * Hook register form.
	 */
	public function loginForm() {

		// Check Gigya's social login is turn on and there an API key filled.
		if ( $this->login_options['login_mode'] == 'wp_sl' && ! empty( $this->global_options['global_api_key'] ) ) {

			require_once( GIGYA__PLUGIN_DIR . 'class/login/class.GigyaLoginForm.php' );
			$gigyaLoginForm = new GigyaLoginForm;
			$gigyaLoginForm->init();

		}
	}

	/**
	 * Hook AJAX login.
	 */
	public function ajaxLogin() {

		require_once( GIGYA__PLUGIN_DIR . 'class/login/class.GigyaLoginAction.php' );
		$gigyaLoginAction = new GigyaLoginAction;
		$gigyaLoginAction->init();

	}

	/**
	 * Hook AJAX RAAS login.
	 */
	public function ajaxRaasLogin() {

		require_once( GIGYA__PLUGIN_DIR . 'class/raas/class.GigyaRaasAction.php' );
		$gigyaLoginAction = new GigyaRaasAction;
		$gigyaLoginAction->init();

	}

	/**
	 * Hook user login.
	 *
	 * @param $user_login
	 * @param $account
	 */
	public function wpLogin( $user_login, $account ) {

		if ( empty ( $_SESSION['gigya_uid'] ) && empty( $_POST['gigyaUID'] ) ) {

			// Notify Gigya socialize.notifyLogin
			// for a return user logged in from SITE.
			$gigyaUser = new GigyaUser( $account->ID );
			$gigyaUser->notifyLogin( $account->ID );

		}

		// This post is when there is a same email on the site,
		// with the one who try to register and we want to link-accounts
		// after the user is logged in with password.
		if ( $_POST['form_name'] == 'loginform-gigya-link-account' ) {

			$gigyaUser = new GigyaUser( $_POST['gigya_uid'] );
			$gigyaUser->notifyRegistration( $account->ID );

		}
	}

	/**
	 * Hook user register.
	 *
	 * @param $uid
	 */
	public function userRegister( $uid ) {

		if ( ! empty( $_POST['gigyaUID'] ) ) {

			// New user was register through our custom
			// extra form. We make a login.
			$wp_user = get_userdata( $uid );
			require_once( GIGYA__PLUGIN_DIR . 'class/login/class.GigyaLoginAction.php' );
			GigyaLoginAction::login( $wp_user );

		}

		if ( ! empty ( $_SESSION['gigya_uid'] ) || ! empty( $_POST['gigyaUID'] ) ) {

			// New user was register through Gigya.
			// We make a notifyRegistration to Gigya.
			$gid       = ! empty( $_SESSION['gigya_uid'] ) ? $_SESSION['gigya_uid'] : $_POST['gigyaUID'];
			$gigyaUser = new GigyaUser( $gid );
			$gigyaUser->notifyRegistration( $uid );

		} else {

			// New user was register through WP form.
			// We notify to Gigya's 'socialize.notifyLogin'
			// with a 'is_new_user' flag.
			$gigyaUser = new GigyaUser( $_SESSION['gigya_uid'] );
			$gigyaUser->notifyLogin( $uid, TRUE );

		}
	}

	/**
	 * Hook user logout
	 */
	public function wpLogout() {

		// Get the current user.
		$account = wp_get_current_user();

		if ( ! empty ( $account->ID ) ) {

			// We using Gigya's account-linking (best practice).
			// So the siteUID is the same as Gigya's UID.
			$gigyaUser = new GigyaUser( $account->ID );
			$gigyaUser->logout();

		}
	}

	/**
	 * Hook delete user.
	 *
	 * @param $user_id
	 */
	public function deletedUser( $user_id ) {

		// Check it logged in by Gigya.
		if ( empty ( $_SESSION['gigya_uid'] ) ) {

			$gigyaUser = new GigyaUser( $_SESSION['gigya_uid'] );
			$gigyaUser->deleteAccount( $user_id );

		}
	}

	private function gigyaUserInfo( $atts, $info = NULL ) {

		$wp_user = wp_get_current_user();
		if ( $info == NULL ) {

			$gigyaUser = new GigyaUser( $wp_user->UID );
			$user_info = $gigyaUser->getUserInfo();

		}

		return $user_info->getString( key( $atts ), current( $atts ) );
	}

	/**
	 * @todo needs work.
	 * @param $avatar
	 * @param $id_or_email
	 * @param $size
	 * @param $default
	 * @param $alt
	 *
	 * @return String
	 */
	public function updateAvatarImage( $avatar, $id_or_email, $size, $default, $alt ) {

		if ( is_object( $id_or_email ) ) {
			$id_or_email = $id_or_email->user_id;
		}

		if ( is_numeric( $id_or_email ) ) {
			$thumb = get_user_meta( $id_or_email, "avatar", 1 );
			if ( ! empty( $thumb ) ) {
				$avatar = preg_replace( "/src='*?'/", "src='$thumb'", $avatar );
			}
		}

		return $avatar;
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

// --------------------------------------------------------------------