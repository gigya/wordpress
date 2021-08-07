<?php

/**
 * Install.
 */
class GigyaInstall {

	private $global_options;
	private $login_options;
	private $field_mapping_options;
	private $screenset_options;
	private $session_options;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->global_options        = get_option( GIGYA__SETTINGS_GLOBAL );
		$this->login_options         = get_option( GIGYA__SETTINGS_LOGIN ); /* User Management */
		$this->field_mapping_options = get_option( GIGYA__SETTINGS_FIELD_MAPPING );
		$this->screenset_options     = get_option( GIGYA__SETTINGS_SCREENSETS );
		$this->session_options       = get_option( GIGYA__SETTINGS_SESSION );
	}

	/**
	 * Initialize the installation process.
	 */
	public function init() {
		// The default behavior of WP is to load all options with autoload='yes'
		// on each request. This behavior can not be change in update_option() function.
		// So, on installation we initialize our records with the desired value of autoload.
		if ( empty ( $this->global_options ) ) {
			add_option( GIGYA__SETTINGS_GLOBAL, array(), '', 'yes' );
		}

		if ( empty ( $this->session_options ) ) {
			add_option( GIGYA__SETTINGS_SESSION, array(), '', 'yes' );
		}

		if ( empty ( $this->login_options ) ) {
			add_option( GIGYA__SETTINGS_LOGIN, array(), '', 'yes' );
		}

		if ( empty ( $this->screenset_options ) ) {
			add_option( GIGYA__SETTINGS_SCREENSETS, array(), '', 'yes' );
		}

		if ( empty ( $this->field_mapping_options ) ) {
			add_option( GIGYA__SETTINGS_FIELD_MAPPING, array(), '', 'yes' );
		}

		$this->upgrade();
	}

	/**
	 * Start the upgrade path workflow.
	 */
	public function upgrade() {

		// We mark the the DB with the current installed version.
		// For future DB updates & upgrades.
		$v = get_option( 'gigya_db_version' );
		if ( ! empty( $v ) && $v == 5.0 ) {
			// If the DB is already on 5.0, do nothing and quit.
			return;
		} else {
			add_option( 'gigya_db_version', 5.0, '', 'no' );
		}

		// Load v4.0 options
		$old = get_option( 'gigya_settings_fields' );
		if ( ! empty( $old ) ) {

			// Update new structure with
			// old values if exist.
			$this->upgradeGlobal( $old );
			$this->upgradeLogin( $old );
		}

		// Upgrade widgets.
		$this->upgradeWidgets();
	}

	/**
	 * Upgrade Global options.
	 *
	 * @param $old
	 */
	private function upgradeGlobal( $old ) {

		// Update old (v4.0) global options if exist.
		$this->setVar( $this->global_options, 'api_key', $old['api_key'] );
		$this->setVar( $this->global_options, 'api_secret', $old['secret_key'] );
		$this->setVar( $this->global_options, 'rsa_private_key', $old['rsa_private_key'] );
		$this->setVar( $this->global_options, 'data_center', $old['data_center'] );
		$this->setVar( $this->global_options, 'providers', $old['providers'] );
		$this->setVar( $this->global_options, 'lang', $old['lang'] );
		$this->setVar( $this->global_options, 'google_analytics', $old['google_analytics'] );
		$this->setVar( $this->global_options, 'log_level', ($old['gigya_debug']) ? 'debug' : 'info' );

		$this->setJson( $this->global_options, 'advanced', $old['global_params'] );

		update_option( GIGYA__SETTINGS_GLOBAL, $this->global_options );
	}

	/**
	 * upgrade Login options.
	 *
	 * @param $old
	 */
	private function upgradeLogin( $old ) {

		// Update old (v4.0) login options if exist.
		$this->login_options['mode'] = ! empty( $old['login_plugin'] ) ? 'wp_sl' : 'wp_only';
		$this->setVar( $this->login_options, 'buttonsStyle', $old['login_button_style'] );
		$this->setVar( $this->login_options, 'connectWithoutLoginBehavior', $old['connect_without'] );
		$this->setVar( $this->login_options, 'width', $old['login_width'] );
		$this->setVar( $this->login_options, 'height', $old['login_height'] );
		$this->setVar( $this->login_options, 'redirect', $old['post_login_redirect'] );
		$this->setVar( $this->login_options, 'enabledProviders', $old['login_providers'] );
		$this->setVar( $this->login_options, 'showTermsLink', $old['login_term_link'] );
		$this->setVar( $this->login_options, 'registerExtra', $old['show_reg'] );

		$this->setJson( $this->login_options, 'advancedAddConnectionsUI', $old['login_add_connection_custom'] );
		$this->setJson( $this->login_options, 'advancedLoginUI', $old['login_ui'] );

		update_option( GIGYA__SETTINGS_LOGIN, $this->login_options );
	}

	/**
	 * Upgrade enable widgets.
	 */
	private function upgradeWidgets() {
		// Creating new widgets based on the old ones.
		$this->upgradeWidget( 'widget_gigya', 'widget_gigya_login' );
		$this->upgradeWidget( 'widget_gigyagamification', 'widget_gigya_gamification' );

		// Updating the sidebars.
		$sb = get_option( 'sidebars_widgets' );
		foreach ( $sb as $k => $sidebar ) {
			if ( is_array( $sidebar ) and ( count( $sidebar ) > 0 ) )
			{
				foreach ( $sidebar as $widget )
				{
					$brk = explode( '-', $widget );
					if ( $brk[0] == 'gigya' )
					{
						$sb[$k][] = 'gigya_login-' . $brk[1];
					}
					elseif ( $brk[0] == 'gigyagamification' )
					{
						$sb[$k][] = 'gigya_gamification-' . $brk[1];
					}
				}
			}
		}

		update_option( 'sidebars_widgets', $sb );
	}

	/**
	 * Helper - Update from old variables.
	 *
	 * @param $options
	 * @param $new_name
	 * @param $old_value
	 */
	private function setVar( &$options, $new_name, $old_value ) {
		if ( ! empty( $old_value ) ) {
			$options[$new_name] = $old_value;
		}
	}

	/**
	 * Helper - Update from old advanced variables.
	 *
	 * @param $options
	 * @param $new_name
	 * @param $old_value
	 */
	private function setJson( &$options, $new_name, $old_value ) {
		if ( ! empty( $old_value ) ) {
			$old_arr            = $this->parseKeyValuePair( $old_value );
			$json               = json_encode( $old_arr );
			$options[$new_name] = ! empty( $json ) ? $json : '';
		}
	}

	/**
	 * Upgrade widgets.
	 *
	 * @param $old
	 * @param $new
	 */
	private function upgradeWidget( $old, $new ) {
		$old_widget = get_option( $old );
		if ( ! empty( $old_widget ) ) {
			if ( $old == 'widget_gigyafollowbar' ) {
				foreach ( $old_widget as $k => $inst ) {
					if ( ! empty( $inst['buttons'] ) ) {
						$old_widget[$k]['buttons'] = $this->toValidJson( $inst['buttons'] );
					}
				}
			}

			// Add new widget based on old one.
			add_option( $new, $old_widget );
		}
	}

	/**
	 * Parse Key Value Pair old style.
	 *
	 * @param $str
	 *
	 * @return array|bool
	 */
	function parseKeyValuePair( $str ) {
		$reg = preg_match_all( "/([^,= ]+)=([^,= ]+)/", $str, $r );
		if ( $reg ) {
			return array_combine( $r[1], $r[2] );
		}
		return false;
	}

	/**
	 * Try to convert invalid JSON.
	 *
	 * @param $json
	 *
	 * @return mixed
	 */
	function toValidJson( $json ) {
		$json = trim( $json );
		$json = str_replace( "'", '"', $json );
		$json = str_replace( array( "\n", "\r" ), "", $json );
		$json = preg_replace( '/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json );
		$json = preg_replace( '/(,)\s*}$/', '}', $json );
		return $json;
	}

	/**
	 * Clean the database from old (4.0) plugin records.
	 */
	public static function cleanDB() {
		// Delete old Settings.
		delete_option( 'gigya_settings_fields' );

		// Delete old Widgets.
		$old_widgets = array( 'widget_gigya', 'widget_gigyaactivityfeed', 'widget_gigyafollowbar', 'widget_gigyagamification' );
		foreach ( $old_widgets as $widget_name ) {
			delete_option( $widget_name );
		}

		// Delete old widgets from the sidebars.
		$sb = get_option( 'sidebars_widgets' );
		foreach ( $sb as $k => $sidebar ) {
			foreach ( $sidebar as $l => $widget ) {
				if (
						strpos( $widget, 'gigya-' ) === 0
						||
						strpos( $widget, 'gigyaactivityfeed-' ) === 0
						||
						strpos( $widget, 'gigyafollowbar-' ) === 0
						||
						strpos( $widget, 'gigyagamification-' ) === 0
				) {
					unset( $sb[$k][$l] );
				}
			}
		}

		// update the DB with the clean record.
		update_option( 'sidebars_widgets', $sb );

		// return AJAX success.
		wp_send_json_success( array( 'msg' => __( 'The database cleaned successfully' ) ) );
	}

	public function add_gigya_caps() {
		$role = get_role('administrator');
		$role->add_cap('edit_gigya');
		if ( !is_multisite() ) { // normal site administrators
			$role->add_cap('edit_gigya_secret');
		}
	}

}

