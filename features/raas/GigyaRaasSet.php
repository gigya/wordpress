<?php

/**
 * @file
 * GigyaRaasSet.php
 * General RaaS settings.
 */
class GigyaRaasSet {

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
		wp_enqueue_script( 'gigya_raas_js', GIGYA__PLUGIN_URL . 'features/raas/gigya_raas.js' );
		wp_enqueue_style( 'gigya_raas_css', GIGYA__PLUGIN_URL . 'features/raas/gigya_raas.css' );

		$params = $this->getParams();

		// Load params to be available on client-side script.
		wp_localize_script( 'gigya_raas_js', 'gigyaRaasParams', $params );

	}

	/**
	 * Generate the parameters for the raas plugin.
	 * @return array
	 */
	public function getParams() {

		// Parameters to be sent to the DOM.
		$params = array(

			// Ajax action.
				'actionRaas'              => 'gigya_raas',
				'redirect'                => user_admin_url(),
				'canEditUsers'            => current_user_can( 'edit_users' ),

			// Screen set.
				'raasWebScreen'           => _gigParam( $this->login_options, 'raasWebScreen', 'Default-RegistrationLogin' ),
				'raasMobileScreen'        => _gigParam( $this->login_options, 'raasMobileScreen', 'DefaultMobile-RegistrationLogin' ),
				'raasLoginScreen'         => _gigParam( $this->login_options, 'raasLoginScreen', 'gigya-login-screen' ),
				'raasRegisterScreen'      => _gigParam( $this->login_options, 'raasRegisterScreen', 'gigya-register-screen' ),
				'raasProfileWebScreen'    => _gigParam( $this->login_options, 'raasProfileWebScreen', 'Default-ProfileUpdate' ),
				'raasProfileMobileScreen' => _gigParam( $this->login_options, 'raasProfileMobileScreen', 'DefaultMobile-ProfileUpdate' ),

			// Override links.
				'raasOverrideLinks'       => _gigParamDefaultOn( $this->login_options, 'raasOverrideLinks' ),

			// Embed DIVs.
				'raasLoginDiv'            => _gigParam( $this->login_options, 'raasLoginDiv', 'loginform' ),
				'raasRegisterDiv'         => _gigParam( $this->login_options, 'raasRegisterDiv', 'registerform' ),
				'raasProfileDiv'          => _gigParam( $this->login_options, 'raasProfileDiv', 'profile-page' )
		);

		// Let others plugins to modify the raas parameters.
		$params = apply_filters( 'gigya_raas_params', $params );

		return $params;
	}
}