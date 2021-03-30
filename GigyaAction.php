<?php

namespace Gigya\WordPress;

use Exception;
use Gigya\CMSKit\GigyaCMS;
use Gigya\CMSKit\GSApiException;
use Gigya\PHP\GSException;
use Gigya\PHP\GSResponse;
use Gigya\WordPress\Admin\GigyaSettings;
use GigyaHookException;
use GigyaInstall;
use WP_User;

/**
 * The main plugin class.
 */
class GigyaAction {
	protected $login_options;
	protected $global_options;
	protected $session_options;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Gigya configuration values.
		$this->login_options   = get_option( GIGYA__SETTINGS_LOGIN );
		$this->global_options  = get_option( GIGYA__SETTINGS_GLOBAL );
		$this->session_options = get_option( GIGYA__SETTINGS_SESSION );

		/* Retrieve basic SAP CDC authentication parameters */
		if ( ! empty( $this->global_options ) ) {
			define( 'GIGYA__API_KEY', $this->global_options['api_key'] );
			define( 'GIGYA__USER_KEY', $this->global_options['user_key'] ?? '' );
			define( 'GIGYA__AUTH_MODE', $this->global_options['auth_mode'] ?? 'user_secret' );
			define( 'GIGYA__AUTH_KEY', _gigya_auth_key( $this->global_options ) );
			define( 'GIGYA__API_SECRET', $this->global_options['api_secret'] );
			define( 'GIGYA__PRIVATE_KEY', $this->global_options['rsa_private_key'] ?? '' );
			define( 'GIGYA__API_DOMAIN', _gigya_data_center( $this->global_options ) );
			define( 'GIGYA__API_DEBUG', $this->global_options['debug'] );
			define( 'GIGYA__JS_CDN', 'https://cdns.' . GIGYA__API_DOMAIN . '/js/socialize.js' );
		} else {
			define( 'GIGYA__API_KEY', '' );
			define( 'GIGYA__USER_KEY', '' );
			define( 'GIGYA__AUTH_MODE', '' );
			define( 'GIGYA__AUTH_KEY', '' );
			define( 'GIGYA__API_SECRET', '' );
			define( 'GIGYA__PRIVATE_KEY', '' );
			define( 'GIGYA__API_DOMAIN', '' );
			define( 'GIGYA__API_DEBUG', '' );
			define( 'GIGYA__JS_CDN', GIGYA__DEFAULT_JS_CDN );
		}

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_action_update', array( $this, 'adminActionUpdate' ) );
		add_action( 'wp_ajax_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_login', array( $this, 'ajaxLogin' ) );
		add_action( 'wp_ajax_gigya_raas', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_ajax_nopriv_gigya_raas', array( $this, 'ajaxRaasLogin' ) );
		add_action( 'wp_ajax_gigya_process_field_mapping', array( $this, 'ajaxProcessFieldMapping' ) );
		add_action( 'wp_ajax_nopriv_gigya_process_field_mapping', array( $this, 'ajaxProcessFieldMapping' ) );
		add_action( 'wp_ajax_custom_login', array( $this, 'ajaxCustomLogin' ) );
		add_action( 'wp_ajax_nopriv_custom_login', array( $this, 'ajaxCustomLogin' ) );
		add_action( 'wp_ajax_fixed_session_cookie', array( $this, 'ajaxSetFixedSessionCookie' ) );
		add_action( 'wp_ajax_nopriv_fixed_session_cookie', array( $this, 'ajaxSetFixedSessionCookie' ) );
		add_action( 'wp_ajax_debug_log', array( $this, 'ajaxDebugLog' ) );
		add_action( 'wp_ajax_clean_db', array( $this, 'ajaxCleanDB' ) );
		add_action( 'wp_ajax_gigya_logout', array( $this, 'ajaxLogout' ) );
		add_action( 'wp_ajax_nopriv_gigya_logout', array( $this, 'ajaxLogout' ) );
		add_action( 'wp_ajax_raas_update_profile', array( $this, 'ajaxUpdateProfile' ) );
		add_action( 'wp_login', array( $this, 'wpLogin' ), 10, 2 );
		add_action( 'user_register', array( $this, 'userRegister' ), 10, 1 );
		add_action( 'delete_user', array( $this, 'deleteUser' ) );
		add_action( 'wpmu_delete_user', array( $this, 'deleteUser' ) );
		add_action( 'widgets_init', array( $this, 'widgetsInit' ) );
		add_action( 'set_logged_in_cookie', 'updateCookie', 10, 2 );
		add_action( 'rest_api_init', array( $this, 'appendUserMetaToRestAPI' ) );
		add_action( 'gigya_offline_sync_cron', array( $this, 'executeOfflineSyncCron' ) );
		add_shortcode( 'gigya_user_info', array( $this, 'shortcodeUserInfo' ) );
		add_filter( 'get_avatar', array( $this, 'getGigyaAvatar' ), 10, 5 );
		add_filter( 'login_message', 'raas_wp_login_custom_message' );
		add_filter( 'cron_schedules', array( $this, 'getOfflineSyncSchedules' ) );
		add_action( 'wp_ajax_get_out_of_sync_users', array( $this, 'getOutOfSyncUsers' ) );
		add_action( 'get_out_of_sync_users', array( $this, 'getOutOfSyncUsers' ) );
		add_action( 'wp_ajax_nopriv_get_out_of_sync_users', array( $this, 'getOutOfSyncUsers' ) );


		/* Plugins shortcode activation switches */
		require_once GIGYA__PLUGIN_DIR . 'features/gigyaPluginsShortcodes.php';
		$shortcodes_class = new gigyaPluginsShortcodes();

		add_shortcode( 'gigya-raas-login', array( $shortcodes_class, 'gigyaRaas' ) );
		add_shortcode( 'gigya-raas-profile', array( $shortcodes_class, 'gigyaRaas' ) );
		add_shortcode( 'gigya-social-login', array( $shortcodes_class, 'gigyaSocialLoginScode' ) );
		/* End plugins shortcodes activation switches */
	}

	/**
	 * Initialize hook.
	 */
	public function init()
	{
		/* Require SDK libraries */
		if ( file_exists( GIGYA__PLUGIN_DIR . 'vendor/autoload.php' ) ) { /* If the plugin is installed by Composer as a `wordpress-plugin`, this becomes unnecessary, but require_once makes sure it's not included again */
			require_once GIGYA__PLUGIN_DIR . 'vendor/autoload.php';
		}
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaJsonObject.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaUserFactory.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaProfile.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaUser.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaApiRequest.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaAuthRequest.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GSApiException.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GSFactory.php';

		GSResponse::init();

		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaApiHelper.php';
		require_once GIGYA__PLUGIN_DIR . 'cms_kit/GigyaCMS.php';

		/* Load jQuery and jQueryUI from WP */
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'gigya_js', GIGYA__PLUGIN_URL . 'gigya.js' );
		wp_enqueue_style( 'gigya_css', GIGYA__PLUGIN_URL . 'gigya.css' );

		/* Parameters to be sent to the DOM, and later to Gigya */
		$session_expirations = _gigya_get_session_expiration( $this->session_options );
		$params              = array(
			'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
			'logoutUrl'                   => wp_logout_url(),
			'connectWithoutLoginBehavior' => _gigParam( $this->login_options, 'connectWithoutLoginBehavior', 'loginExistingUser' ),
			'jsonExampleURL'              => GIGYA__PLUGIN_URL . 'admin/forms/json/advance_example.json',
			'enabledProviders'            => _gigParam( $this->global_options, 'enabledProviders', '*' ),
			'lang'                        => _gigParam( $this->global_options, 'lang', 'en' ),
			'sessionExpiration'           => $session_expirations['sessionExpiration'],
			'rememberSessionExpiration'   => $session_expirations['rememberSessionExpiration']
		);

		/* Sync Gigya and WordPress sessions */
		$this->gigyaSyncLoginSession(
			( isset( $this->login_options['mode'] ) ? $this->login_options['mode'] : '' ),
			$this->getSessionOptions()
		);
		/*getting new logout url after session sync*/
		$params['logoutUrl'] = wp_logout_url();


		/* Add advanced parameters if exist */
		if ( ! empty( $this->global_options['advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( _gigParam( $this->global_options, 'advanced', '' ) );
			if ( is_array( $advanced ) ) {
				$params = array_merge( $params, $advanced );
			}
		}

		/* Let others plugins to modify the global parameters */
		$params = apply_filters( 'gigya_global_params', $params );

		/* Load params to be available to client-side script */
		wp_localize_script( 'gigya_js', 'gigyaParams', $params );

		/* Checking that we have an API key and Gigya's plugin is turned on */
		$api_key = GIGYA__API_KEY;
		if ( ! empty( $api_key ) ) {
			/*	* Loads requirements for any Gigya's login
				* Load Gigya's socialize.js from CDN */
			wp_enqueue_script( 'gigya_cdn', GIGYA__JS_CDN . '?apiKey=' . GIGYA__API_KEY . '&lang=' . $params['lang'] );

			if ( ! empty( $this->login_options ) ) /* Empty only happens on initial plugin enable, before configuring it */ {
				/* Social Login – load requirements  */
				if ( $this->login_options['mode'] == 'wp_sl' ) {
					require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginSet.php';
					$gigyaLoginSet = new GigyaLoginSet;
					$gigyaLoginSet->init();
				}

				/* RaaS Login – load requirements */
				if (
					$this->login_options['mode'] == 'raas'
					and defined( 'GIGYA__USER_KEY' )
						and ( ! empty( GIGYA__USER_KEY ) )
							and ( ! empty( GIGYA__AUTH_KEY ) )
				) {
					require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaOfflineSync.php';

					/* Loads RaaS links class */
					require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasSet.php';
					$gigyaRaasSet = new GigyaRaasSet;
					$gigyaRaasSet->init();

					/* Updates GltExp cookie */
					require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasAjax.php';
					$raasAjaxObject = new GigyaRaasAjax();
					$raasAjaxObject->updateGltExpCookie();
				}
			}

			/* Loads requirements for any Gigya's Google-Analytics integration. */
			if ( ! empty( $this->global_options['google_analytics'] ) ) {
				wp_enqueue_script( 'gigya_ga', GIGYA__CDN_PROTOCOL . '.gigya.com/js/gigyaGAIntegration.js' );
			}
		}

		if ( is_admin() ) {
			/* Loads requirements for the admin settings section. */
			require_once GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php';
			require_once GIGYA__PLUGIN_DIR . 'features/admin/GigyaReportGenerator.php';

			new GigyaSettings;
		}
	}

	/**
	 * admin_action_ hook
	 * Fires when an 'action' REQUEST variable is sent.
	 *
	 * @throws Exception
	 */
	public function adminActionUpdate() {
		require_once GIGYA__PLUGIN_DIR . 'admin/admin.GigyaSettings.php';

		GigyaSettings::onSave();
	}

	/**
	 * Hook AJAX login
	 *
	 * @throws Exception
	 */
	public function ajaxLogin() {
		// Loads Gigya's social login class.
		require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginAjax.php';
		$gigyaLoginAjax = new GigyaLoginAjax;
		$gigyaLoginAjax->init();
	}

	/**
	 * Hook AJAX RaaS login.
	 *
	 * @throws Exception
	 */
	public function ajaxRaasLogin() {
		// Loads Gigya's RaaS class.
		require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasAjax.php';
		$gigyaLoginAjax = new GigyaRaasAjax;
		$gigyaLoginAjax->init();
	}

	/**
	 * Process field mapping
	 *
	 * @throws Exception
	 */
	public function ajaxProcessFieldMapping() {
		$wp_uid      = get_current_user_id();
		$generic_msg = 'You are not logged in correctly';

		if ( ! empty( $wp_uid ) ) {
			$gigya_uid = get_user_meta( $wp_uid, 'gigya_uid', true );

			if ( ! empty( $gigya_uid ) ) {
				$gigya_cms = new GigyaCMS();

				try {
					$gigya_account = $gigya_cms->getAccount( $gigya_uid );

					_gigya_add_to_wp_user_meta( $gigya_account, $wp_uid );

					wp_send_json_success();
				} catch ( Exception $e ) {
					error_log( 'Unable to process field mapping for SAP Customer Data Cloud user ' . $gigya_uid );

					wp_send_json_error( [ 'msg' => $generic_msg ] );
				}
			} else {
				wp_send_json_error( [ 'msg' => $generic_msg ] );
			}
		} else {
			wp_send_json_error( [ 'msg' => $generic_msg ] );
		}
	}

	/**
	 * @throws GSApiException
	 * @throws GSException
	 */
	public function ajaxUpdateProfile() {
		// Loads Gigya's RaaS class.
		require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasAjax.php';
		$gigyaAjax = new GigyaRaasAjax;
		$data      = $_POST['data'];
		$gigyaAjax->updateProfile( $data );
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
		$this->gigyaSyncLogout();
		wp_send_json_success();
	}

	public function ajaxSetFixedSessionCookie() {
		$session_options = $this->getSessionOptions();

		$return = array(
			$_POST['expiration'],
			time() + $session_options['session_duration'],
		);

		$expiration = intval( $_POST['expiration'] / 1000 ) - time();
		if ( $this->login_options['mode'] == 'raas' and $session_options['session_type_numeric'] > 0 ) /* Fixed session in RaaS */ {
			$return[] = $this->gigyaSyncLoginSession( 'raas', $session_options, $expiration );
		}

		echo json_encode( $return );

		wp_die();
	}

	/**
	 * Necessary for hooking into the auth_cookie_expiration hook. The apply_filters function uses $expiration, $user_id and $remember
	 *
	 * @param int $expiration
	 * @param int $user_id
	 * @param boolean $remember
	 * @param int $forced_expiration
	 *
	 * @return int
	 */
	public function getHookSessionExpiration( $expiration = null, $user_id = null, $remember = null, $forced_expiration = null ) {
		if ( $forced_expiration ) {
			return $forced_expiration;
		}

		$default_expiration = GIGYA__DEFAULT_COOKIE_EXPIRATION;
		$expiration         = $default_expiration;

		$session_options = $this->getSessionOptions( $remember );

		if ( isset( $session_options['session_type_numeric'] ) ) {
			switch ( $session_options['session_type_numeric'] ) {
				case GIGYA__SESSION_DEFAULT: /* Until browser closes */
				case GIGYA__SESSION_FOREVER: /* Forever */
					$expiration = YEAR_IN_SECONDS;
					break;
				default: /* Fixed or dynamic session */
					$expiration = $session_options['session_duration'];
					break;
			}
		}

		return $expiration;
	}

	/**
	 * Get Login session time from Gigya's plugin, as configured by the user, and syncs it with WordPress using the auth_cookie_expiration hook
	 *
	 * @param string $mode Whether RaaS (raas) or Social Login (wp_sl)
	 * @param array $session_opts Login options for RaaS (session duration etc.)
	 * @param int $forced_expiration
	 *
	 * @return    integer
	 */
	public function gigyaSyncLoginSession( $mode, $session_opts, $forced_expiration = null ) {
		$session_type   = GIGYA__DEFAULT_COOKIE_EXPIRATION;
		$is_remember_me = ( _gigya_get_session_remember() );

		if ( $mode == 'raas' ) {
			if ( isset( $session_opts['session_type_numeric'] ) ) {
				$session_type = intval( $session_opts['session_type_numeric'] );
				$expiration   = $this->getHookSessionExpiration( null, null, null, $forced_expiration );

				if ( $forced_expiration ) {
					$expiration = $forced_expiration;
				}

				if ( $session_type > 0 ) {
					$session_type = $expiration;
				}

				/* Updates WP cookie expiration--WP runs apply_filters on this */
				add_filter( 'auth_cookie_expiration', array( $this, 'getHookSessionExpiration' ), 10, 3 );

				$gltexp_cookie           = isset( $_COOKIE[ 'gltexp_' . GIGYA__API_KEY ] ) ? $_COOKIE[ 'gltexp_' . GIGYA__API_KEY ] : '';
				$gltexp_cookie_timestamp = explode( '_', $gltexp_cookie )[0];
				if ( ( ( $session_type === GIGYA__SESSION_SLIDING ) and ( time() < $gltexp_cookie_timestamp ) )
				     or ( $session_type > 0 and $forced_expiration ) ) {
					$wp_user = wp_get_current_user();
					wp_set_auth_cookie( $wp_user->ID, $is_remember_me );

					$data = $_POST;

					/*set the login coockie except while there is logout action*/
					if ( empty( $data ) or $data['action'] !== 'gigya_logout' ) {
						do_action( 'set_logged_in_cookie', null, $expiration );
					}

				}
			}
		}

		return (int) $session_type;
	}

	public function gigyaSyncLogout() {
		if ( isset( $_COOKIE[ 'gltexp_' . GIGYA__API_KEY ] ) ) {
			unset( $_COOKIE[ 'gltexp_' . GIGYA__API_KEY ] );
			setrawcookie( 'gltexp_' . GIGYA__API_KEY, null, - 1, '/' );
		}
		_gigya_remove_session_remember();
	}

	public function appendUserMetaToRestAPI() {
		register_rest_field( 'user',
			'gigya_fields',
			[
				'get_callback' => function ( $user_data ) {
					if ( apply_filters( 'rest_show_user_meta', $user_data['id'] ) ) {
						$nonce = ( isset( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
						if ( wp_verify_nonce( $nonce, 'wp_rest' ) ) {
							$login_opts = get_option( GIGYA__SETTINGS_FIELD_MAPPING );
							$meta       = get_user_meta( $user_data['id'] );

							if ( ! empty( $login_opts['map_raas_full_map'] ) ) /* Fully customized field mapping options */ {
								foreach ( json_decode( $login_opts['map_raas_full_map'] ) as $meta_key ) {
									$meta_key = ( (array) $meta_key );
									$key      = $meta_key['cmsName'];

									if ( isset( $meta[ $key ] ) ) {
										$meta_trimmed[ $key ] = $meta[ $key ];
									}
								}

								return ( ! empty( $meta_trimmed ) ) ? $meta_trimmed : []; /* array() for fallback compatibility */
							} else {
								return [];
							}
						} else {
							return [];
						}
					} else {
						return [];
					}
				},
			]
		);
	}

	/**
	 * Hook to WP user login.
	 * If user logs in with WP form, check if raas is enabled,
	 * if so check if user has allowed capabilities
	 * if not log user out, if yes notify gigya.
	 *
	 * @param $user_login
	 * @param $account
	 *
	 * @throws Exception
	 */
	public function wpLogin( $user_login, $account ) {
		/* Login through WP form. */
		if ( isset( $_POST['log'] ) and isset( $_POST['pwd'] ) ) {
			/* Trap for non-admin user who tries to login through WP form on RaaS mode. */
			$_is_allowed_user = check_raas_allowed_user_role( $account->roles );
			if ( $this->login_options['mode'] == 'raas' and ( ! $_is_allowed_user ) )
			{
				wp_logout();
				$this->gigyaSyncLogout();
				wp_safe_redirect( $_SERVER['REQUEST_URI'] . '?rperm=1' ); // rperm used to create custom error message in wp login screen
				exit;
			}

			/* Notify Gigya socialize.notifyLogin for a return user logged in from WP login form. */
			$gigyaCMS = new GigyaCMS();
			$gigyaCMS->notifyLogin( $account->ID );
		}

		if ( empty( $_POST['action'] ) and ! empty( $_POST['data']['action'] ) ) {
			$_POST['action'] = $_POST['data']['action'];
		}

		if ( empty( $_POST['action'] ) ) {
			error_log( 'Login: No POST action specified' );
		} else {
			/* RaaS Login */
			if ( $_POST['action'] === 'gigya_raas' ) {
				/* Update Gigya UID in WordPress user meta if it isn't set already */
				if ( isset( $_POST['data']['UID'] ) ) {
					$wp_gigya_uid = get_user_meta( $account->ID, 'gigya_uid', true );
					if ( empty( $wp_gigya_uid ) ) {
						add_user_meta( $account->ID, 'gigya_uid', $_POST['data']['UID'] );
					} elseif ( $wp_gigya_uid !== $_POST['data']['UID'] ) {
						wp_send_json_error( array( 'msg' => __( 'Oops! Someone is already registered with the email' ) ) );
					}
				}
			}

			/*
			 * These post vars are available when there is the same email on the site,
			 * with the one who try to register and we want to link-accounts
			 * after the user is logged in with password. Or login after email verify.
			 */
			if ( ( $_POST['action'] === 'link_accounts' or $_POST['action'] === 'custom_login' ) and ! empty ( $_POST['data'] ) ) {
				parse_str( $_POST['data'], $data );
				if ( ! empty( $data['gigyaUID'] ) ) {
					$gigyaCMS = new GigyaCMS();
					$gigyaCMS->notifyRegistration( $data['gigyaUID'], $account->ID );
				}
			}
		}
	}

	/**
	 * Hook user register.
	 *
	 * @param $uid
	 *
	 * @throws Exception
	 */
	public function userRegister( $uid ) {
		/* Registered through RaaS */
		if ( isset( $_POST['data']['UID'] ) ) {
			add_user_meta( $uid, 'gigya_uid', $_POST['data']['UID'] );
		}
		/* New user was registered through our custom extra-details form. */
		if ( isset( $_POST['form_name'] ) and $_POST['form_name'] == 'registerform-gigya-extra' and ! empty( $_POST['gigyaUID'] ) ) {
			add_user_meta( $uid, 'gigya_uid', $_POST['gigyaUID'] );
		}

		// New user was registered through Gigya social login.
		// $_POST['action'] == 'gigya_login';
		if ( $this->login_options['mode'] == 'wp_sl' ) {
			if ( ! empty( $_POST['data'] ) and ! empty( $_POST['data']['UID'] ) ) {

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

		/* New user was registered through WP form. */
		if ( isset( $_POST['user_login'] ) and isset( $_POST['user_email'] ) ) {
			/*
			 * We notify to Gigya's 'socialize.notifyLogin'
			 * with a 'is_new_user' flag.
			 */
			$gigyaCMS = new GigyaCMS();
			$gigyaCMS->notifyLogin( $uid, true );
		}
	}

	/**
	 * Hook delete user.
	 *
	 * @param $user_id
	 *
	 * @throws Exception
	 */
	public function deleteUser( $user_id ) {
		$gigyaCMS = new GigyaCMS();

		if ( $this->login_options['mode'] == 'wp_sl' ) {
			$gigyaCMS->deleteUser( $user_id );
		} elseif ( $this->login_options['mode'] == 'raas' ) {
			$account = get_userdata( $user_id );
			$gigyaCMS->deleteAccountByEmail( $account->data->user_email );
		}
	}

	/**
	 * Register widgets.
	 */
	public function widgetsInit() {
		if ( empty( $this->login_options ) ) /* Only happens on initial activation, before configuring Gigya */ {
			return false;
		}

		/* Screen-set Widget */
		require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaScreenSetWidget.php';
		register_widget( 'GigyaScreenSet_Widget' );

		/* RaaS Widget */
		$raas_on = $this->login_options['mode'] == 'raas';
		if ( ! empty( $raas_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasWidget.php';
			register_widget( 'GigyaRaas_Widget' );
		}

		/* Login Widget */
		$login_on = $this->login_options['mode'] == 'wp_sl';
		if ( ! empty( $login_on ) ) {
			require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginWidget.php';
			register_widget( 'GigyaLogin_Widget' );
		}

		return true;
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

	/**
	 * Get 'GIGYA__SYNC_REPORT_MAX_USERS' from Gigya and check if the users exists at WP DB. and each user have the same UID,
	 * and exactly the same with the same amount of users from WP that have been searching in Gigya.
	 *
	 */
	public function getOutOfSyncUsers() {

		if ( ! is_dir( GIGYA__USER_FILES ) ) {
			$message = "Could not generate report: The path: " . GIGYA__USER_FILES . " does not exist";
			error_log( $message );
			wp_send_json_error( $message );
			return;
		};

		$wp_to_gigya_compare = GigyaReportGenerator:: getWPUsersNotInGigya();
		if ( gettype( $wp_to_gigya_compare ) == 'string' ) {
			error_log( $wp_to_gigya_compare );
			wp_send_json_error( $wp_to_gigya_compare );
			return;
		}

		$gigya_to_wp_compare = GigyaReportGenerator::getGigyaUsersNotInWP();
		if ( gettype( $gigya_to_wp_compare ) == 'string' ) {
			error_log( $gigya_to_wp_compare );
			wp_send_json_error( $gigya_to_wp_compare );
			return;
		}
		$message = '';
		$keys    = array_keys( array_merge( $wp_to_gigya_compare, ( $gigya_to_wp_compare ) ) );

		/*generating files for each key*/
		foreach ( $keys as $key ) {

			if ( isset( $wp_to_gigya_compare[ $key ] ) and isset( $gigya_to_wp_compare[ $key ] ) ) {
				$merged_array = array_merge( $wp_to_gigya_compare[ $key ], $gigya_to_wp_compare[ $key ] );
			} else if ( isset( $wp_to_gigya_compare[ $key ] ) ) {
				$merged_array = $wp_to_gigya_compare[ $key ];
			} else if ( isset( $gigya_to_wp_compare[ $key ] ) ) {
				$merged_array = $gigya_to_wp_compare[ $key ];
			} else {
				$merged_array = array();
			}

			$file = fopen( GIGYA__USER_FILES . $key . '_' . date( "Y-m-d_H-i-s" ) . ".csv", 'w' );
			if ( strstr( $key, 'SAP_users' ) != false ) {
				fputcsv( $file, array( 'UID', 'Email' ) );
			} else {
				fputcsv( $file, array( 'ID', 'Email' ) );
			}
			if ( ! empty( $merged_array ) ) {
				$message .= '<br>* ' . $key . '_' . date( "Y-m-d_H-i-s" ) . '.csv';
				foreach ( $merged_array as $user ) {
					fputcsv( $file, $user );
				}
			}
			fclose( $file );
		}

		if ( empty( $message ) ) {
			$message = 'All the ' . number_format( GIGYA__SYNC_REPORT_MAX_USERS ) . ' users that checked are synchronized.';
		} else {
			$message = 'The report has been generated successfully and saved to: ' . GIGYA__USER_FILES . '<br> Generated filenames are below. Note, this list does not include empty files. <br>' . $message;
		}

		wp_send_json_success( $message );
	}

	/**
	 * Get WordPress user object by Gigya UID
	 *
	 * @param string $gigya_uid Gigya UID
	 *
	 * @return WP_User|false
	 */
	public function getWPUserByGigyaUid( $gigya_uid ) {
		$wp_user = get_users( [
			'meta_key'   => 'gigya_uid',
			'meta_value' => $gigya_uid,
		] );

		if ( ! empty( $wp_user ) ) {
			$wp_user = $wp_user[0];
		} else {
			return false;
		}

		return $wp_user;
	}

	public function getGigyaAvatar( $avatar, $id_or_email, $size, $default, $alt ) {
		if ( empty( $id_or_email ) ) {
			$id = get_current_user_id();
		} else {
			if ( is_numeric( $id_or_email ) ) {
				$id = $id_or_email;
			} elseif ( is_string( $id_or_email ) ) {
				$user = get_user_by( 'email', $id_or_email );
				$id   = $user->ID;
			} else {
				return $avatar;
			}
		}
		$url = get_user_meta( $id, "profile_image", true );
		if ( empty( $url ) ) {
			return $avatar;
		}
		$alt = empty( $alt ) ? get_user_meta( $id, "first_name", true ) : $alt;

		return "<img src='{$url}' alt='{$alt}' width='{$size}' height='{$size}'>";
	}

	public function getOfflineSyncSchedules( $schedules ) {
		$schedules['every_five_seconds'] = array(
			'interval' => 5,
			'display'  => __( 'Every five seconds' ),
		);

		$schedules['every_thirty_seconds'] = array(
			'interval' => 30,
			'display'  => __( 'Every thirty seconds' ),
		);

		$schedules['every_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Every minute' ),
		);

		$schedules['every_two_hours'] = array(
			'interval' => 7200,
			'display'  => __( 'Every two hours' ),
		);

		$settings                               = get_option( GIGYA__SETTINGS_FIELD_MAPPING );
		$schedules['gigya_offline_sync_custom'] = array(
			'interval' => ( ! empty( $settings['map_offline_sync_frequency'] ) ) ? ( $settings['map_offline_sync_frequency'] * 60 ) : 3600,
			'display'  => __( 'Custom' ),
		);

		return $schedules;
	}

	public function executeOfflineSyncCron() {
		/* Retrieve config variables */
		$config           = get_option( 'gigya_field_mapping_settings' );
		$job_config       = get_option( 'gigya_offline_sync_params' );
		$enable_job       = $config['map_offline_sync_enable'];
		$email_on_success = $config['map_offline_sync_email_on_success'];
		$email_on_failure = $config['map_offline_sync_email_on_failure'];
		$required_field   = 'profile'; /* Offline sync might not work on users without a profile */

		$helper = new GigyaOfflineSync();

		if ( $enable_job ) {
			try {
				$last_customer_update = null;
				$gigya_query          = "SELECT * FROM accounts";
				if ( ! empty( $job_config['last_customer_update'] ) ) {
					$last_customer_update = $job_config['last_customer_update'];
					$gigya_query          .= ' WHERE lastUpdatedTimestamp > ' . $last_customer_update;
				}
				$gigya_query     .= " ORDER BY lastUpdatedTimestamp ASC LIMIT " . GIGYA__OFFLINE_SYNC_MAX_USERS;
				$gigya_cms       = new GigyaCMS();
				$gigya_users     = $gigya_cms->searchGigyaUsers( [ 'query' => $gigya_query ], $required_field );
				$processed_users = 0;
				$users_not_found = 0;
				$uids_not_found  = [];

				foreach ( $gigya_users as $gigya_user ) {
					$gigya_uid                    = $gigya_user['UID'];
					$gigya_last_updated_timestamp = $gigya_user['lastUpdatedTimestamp'];

					if ( ! empty( $gigya_uid ) and ! empty( $gigya_last_updated_timestamp ) ) {
						$wp_user = $this->getWPUserByGigyaUid( $gigya_uid );
						if ( ! empty( $wp_user ) ) {
							_gigya_add_to_wp_user_meta( $gigya_user, $wp_user->ID );

							$job_config['last_customer_update'] = $gigya_last_updated_timestamp - GIGYA__OFFLINE_SYNC_UPDATE_DELAY;
							update_option( 'gigya_offline_sync_params', $job_config );

							$processed_users ++;
						} else {
							$users_not_found ++;
							$uids_not_found[] = $gigya_user['UID'];
						}
					} else {
						error_log( 'Gigya offline sync: unable to process user due to a lack of essential data. User data received: ' . json_encode( $gigya_user,
								JSON_PRETTY_PRINT ) );
					}
				}

				$job_config['last_run'] = round( microtime( true ) * 1000 );
				update_option( 'gigya_offline_sync_params', $job_config );

				error_log( 'Gigya offline sync completed. Users processed: ' . $processed_users . ( ( $users_not_found )
						? '. Users not found: ' . $users_not_found . PHP_EOL . implode( ',' . PHP_EOL, $uids_not_found )
						: '' ) );

				$status = ( $users_not_found > 0 ) ? 'completed with errors' : 'succeeded';
				$helper->sendCronEmail( 'offline sync', $status, $email_on_success, $processed_users, $users_not_found );
			} catch ( GigyaHookException $e ) {
				error_log( 'Gigya offline sync: There was a problem adding custom data to field mapping: ' . $e->getMessage() );
				$status = 'failed';
				$helper->sendCronEmail( 'offline sync', $status, $email_on_failure );
			} catch ( GSApiException $e ) {
				error_log( 'Offline sync failed: ' . $e->getErrorCode() . ' – ' . $e->getMessage() . '. Call ID: ' . $e->getCallId() );
				$status = 'failed';
				$helper->sendCronEmail( 'offline sync', $status, $email_on_failure );
			} catch ( Exception $e ) {
				error_log( 'Offline sync failed: ' . $e->getMessage() );
				$status = 'failed';
				$helper->sendCronEmail( 'offline sync', $status, $email_on_failure );
			}
		}
	}

	/**
	 * Shortcode for UserInfo.
	 *
	 * @param array $atts
	 * @param          $info
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function shortcodeUserInfo( $atts, $info = null ) {
		/**
		 * @var    WP_User
		 */
		$wp_user   = wp_get_current_user();
		$user_info = array();

		if ( $info == null ) {
			$gigyaCMS  = new GigyaCMS();
			$user_info = $gigyaCMS->getUserInfo( $wp_user->UID );
		}

		return json_encode( $user_info );
	}

	/**
	 * Retrieves the session_type, session_type_numeric, session_duration from the WP session options, taking Remember Me status into account.
	 * If Remember Me status isn't specified, it still checks
	 *
	 * @param boolean|null $is_remember_me
	 *
	 * @return array
	 */
	private function getSessionOptions( $is_remember_me = null ) {
		$options = $this->session_options;

		if ( $is_remember_me === null ) {
			$is_remember_me = ( _gigya_get_session_remember() );
		}

		if ( $is_remember_me ) {
			$options['session_type']         = $options['remember_session_type'];
			$options['session_type_numeric'] = $options['remember_session_type_numeric'];
			$options['session_duration']     = $options['remember_session_duration'];
		}

		return array_intersect_key( $options, array_flip( [
			'session_type',
			'session_type_numeric',
			'session_duration'
		] ) );
	}
}
