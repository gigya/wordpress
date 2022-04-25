<?php

namespace Gigya\WordPress\Admin;

/*
 * Plugin editing permission levels
 */

use Exception;
use Gigya\CMSKit\GigyaApiHelper;
use Gigya\CMSKit\GigyaCMS;
use Gigya\PHP\GSException;
use Gigya\WordPress\GigyaLogger;

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

		//Adding variables to gigya_Admin.js
		$params = array(
			'max_execution_time'    => intval( ini_get( 'max_execution_time' ) ) * 1000,
			'offline_sync_min_freq' => GIGYA__OFFLINE_SYNC_MIN_FREQ
		);

		$params = apply_filters( 'gigya_admin_params', $params );
		wp_localize_script( 'gigya_admin_js', 'gigyaAdminParams', $params );

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
				add_submenu_page( 'gigya_global_settings',
					__( $section['title'], $section['title'] ),
					__( $section['title'], $section['title'] ),
					GIGYA__PERMISSION_LEVEL, $section['slug'], [
						$this,
						'adminPage',
					] );
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
				add_submenu_page( 'gigya_global_settings',
					__( $section['title'], $section['title'] ),
					__( $section['title'], $section['title'] ),
					CUSTOM_GIGYA_EDIT,
					$section['slug'], [
						$this,
						'adminPage',
					] );
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
		);
	}

	/**
	 * Render the Gigya admin pages wrapper (Tabs, Form, etc.).
	 */
	public static function adminPage() {
		$page   = $_GET['page'];
		$render = '';

		$are_dependencies_installed = class_exists('Gigya\\PHP\\GSRequest');
		$dependencies_missing_message = __('Fatal error: SAP Customer Data Cloud PHP SDK has not been installed. The plugin will not work. Please install Composer dependencies before proceeding.');

		echo _gigya_render_tpl( 'admin/tpl/adminPage-wrapper.tpl.php', array(
			'sections' => self::getSections(),
			'page'     => $page,
		) );
		if (!$are_dependencies_installed) {
			add_settings_error($page, 'dependencies-not-installed-error', $dependencies_missing_message, 'error');
		}
		settings_errors();

		echo '<form class="gigya-settings" action="options.php" method="post">' . PHP_EOL;
		echo '<input type="hidden" name="action" value="gigya_settings_submit">' . PHP_EOL;

		wp_nonce_field( 'update-options', 'update_options_nonce' );
		wp_nonce_field( 'wp_rest', 'wp_rest_nonce' );
		settings_fields( $page . '-group' );

		if ($are_dependencies_installed) {
			do_settings_sections( $page );
			submit_button();
		} else {
			echo '<h4>' . $dependencies_missing_message . '</h4>';
		}

		echo '</form>';

		return $render;
	}

	/**
	 * On Setting page save event.
	 *
	 * @throws Exception
	 */
	public static function onSave() {
		$cms    = new gigyaCMS();
		$logger = new GigyaLogger();


		/* When a Gigya's setting page is submitted */
		if ( isset( $_POST['gigya_global_settings'] ) ) {
			$has_error = false;
			$has_warning = false;
			$gigya_error_log_file = $logger->getGigyaLogFilePointer() ;

			$auth_field = 'api_secret';
			if ( $_POST['gigya_global_settings']['auth_mode'] === 'user_rsa' ) {
				$auth_field                                       = 'rsa_private_key';
				$_POST['gigya_gigya_settings']['rsa_private_key'] = '';
			} else {
				$_POST['gigya_gigya_settings']['api_secret'] = '';
			}

			if ( self::_setObfuscatedField( $auth_field ) ) {
				$res = $cms->apiValidate(
					( empty( $_POST['gigya_global_settings']['auth_mode'] === 'user_rsa' ) ) ? 'user_secret' : $_POST['gigya_global_settings']['auth_mode'],
					$_POST['gigya_global_settings']['api_key'],
					$_POST['gigya_global_settings']['user_key'],
					GigyaApiHelper::decrypt( $_POST['gigya_global_settings'][ $auth_field ], SECURE_AUTH_KEY ),
					_gigya_data_center( $_POST['gigya_global_settings'] )
				);

				if ( ! empty( $res ) ) {
					$gigya_error_code = $res->getErrorCode();
					if ( $gigya_error_code > 0 ) {
						$gigya_error_message = $res->getErrorMessage();

						self::setError( $gigya_error_code, $gigya_error_message, ( ! empty( $res->getData() ) ) ? $res->getString( "callId", "N/A" ) : null );

						/* Prevent updating values */
						static::_keepOldApiValues();
						$logger->error( 'Error saving Global Settings: Can\'t validate the admin user: ' . $res->getErrorCode() . ' - ' . $res->getErrorMessage() . ( ! empty( $res->getData() ) ? ( ', call ID: ' . $res->getString( "callId", "N/A" ) ) : '' ) );
						$has_error = true;
					}
				} else {
					add_settings_error( 'gigya_global_settings', 'api_validate', __( 'Error sending request to SAP CDC' ), 'error' );
					$logger->error( 'Global Settings page error: ' . 'Error sending request to SAP CDC' );
				$has_error = true;
				}
			} else {
				add_settings_error( 'gigya_global_settings', 'api_validate', __( 'Error retrieving existing secret key or private key from the database. This is normal if you have a multisite setup. Please re-enter the key.' ), 'error' );
				$logger->error( '"Global Settings" page: error retrieving existing secret key or private key from the database. This is normal if you have a multisite setup. Please re-enter the key.' );
				$has_error = true;
			}
			if ( $gigya_error_log_file === false ) {
				if ( ! $has_error ) {
					add_settings_error( 'gigya_global_settings', 'gigya_validate', __( 'Settings saved.' ) . '<p>' . __( 'Warning: Could not open the SAP CDC log file at: ' . GIGYA__LOG_FILE . '. The parent directory of the file does not exist, or the file is not writable.' ) . '</p>', 'warning' );
				}
			} else {
				fclose( $gigya_error_log_file );
				if ( ! $has_error and ! $has_warning ) {
					$logger->info( '"Global Settings" page was saved successfully.' );
				}
			};
		} elseif ( isset( $_POST['gigya_login_settings'] ) ) {
			/*
			 * When we turn on the Gigya's social login plugin, we also turn on the WP 'Membership: Anyone can register' option
			 */
			if ( $_POST['gigya_login_settings']['mode'] == 'wp_sl' ) {
				update_option( 'users_can_register', 1 );
			} elseif ( $_POST['gigya_login_settings']['mode'] == 'raas' ) {
				update_option( 'users_can_register', 0 );
			}

			$logger->info( '"User Management Settings" page was saved successfully.' );
		} elseif ( isset( $_POST['gigya_field_mapping_settings'] ) ) {
			$has_error   = false;
			$has_warning = false;


			/* Validate field mapping settings, including offline sync */
			$data = $_POST['gigya_field_mapping_settings'];
			if ( $data['map_offline_sync_enable'] ) {
				if ( $data['map_offline_sync_frequency'] < GIGYA__OFFLINE_SYNC_MIN_FREQ ) {
					add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate',
						__( 'Error: Offline sync job frequency cannot be lower than ' . GIGYA__OFFLINE_SYNC_MIN_FREQ . ' minutes' ),
						'error' );
					$logger->error( 'Field Mapping settings page error: Offline sync job frequency cannot be lower than ' . GIGYA__OFFLINE_SYNC_MIN_FREQ . ' minutes.' );
					static::_keepOldApiValues( 'gigya_field_mapping_settings' );
					$has_error = true;
				}

				$emails_are_valid = true;
				foreach ( array_merge( explode( ',', $data['map_offline_sync_email_on_success'] ), explode( ',', $data['map_offline_sync_email_on_failure'] ) ) as $email ) {
					if ( $email and ! is_email( $email ) ) {
						$emails_are_valid = false;
					}
				}
				if ( ! $emails_are_valid ) {
					add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate', __( 'Error: Invalid emails entered' ), 'error' );
					$logger->error( 'Field Mapping settings page error: Invalid emails entered.' );
					static::_keepOldApiValues( 'gigya_field_mapping_settings' );
					$has_error = true;
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

			if ( $data['map_raas_full_map'] ) {
				$error_message = '';
				$params        = [ 'include' => 'profileSchema, dataSchema, subscriptionsSchema, preferencesSchema, systemSchema' ];

				try {
					$response = $cms->call( 'accounts.getSchema', $params );

				} catch ( GSException $e ) {
					add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate', __( 'Settings saved.' ) . '<p>' . __( 'Warning: Can\'t reach SAP servers, please check the global configuration settings.' ) . '</p>', 'warning' );
					$logger->info( 'Field Mapping settings page warning: Can\'t reach SAP servers, please check the global configuration settings' );
					$has_warning = true;
				}
				if ( is_wp_error( $response ) ) {
					add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate', __( 'Settings saved.' ) . '<p>' . __( 'Warning: Can\'t reach SAP servers, please check the global configuration settings: ' ) . $response->get_error_message() . '</p>', 'warning' );
					$logger->info( 'Field Mapping settings page warning: Can\'t reach SAP servers, please check the global configuration settings ' . $response->get_error_message() );
					$has_warning = true;

				} elseif ( $response['errorCode'] === 0 ) {
					try {
						$error_message = static::getDuplicateAndMissingFields( $data['map_raas_full_map'], $response );
					} catch ( Exception $e ) {
						add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate', $e->getMessage(), 'error' );
						$logger->error( 'Field Mapping settings page error: ' . $e->getMessage() );
						static::_keepOldApiValues( 'gigya_field_mapping_settings' );
						$has_error = true;
					}
				}

				//Sending the warning message if necessary.
				if ( ! empty( $error_message ) ) {
					add_settings_error( 'gigya_field_mapping_settings', 'gigya_validate', static::buildFieldMappingHTMLWarning( $error_message ), 'warning' );
					$error_opening = 'Field Mapping settings page warning: ';
					array_unshift( $error_message, $error_opening );
					$logger->info( $error_message );
				}
			}
			if ( ! $has_error and ! $has_warning ) {
				$logger->info( '"Field Mapping Settings" page was saved successfully.' );
			}
		} elseif ( isset( $_POST['gigya_screenset_settings'] ) ) {
			/* Screen-set page validation */
			foreach ( $_POST['gigya_screenset_settings']['custom_screen_sets'] as $key => $screen_set ) {
				if ( ! empty( $screen_set['desktop'] ) ) {
					if ( in_array( $screen_set['desktop'], array_column( $_POST['gigya_screenset_settings']['custom_screen_sets'], 'id' ) ) ) {
						$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['id'] = self::generateMachineName( $screen_set['desktop'], $key );
					} else {
						$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['id'] = $screen_set['desktop'];
					}
					$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['value'] = $screen_set['desktop'];

					if ( empty( $screen_set['mobile'] ) && ! empty( $screen_set['desktop'] ) ) {
						$_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ]['mobile'] = 'desktop';
					}
				} else {
					unset( $_POST['gigya_screenset_settings']['custom_screen_sets'][ $key ] );
				}
			}
			$logger->info( '"Screen-Set Settings" page was saved successfully.' );
		} elseif ( isset( $_POST['gigya_session_management'] ) ) {
			$logger->info( '"Session Management Settings" page was saved successfully.' );

		}
	}


	/**
	 * The function reads the field mapping map and searches for duplication in cmsName fields,
	 * and missing fields in the gigyaCms property.
	 *
	 * @param $data string The data of field mapping setting page.
	 * @param $response array The response from 'accounts.getSchema' query.
	 *
	 * @return array The error message should be printing. Empty string will return in case of no errors.
	 * @throws Exception An exception will be thrown if there is an issue with the JSON, missing gigyName or cmsName, or if one of the properties is empty.
	 */
	private static function getDuplicateAndMissingFields( $data, $response ) {


		$array_of_wp_fields         = array();
		$duplications_of_wp_fields  = array();
		$unnecessary_fields         = array();
		$not_existing_fields        = array();
		$warning_message            = array();
		$invalid_json_error_message = 'Error: The field mapping configuration must be an array of objects containing the following fields: cmsName, gigyaName.';
		$json_block                 = json_decode( stripslashes( $data ), true );

		//JSON validations.
		if ( ( is_null( $json_block ) ) or ( $json_block === false ) or ( ! is_array( $json_block ) and ! empty( (array) $json_block ) ) ) {
			throw new Exception( $invalid_json_error_message );
		}

		if ( is_array( $json_block ) ) {

			//Searching each block.
			foreach ( $json_block as $meta_key ) {
				if ( ! is_array( $meta_key ) ) {
					throw new Exception( $invalid_json_error_message );
				}

				$error = static::getBlockValidationError( $meta_key );
				if ( ! empty( $error ) ) {
					throw new Exception( $error );
				}

				$gigya_name = $meta_key['gigyaName'];
				$cms_name   = $meta_key['cmsName'];


				//Searching for duplications of WP fields.
				if ( array_key_exists( $cms_name, $array_of_wp_fields ) === false ) {
					$array_of_wp_fields[ $cms_name ] = $gigya_name;
				} elseif ( $array_of_wp_fields[ $cms_name ] !== $gigya_name ) {
					$duplications_of_wp_fields[] = $cms_name;
				}

				//Searching for not existing fields in gigya
				if ( ! static::doesFieldExist( $response, $gigya_name ) ) {
					$not_existing_fields[] = $gigya_name;

				}

				//Getting unnecessary fields.
				if ( count( $meta_key ) > 2 ) {
					$fields = array_keys( $meta_key );
					foreach ( $fields as $field ) {
						if ( ( $field !== 'gigyaName' ) and ( $field !== 'cmsName' ) ) {
							$unnecessary_fields[] = $field;
						}
					}
				}
			}
		}

		$duplications_of_wp_fields_str = implode( ', ', $duplications_of_wp_fields );
		$not_existing_fields_str       = implode( ', ', $not_existing_fields );
		$unnecessary_fields_str        = implode( ', ', $unnecessary_fields );

		//Builds the error message.
		if ( ! empty( $not_existing_fields_str ) ) {
			$warning_message['not_existing_fields'] = array(
				'case'     => __( 'The following fields were not found in Customer Data Cloud\'s account schema:' ),
				'fields'   => $not_existing_fields_str,
				'solution' => __( 'Please make sure that the names are spelled correctly, or add the fields in the schema editor.' ),

			);
		}

		if ( ! empty( $duplications_of_wp_fields_str ) ) {
			$warning_message['duplications_of_wp_fields'] = array(
				'case'     => __( 'The following duplicates have been found in the cmsName field:' ),
				'fields'   => $duplications_of_wp_fields_str,
				'solution' => __( 'Field mapping for these fields may not work as expected.' ),
			);
		}

		if ( ! empty( $unnecessary_fields_str ) ) {
			$warning_message['unnecessary_fields'] = array(
				'case'     => __( 'The following fields will be ignored:' ),
				'fields'   => $unnecessary_fields_str,
				'solution' => '',
			);

		}

		return $warning_message;
	}

	/**
	 * @param $block array Array of JSON properties to validate that cmsName and gigyaName exist and are not empty.
	 *
	 * @return string The error message will be sent to the user, and empty string in case there is no error.
	 */

	private static function getBlockValidationError( $block ) {
		if ( ( ! key_exists( 'gigyaName', $block ) ) and ( ! key_exists( 'cmsName', $block ) ) and ( count( $block ) === 0 ) ) {        //empty JSON
			return '';
		} elseif ( ( ! key_exists( 'gigyaName', $block ) ) || ( ! key_exists( 'cmsName', $block ) ) ) {        //Missing property
			return 'Error: gigyaName or cmsName does not exist in one of the blocks of the field mapping JSON.';
		} elseif ( key_exists( 'gigyaName', $block ) and empty( $block['gigyaName'] ) ) {                             // gigyaName is empty
			return 'Error: gigyaName is empty in the field mapping JSON, this property can\'t be empty. Please enter a value.';

		} elseif ( key_exists( 'cmsName', $block ) and empty( $block['cmsName'] ) ) {                                //cmsName is empty
			return 'Error: cmsName is empty in the field mapping JSON, this property can\'t be empty. Please enter a value.';
		} else {
			return '';
		}
	}

	/**
	 * @param $response array The response from 'accounts.getSchema' query.
	 * @param $key string Field to search for in Gigya schema, with dot notation.
	 *                        Example: subscriptions.mySubscription.isSubscribed
	 *
	 * @return bool True if the field has been found false otherwise.
	 */
	private static function doesFieldExist( $response, $key ) {

		if ( empty( $key ) ) {
			return false;
		}

		$field_name_in_array = explode( '.', $key );
		$schema_type         = $field_name_in_array[0];
		$field_name          = substr( strpbrk( $key, '.' ), 1 );


		switch ( $schema_type ) {
			case 'profile':
				$array_of_keys = $response['profileSchema'] ['fields'];
				break;
			case'data':
				$array_of_keys = $response['dataSchema']['fields'];
				break;
			case 'subscriptions':
				$array_of_keys = $response['subscriptionsSchema']['fields'];
				break;
			case 'preferences':
				$array_of_keys = $response['preferencesSchema']['fields'];
				break;

			//Should be the systemSchema (doesn't include specific name).
			default:
				$field_name    = $key;
				$array_of_keys = $response['systemSchema']['fields'];
				break;

		}

		return ( ( ! empty ( $field_name ) ) and array_key_exists( $field_name, $array_of_keys ) );
	}

	public static function generateMachineName( $desktop_screen_set_id, $serial ) {
		$machine_name = $desktop_screen_set_id;
		if ( $serial !== 0 ) {
			$machine_name .= '_' . $serial;
		}

		return $machine_name;
	}

	public static function fieldsMappingWarningsBuilder( $case_str, $fields, $solution, $does_have_several_warnings ) {


		$error = $case_str
				 . '<br>&nbsp;&nbsp;&nbsp;'
				 . '<i>' . $fields . '</i>'
				 . '<br>'
				 . $solution;

		if ( $does_have_several_warnings ) {
			$error = '<li>' . $error . '</li>';
		} else {
			$error = '<p class="gigya-field-mapping-error-p">' . $error . '</p>';
		}

		return $error;
	}

	public static function buildFieldMappingHTMLWarning( $message_data ) {

		$warning_message            = __( 'Settings saved.' ) . '<p>' . __( 'Warning:' ) . '</p>';
		$does_have_several_warnings = false;
		if ( count( $message_data ) > 1 ) {
			$does_have_several_warnings = true;
		};

		if ( $does_have_several_warnings ) {
			$warning_message = __( 'Settings saved.' ) . '<p>' . __( 'Warning:' ) . '</p>' . '<ol class="gigya-field-mapping-error-p">';
		}
		foreach ( $message_data as $type_of_error ) {
			$warning_message .= static::fieldsMappingWarningsBuilder( $type_of_error['case'], $type_of_error['fields'], $type_of_error['solution'], $does_have_several_warnings );
		}
		if ( $does_have_several_warnings ) {
			$warning_message .= '</ol>';
		}

		return $warning_message;
	}

	/**
	 * Set the POSTed secret key.
	 * If it's not submitted, take it from DB.
	 *
	 * @param string $field	The obfuscated field
	 *
	 * @return bool
	 */
	private static function _setObfuscatedField( $field ) {
		if ( empty( $_POST['gigya_global_settings'][$field] ) ) {
			$options = static::_getSiteOptions();
			if ( $options === false ) {
				return false;
			}

			$_POST['gigya_global_settings'][$field] = $options[$field];
		} else {
			$_POST['gigya_global_settings'][$field] = GigyaApiHelper::encrypt( $_POST['gigya_global_settings'][$field], SECURE_AUTH_KEY );
		}

		return true;
	}

	private static function setError( $errorCode, $errorMessage, $callId = null ) {
		$errorLink = "<a href='https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/416d41b170b21014bbc5a10ce4041860.html' target='_blank' rel='noopener noreferrer'>Response_Codes_and_Errors</a>";
		$message   = "SAP CDC API error: {$errorCode} - {$errorMessage}.";
		add_settings_error( 'gigya_global_settings', 'api_validate', __( $message . " For more information please refer to {$errorLink}", 'error' ) );
	}

	/**
	 * Set the posted api related values to the old (from DB) values
	 *
	 * @param string $option The option under which to keep the settings
	 * @param null|string|array $settings Tells the function which specific old values to get, if we don't want all of them.
	 */
	public static function _keepOldApiValues( $option = '', $settings = [] ) {
		if ( ! $option ) {
			$options                                           = self::_getSiteOptions();
			$_POST['gigya_global_settings']['api_key']         = $options['api_key'];
			$_POST['gigya_global_settings']['user_key']        = $options['user_key'];
			$_POST['gigya_global_settings']['auth_mode']       = $options['auth_mode'];
			$_POST['gigya_global_settings']['api_secret']      = $options['api_secret'];
			$_POST['gigya_global_settings']['rsa_private_key'] = $options['rsa_private_key'];
			$_POST['gigya_global_settings']['data_center']     = $options['data_center'];
			$_POST['gigya_global_settings']['other_ds']        = ( ! empty( $_POST['gigya_global_settings']['other_ds'] ) ) ? $options['other_ds'] : '';
			if ( isset( $options['sub_site_settings_saved'] ) ) {
				$_POST['gigya_global_settings']['sub_site_settings_saved'] = $options['sub_site_settings_saved'];
			}
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
