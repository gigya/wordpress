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

		// Parameters to be sent to the DOM.
		$params = array(
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'actionRaasLogin'   => 'gigya_raas_login',
				'raasOverrideLinks' => isset( $this->login_options['login_raas_override_links'] ) ? $this->login_options['login_raas_override_links'] : 1,
				'raasLoginDiv'      => isset( $this->login_options['login_raas_login_div'] ) ? $this->login_options['login_raas_login_div'] : '',
				'raasRegisterDiv'   => isset( $this->login_options['login_raas_register_div'] ) ? $this->login_options['login_raas_register_div'] : '',
				'raasProfileDiv'    => isset( $this->login_options['login_raas_profile_div'] ) ? $this->login_options['login_raas_profile_div'] : '',
		);

		// Load params to be available to client-side script.
		wp_localize_script( 'gigya_rass_js', 'gigyaRassParams', $params );

	}
}