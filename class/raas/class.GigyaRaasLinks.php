<?php

/**
 * @file
 * class.GigyaRaasLinks.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaRaasLinks {

	public function __construct() {

		// Get settings variables.
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );

	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {

		// Load custom Gigya login script.
		wp_enqueue_script( 'gigya_raas_js', GIGYA__PLUGIN_URL . 'assets/scripts/gigya_raas.js' );
		wp_enqueue_style( 'gigya_raas_css', GIGYA__PLUGIN_URL . 'assets/styles/gigya_raas.css' );

		// Parameters to be sent to the DOM.
		$params = array(

			// Ajax action.
				'actionRaas'              => 'gigya_raas',

			// Screen set.
				'raasWebScreen'           => isset( $this->login_options['raas_web_screen'] ) ? $this->login_options['raas_web_screen'] : 'Login-web',
				'raasMobileScreen'        => isset( $this->login_options['raas_mobile_screen'] ) ? $this->login_options['raas_mobile_screen'] : 'Mobile-login',
				'raasLoginScreen'         => isset( $this->login_options['raas_login_screen'] ) ? $this->login_options['raas_login_screen'] : 'gigya-login-screen',
				'raasRegisterScreen'      => isset( $this->login_options['raas_register_screen'] ) ? $this->login_options['raas_register_screen'] : 'gigya-register-screen',
				'raasProfileWebScreen'    => isset( $this->login_options['raas_profile_web_screen'] ) ? $this->login_options['raas_profile_web_screen'] : 'Profile-web',
				'raasProfileMobileScreen' => isset( $this->login_options['raas_profile_mobile_screen'] ) ? $this->login_options['raas_profile_mobile_screen'] : 'Profile-mobile',

			// Override links.
				'raasOverrideLinks'       => isset( $this->login_options['raas_override_links'] ) ? $this->login_options['raas_override_links'] : 1,

			// Embed DIVs.
				'raasLoginDiv'            => isset( $this->login_options['raas_login_div'] ) ? $this->login_options['raas_login_div'] : 'loginform',
				'raasRegisterDiv'         => isset( $this->login_options['raas_register_div'] ) ? $this->login_options['raas_register_div'] : 'registerform',
				'raasProfileDiv'          => isset( $this->login_options['raas_profile_div'] ) ? $this->login_options['raas_profile_div'] : 'profile-page',
		);

		// Load params to be available on client-side script.
		wp_localize_script( 'gigya_raas_js', 'gigyaRaasParams', $params );

	}
}