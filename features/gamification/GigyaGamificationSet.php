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

		if ( ! empty( $this->gm_options['notification'] ) ) {
			$params = array();
			$advanced = $this->getAdvancedConfig( "notification");
			if ( !empty($advanced) ) {
				$params = array_merge($params, $advanced);
			}

			// Let others plugins to modify the gmNotification parameters.
			$params = apply_filters( 'gigya_gmNotification_params', $params );

			// Load params to be available on client-side script.
			wp_localize_script( 'gigya_gamification_js', 'gigyaGmNotificationParams', $params );
		}

	}

	/**
	 * Generate the parameters for the gamification plugin.
	 * @return array
	 */
	public function getParams( $type ) {

		// The parameters array.
		$params = array();
		if ( $type == "leaderboard") {
			$params = array(
				'period'     => _gigParam( $this->gm_options, 'period', '7days' ),
				'totalCount' => _gigParam( $this->gm_options, 'totalCount', '12' ),
			);
		}
		$advanced = $this->getAdvancedConfig($type);
		if ( !empty($advanced) ) {
			$params = array_merge($params, $advanced);
		}

		// Let others plugins to modify the gamification parameters.
		$params = apply_filters( 'gigya_gamification_params', $params );

		return $params;
	}

	protected function getAdvancedConfig($type) {
		switch ( $type ) {
			case "game":
				return json_decode($this->gm_options['advanced_user_status'], true);
			case "challenge":
				return json_decode($this->gm_options['advanced_challenge'], true);
			case "leaderboard":
				return json_decode($this->gm_options['advanced_leaderboard'], true);
			case "achievements":
				return json_decode($this->gm_options['advanced_achievements'], true);
			case "notification":
				return json_decode($this->gm_options['advanced_notification'], true);
			default:
				return array();
		}
	}

}