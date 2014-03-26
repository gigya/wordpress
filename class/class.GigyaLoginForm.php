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

			// Add an HTML element to attach the Gigya Login UI to.
//			echo '<div id="gigya-login"></div>';

			// Load custom Gigya login script.
			wp_enqueue_script( 'gigya_login_js', GIGYA__PLUGIN_URL . 'assets/scripts/gigya_login.js' );
			wp_enqueue_style( 'gigya_login_css', GIGYA__PLUGIN_URL . 'assets/styles/gigya_login.css' );

			if ( ! empty ( $login_options['login_ui'] ) ) {
				$string = str_replace( ' ', '', $login_options['login_ui'] );
				$vars   = explode( '|', $string );
				foreach ( $vars as $var ) {

				}
			}
			// Parameters to be sent to the DOM.
			$params = array(
					'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					'action'   => 'gigya_login',
					'redirect' => ! empty ( $login_options['login_redirect'] ) ? $login_options['login_redirect'] : user_admin_url(),
			);

			if ( ! empty ( $login_options['login_width'] ) ) {
				$params['width'] = $login_options['login_width'];
			}
			if ( ! empty ( $login_options['login_height'] ) ) {
				$params['height'] = $login_options['login_term_link'];
			}
			if ( ! empty ( $login_options['login_term_link'] ) ) {
				$params['showTermsLink'] = $login_options['login_term_link'];
			}
			if ( ! empty ( $login_options['login_providers'] ) ) {
				$params['enabledProviders'] = $login_options['login_providers'];
			}
			if ( ! empty ( $login_options['login_ui'] ) ) {
				$params['loginUI'] = json_encode( $this->advanced_values_parser( $login_options['login_ui'] ) );
			}

			// Load params to be available to client-side script.
			wp_localize_script( 'gigya_login_js', 'gigyaLoginParams', $params );
		}
	}

	public static function advanced_values_parser( $str ) {
		$reg = preg_match_all( "/([^,= ]+)=([^,= ]+)/", $str, $r );
		if ( $reg ) {
			return array_combine( $r[1], $r[2] );
		}
		return false;
	}
}