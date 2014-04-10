<?php

/**
 * @file
 * GigyaGamificationSet.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaGamificationSet {

	public function __construct() {

		// Get settings variables.
		$this->gm_options = get_option( GIGYA__SETTINGS_GM );

		// Load custom Gigya gamification script.
		wp_enqueue_script( 'gigya_gamification_js', GIGYA__PLUGIN_URL . 'features/gamification/gigya_gamification.js' );
		wp_enqueue_style( 'gigya_gamification_css', GIGYA__PLUGIN_URL . 'features/gamification/gigya_gamification.css' );

		if ( ! empty( $this->gm_options['gamification_notification'] ) ) {
			$params = array();

			// Let others plugins to modify the comments parameters.
			$params = apply_filters( 'gigya_gamification_notification_params_alter', $params );

			// Load params to be available on client-side script.
			wp_localize_script( 'gigya_gamification_js', 'gigyaGmNotificationParams', $params );
		}

	}

	/**
	 * Generate the parameters for the gamification plugin.
	 * @return array
	 */
	public function getParams() {

		// The parameters array.
		$params = array(
				'period'     => _gigParam( $this->gm_options['gamification_period'], '7days' ),
				'totalCount' => _gigParam( $this->gm_options['gamification_count'], '12' ),
		);

		return $params;
	}

}