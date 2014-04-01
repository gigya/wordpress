<?php

/**
 * @file
 * GigyaLoginForm.php
 * Adds Gigya login option to WP login/register form.
 */
class GigyaLoginForm {

	public function __construct() {

		// Gigya configuration values.
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );

	}

	/**
	 * Initialize Gigya login block at Wp login/register.
	 */
	function init() {

		// Load custom Gigya login script.
		wp_enqueue_script( 'gigya_login_js', GIGYA__PLUGIN_URL . 'features/login/gigya_login.js' );
		wp_enqueue_style( 'gigya_login_css', GIGYA__PLUGIN_URL . 'features/login/gigya_login.css' );

		// Parameters to be sent to the DOM.
		$params = array(
				'actionLogin'                 => 'gigya_login',
				'redirect'                    => getParam( $this->login_options['login_redirect'], user_admin_url() ),
				'connectWithoutLoginBehavior' => getParam( $this->login_options['login_connect_without'], 'loginExistingUser' )
		);

		$params['ui']                  = array();
		$params['ui']['showTermsLink'] = false;

		if ( ! empty ( $this->login_options['login_width'] ) ) {
			$params['ui']['width'] = $this->login_options['login_width'];
		}
		if ( ! empty ( $this->login_options['login_height'] ) ) {
			$params['ui']['height'] = $this->login_options['login_height'];
		}
		if ( ! empty ( $this->login_options['login_term_link'] ) ) {
			$params['ui']['showTermsLink'] = $this->login_options['login_term_link'];
		}
		if ( ! empty ( $this->login_options['login_providers'] ) ) {
			$params['ui']['enabledProviders'] = $this->login_options['login_providers'];
		}
		if ( ! empty ( $this->login_options['login_button_style'] ) ) {
			$params['ui']['buttonsStyle'] = $this->login_options['login_button_style'];
		}
		if ( ! empty ( $this->login_options['login_ui'] ) ) {
			$arr = gigyaCMS::advancedValuesParser( $this->login_options['login_ui'] );
			if ( ! empty( $arr ) ) {
				foreach ( $arr as $key => $val ) {
					$params['ui'][$key] = $val;
				}
			}
		}
		if ( ! empty ( $this->login_options['login_add_connection_custom'] ) ) {
			$arr = gigyaCMS::advancedValuesParser( $this->login_options['login_add_connection_custom'] );
			if ( ! empty( $arr ) ) {
				foreach ( $arr as $key => $val ) {
					$params['addConnection'][$key] = $val;
				}
			}
		}

		// Load params to be available to client-side script.
		wp_localize_script( 'gigya_login_js', 'gigyaLoginParams', $params );

	}
}