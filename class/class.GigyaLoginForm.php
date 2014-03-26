<?php

/**
 * @file
 * class.GigyaLoginForm.php
 * Adds Gigya login option to WP login/register form.
 */
class GigyaLoginForm {

	public function __construct() {
	}

	/**
	 * Initialize Gigya login block at Wp login/register.
	 */
	function init() {

		// Gigya configuration values.
		$login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$global_options = get_option( GIGYA__SETTINGS_GLOBAL );

		// Check Gigya's social login is turn on and there an API key filled.
		if ( ! empty( $login_options['login_plugin'] ) && ! empty( $global_options['global_api_key'] ) ) {

			// Load custom Gigya login script.
			wp_enqueue_script( 'gigya_login_js', GIGYA__PLUGIN_URL . 'assets/scripts/gigya_login.js' );
			wp_enqueue_style( 'gigya_login_css', GIGYA__PLUGIN_URL . 'assets/styles/gigya_login.css' );

			// Parameters to be sent to the DOM.
			$params = array(
					'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					'action'   => 'gigya_login',
					'redirect' => ! empty ( $login_options['login_redirect'] ) ? $login_options['login_redirect'] : user_admin_url(),
			);

			$params['ui'] = array();
			$params['ui']['showTermsLink'] = false;
			
			if ( ! empty ( $login_options['login_width'] ) ) {
				$params['ui']['width'] = $login_options['login_width'];
			}
			if ( ! empty ( $login_options['login_height'] ) ) {
				$params['ui']['height'] = $login_options['login_height'];
			}
			if ( ! empty ( $login_options['login_term_link'] ) ) {
				$params['ui']['showTermsLink'] = $login_options['login_term_link'];
			}
			if ( ! empty ( $login_options['login_providers'] ) ) {
				$params['ui']['enabledProviders'] = $login_options['login_providers'];
			}
			if ( ! empty ( $login_options['login_ui'] ) ) {
				$arr = $this->advanced_values_parser( $login_options['login_ui']);
				if (! empty($arr)) {
					foreach ( $arr as $key => $val) {
						$params['ui'][$key] = $val;
					}
				}
			}

			// Load params to be available to client-side script.
			wp_localize_script( 'gigya_login_js', 'gigyaLoginParams', $params );
		}
	}

	/**
	 * Parser for the 'key=value | key=value' format.
	 * @param $str
	 *
	 * @return array|bool
	 */
	public static function advanced_values_parser( $str ) {
		$reg = preg_match_all( "/([^,= ]+)=([^,= ]+)/", $str, $r );
		if ( $reg ) {
			return array_combine( $r[1], $r[2] );
		}
		return false;
	}
}