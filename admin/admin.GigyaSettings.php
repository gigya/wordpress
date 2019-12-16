<?php

/*
 * Plugin editing permission levels
 */
define( "GIGYA__PERMISSION_LEVEL", "manage_options" );
define( "GIGYA__SECRET_PERMISSION_LEVEL", "install_plugins" ); // Network super admin + single site admin

// custom Gigya capabilities are added separately on installation
define( "CUSTOM_GIGYA_EDIT", 'edit_gigya' );
define( "CUSTOM_GIGYA_EDIT_SECRET", 'edit_gigya_secret' );

class GigyaSettings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add Javascript and css to admin page
		wp_enqueue_style( 'gigya_admin_css', GIGYA__PLUGIN_URL . 'admin/gigya_admin.css' );
		wp_enqueue_script( 'gigya_admin_js', GIGYA__PLUGIN_URL . 'admin/gigya_admin.js' );
		wp_enqueue_script( 'gigya_jsonlint_js', GIGYA__PLUGIN_URL . 'admin/jsonlint.js' );

		// Actions.
		add_action( 'admin_init', array( $this, 'adminInit' ) );
		add_action( 'admin_menu', array( $this, 'adminMenu' ) );
	}

	/**
	 * Hook admin_init callback.
	 * Initialize Admin section.
	 */
	public function adminInit() {

		// Add settings sections.
		foreach ( $this->getSections() as $id => $section ) {
			$option_group = $section['slug'] . '-group';
			add_settings_section( $id, $section['title'], $section['func'], $section['slug'] );
			register_setting( $option_group, $section['slug'], array( $this, 'validate' ) );
			add_filter( "option_page_capability_{$option_group}", array( $this, 'addGigyaCapabilities' ) );
		}
	}

	/**
	 * Add gigya edit capability to allow custom roles to edit Gigya
	 */
	public function addGigyaCapabilities() {
		return CUSTOM_GIGYA_EDIT;
	}

	/**
	 * Hook admin_menu callback.
	 * Set Gigya's Setting area.
	 */
	public function adminMenu() {
		// Default admin capabilities
		if ( current_user_can( 'GIGYA__PERMISSION_LEVEL' ) ) {
			// Register the main Gigya setting route page.
			add_menu_page( 'Customer Data Cloud', 'Customer Data Cloud', GIGYA__PERMISSION_LEVEL, 'gigya_global_settings', array(
				$this,
				'adminPage'
			), GIGYA__PLUGIN_URL . 'admin/images/SAP_R_grad_scrn.jpg', '70.1' );

			// Register the sub-menus Gigya setting pages.
			foreach ( $this->getSections() as $section ) {

				require_once GIGYA__PLUGIN_DIR . 'admin/forms/' . $section['func'] . '.php';
				add_submenu_page( 'gigya_global_settings', __( $section['title'], $section['title'] ), __( $section['title'], $section['title'] ), GIGYA__PERMISSION_LEVEL, $section['slug'], array(
					$this,
					'adminPage'
				) );

			}
		} elseif ( current_user_can( CUSTOM_GIGYA_EDIT ) ) {
			// Register the main Gigya setting route page.
			add_menu_page( 'Customer Data Cloud', 'Customer Data Cloud', CUSTOM_GIGYA_EDIT, 'gigya_global_settings', array(
				$this,
				'adminPage'
			), GIGYA__PLUGIN_URL . 'admin/images/SAP_R_grad_scrn.png', '70.1' );

			// Register the sub-menus Gigya setting pages.
			foreach ( $this->getSections() as $section ) {

				require_once GIGYA__PLUGIN_DIR . 'admin/forms/' . $section['func'] . '.php';
				add_submenu_page( 'gigya_global_settings', __( $section['title'], $section['title'] ), __( $section['title'], $section['title'] ), CUSTOM_GIGYA_EDIT, $section['slug'], array(
					$this,
					'adminPage'
				) );

			}
		}
	}

	/**
	 * Returns the form sections definition
	 * @return array
	 */
	public static function getSections() {
		$login_options = get_option( GIGYA__SETTINGS_LOGIN );

		return array(
			'gigya_global_settings'        => array(
				'title' => 'Global Settings',
				'func'  => 'globalSettingsForm',
				'slug'  => 'gigya_global_settings'
			),
			'gigya_login_settings'         => array(
				'title' => 'User Management',
				'func'  => 'loginSettingsForm',
				'slug'  => 'gigya_login_settings'
			),
			'gigya_field_mapping_settings' => array(
				'title'   => 'Field Mapping',
				'func'    => 'fieldMappingForm',
				'slug'    => 'gigya_field_mapping_settings',
				'display' => ( isset( $login_options['mode'] ) and in_array( $login_options['mode'], [
						'raas',
						'wp_sl'
					] ) ) ? 'visible' : 'hidden',
			),
			'gigya_screenset_settings'     => array(
				'title' => 'Screen-Sets',
				'func'  => 'screenSetSettingsForm',
				'slug'  => 'gigya_screenset_settings'
			),
			'gigya_session_management'     => array(
				'title' => 'Session Management',
				'func'  => 'sessionManagementForm',
				'slug'  => 'gigya_session_management'
			),
			'gigya_share_settings'         => array(
				'title' => 'Share Settings',
				'func'  => 'shareSettingsForm',
				'slug'  => 'gigya_share_settings'
			),
			'gigya_comments_settings'      => array(
				'title' => 'Comments',
				'func'  => 'commentsSettingsForm',
				'slug'  => 'gigya_comments_settings'
			),
			'gigya_reactions_settings'     => array(
				'title' => 'Reactions',
				'func'  => 'reactionsSettingsForm',
				'slug'  => 'gigya_reactions_settings'
			),
			'gigya_gm_settings'            => array(
				'title' => 'Gamification',
				'func'  => 'gmSettingsForm',
				'slug'  => 'gigya_gm_settings'
			),
		);
	}

	/**
	 * Render the Gigya admin pages wrapper (Tabs, Form, etc.).
	 */
	public static function adminPage() {
		$page   = $_GET['page'];
		$render = '';

		echo _gigya_render_tpl( 'admin/tpl/adminPage-wrapper.tpl.php', array( 'page' => $page ) );
		settings_errors();

		echo '<form class="gigya-settings" action="options.php" method="post">' . PHP_EOL;
		echo '<input type="hidden" name="action" value="gigya_settings_submit">' . PHP_EOL;

		wp_nonce_field( 'update-options', 'update_options_nonce' );
		wp_nonce_field( 'wp_rest', 'wp_rest_nonce' );
		settings_fields( $page . '-group' );
		do_settings_sections( $page );
		submit_button();

		echo '</form>';

		return $render;
	}

	/**
	 * On Setting page save event.
	 *
	 * @throws Exception
	 */
	public static function onSave() {
		/* When a Gigya's setting page is submitted */
		if ( isset( $_POST['gigya_global_settings'] ) ) {
			$cms = new gigyaCMS();
			if ( static::_setSecret() ) {
				$res = $cms->apiValidate( $_POST['gigya_global_settings']['api_key'], $_POST['gigya_global_settings']['user_key'], GigyaApiHelper::decrypt( $_POST['gigya_global_settings']['api_secret'], SECURE_AUTH_KEY ), $_POST['gigya_global_settings']['data_center'] );
				if ( ! empty( $res ) ) {
					$gigyaErrCode = $res->getErrorCode();
					if ( $gigyaErrCode > 0 ) {
						$gigyaErrMsg = $res->getErrorMessage();
						$errorsLink  = "<a href='https://developers.gigya.com/display/GD/Response+Codes+and+Errors+REST' target='_blank' rel='noopener noreferrer'>Response_Codes_and_Errors</a>";
						$message     = "SAP CDC API error: {$gigyaErrCode} - {$gigyaErrMsg}.";
						add_settings_error( 'gigya_global_settings', 'api_validate', __( $message . " For more information please refer to {$errorsLink}", 'error' ) );
						error_log( 'Error updating SAP CDC settings: ' . $message . ' Call ID: ' . $res->getString( "callId", "N/A" ) );

						/* Prevent updating values */
						static::_keepOldApiValues();
					}
				} else {
					add_settings_error( 'gigya_global_settings', 'api_validate', __( 'Error sending request to Gigya' ), 'error' );
				}
			} else {
				add_settings_error( 'gigya_global_settings', 'api_validate', __( 'Error retrieving existing secret key from the database. This is normal if you have a multisite setup. Please re-enter the secret key.' ), 'error' );
			}
		} elseif ( isset( $_POST['gigya_login_settings'] ) ) {
			/*
			 * When we turn on the Gigya's social login plugin, we also turn on the WP 'Membership: Anyone can register' option
			 */
			if ( $_POST['gigya_login_settings']['mode'] == 'wp_sl' ) {
				update_option( 'users_can_register', 1 );
			} elseif ( $_POST['gigya_login_settings']['mode'] == 'raas' ) {
				update_option( 'users_can_register', 0 );
			}
		} elseif ( isset( $_POST['gigya_field_mapping_settings'] ) ) {
			/* Validate field mapping settings, including offline sync */
			$data = $_POST['gigya_field_mapping_settings'];
			if ( $data['map_offline_sync_enable'] ) {
				if ( $data['map_offline_sync_frequency'] < GIGYA__OFFLINE_SYNC_MIN_FREQ ) {
					add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate',
						__( 'Error: Offline sync job frequency cannot be lower than ' . GIGYA__OFFLINE_SYNC_MIN_FREQ . ' minutes' ),
						'error' );
					static::_keepOldApiValues( 'gigya_field_mapping_settings' );
				}

				$emails_are_valid = true;
				foreach ( array_merge( explode( ',', $data['map_offline_sync_email_on_success'] ), explode( ',', $data['map_offline_sync_email_on_failure'] ) ) as $email ) {
					if ( $email and ! is_email( $email ) ) {
						$emails_are_valid = false;
					}
				}
				if ( ! $emails_are_valid ) {
					add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate', __( 'Error: Invalid emails entered' ), 'error' );
					static::_keepOldApiValues( 'gigya_field_mapping_settings' );
				}
			}

			/*
			 * Deletes cron and re-enables it. This way it's possible to change the cron's interval, and prevents from scheduling duplicates
			 * (WP doesn't overwrite a cron even if it has the same name. Instead, it creates a new one).
			 */
			$cron_name = 'gigya_offline_sync_cron';
			wp_clear_scheduled_hook( $cron_name );
			if ( $data['map_offline_sync_enable'] ) {
				wp_schedule_event( time(), 'gigya_offline_sync_custom', $cron_name );
			}
		} elseif ( isset( $_POST['gigya_screenset_settings'] ) ) {
			foreach ( $_POST['gigya_screenset_settings']['custom_screen_sets'] as $key => $screen_set ) {
				if ( in_array( $screen_set['desktop'], array_column( $_POST['gigya_screenset_settings']['custom_screen_sets'], 'id' ) ) ) {
					$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['id'] = self::generateMachineName( $screen_set['desktop'], $key );
				} else {
					$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['id'] = $screen_set['desktop'];
				}
				$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['value'] = $screen_set['desktop'];

				if ( ! isset( $screen_set['mobile'] ) ) {
					$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['mobile'] = 'desktop';
				} else {
					if ( ! isset( $screen_set['desktop'] ) ) {
						$gigyaErrMsg = 'Custom Scree-Set without Desktop value will not be saved';
						$message     = "SAP CDC  error: {$gigyaErrMsg}.";
						add_settings_error( 'gigya_screenset_settings', 'empty_desktop_screen_set', __( $message ) );
						error_log( 'Error updating SAP CDC settings: ' . $message );
						unset( $_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ] );
					}
				}
			}
		}
	}

	public static function generateMachineName( $desktop_screen_set_id, $serial ) {
		$machine_name = $desktop_screen_set_id;
		if ( $serial !== 0 ) {
			$machine_name .= '_' . $serial;
		}


		return $machine_name;
	}

	/**
	 * Set the POSTed secret key.
	 * If it's not submitted, take it from DB.
	 */
	public static function _setSecret() {
		if ( empty( $_POST['gigya_global_settings']['api_secret'] ) ) {
			$options = static::_getSiteOptions();
			if ( $options === false ) {
				return false;
			}

			$_POST['gigya_global_settings']['api_secret'] = $options['api_secret'];
		} else {
			$_POST['gigya_global_settings']['api_secret'] = GigyaApiHelper::encrypt( $_POST['gigya_global_settings']['api_secret'], SECURE_AUTH_KEY );
		}

		return true;
	}

	/**
	 * Set the posted api related values to the old (from DB) values
	 *
	 * @param string $option The option under which to keep the settings
	 * @param null|string|array $settings Tells the function which specific old values to get, if we don't want all of them.
	 */
	public static function _keepOldApiValues( $option = '', $settings = [] ) {
		if ( ! $option ) {
			$options                                                   = self::_getSiteOptions();
			$_POST['gigya_global_settings']['api_key']                 = $options['api_key'];
			$_POST['gigya_global_settings']['user_key']                = $options['user_key'];
			$_POST['gigya_global_settings']['api_secret']              = $options['api_secret'];
			$_POST['gigya_global_settings']['data_center']             = $options['data_center'];
			$_POST['gigya_global_settings']['sub_site_settings_saved'] = $options['sub_site_settings_saved'];
		} elseif ( ! empty( $settings ) ) { /* $settings is an array--retrieve specific options */
			if ( $option ) {
				$options = self::_getSiteOptions( $option );
				foreach ( $settings as $setting ) {
					$_POST[ $option ][ $setting ] = $options[ $setting ];
				}
			}
		} else {
			$_POST[ $option ] = self::_getSiteOptions( $option );
		}
	}

	/**
	 * If multisite, get options from main site, else from current site
	 *
	 * @param string $option
	 *
	 * @return mixed
	 */
	public static function _getSiteOptions( $option = GIGYA__SETTINGS_GLOBAL ) {
		if ( is_multisite() ) {
			$options = get_blog_option( get_current_blog_id(), $option );
		} else {
			$options = get_option( $option );
		}

		return $options;
	}

}