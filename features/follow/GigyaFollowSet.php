<?php

/**
 * @file
 * GigyaFollowSet.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaFollowSet {

	public function __construct() {

		// Get settings variables.
		$this->follow_options = get_option( GIGYA__SETTINGS_FOLLOW );

		// Load custom Gigya follow script.
		wp_enqueue_script( 'gigya_follow_js', GIGYA__PLUGIN_URL . 'features/follow/gigya_follow.js' );
		wp_enqueue_style( 'gigya_follow_css', GIGYA__PLUGIN_URL . 'features/follow/gigya_follow.css' );

	}

	/**
	 * Generate the parameters for the feed plugin.
	 * @return array
	 */
	public function getParams() {

		// The parameters array.
		$params = array(
				'on' => _gigParam( $this->follow_options, 'on', true ),
				'layout' => _gigParam( $this->follow_options, 'layout', 'horizontal' ),
				'iconSize'    => _gigParam( $this->follow_options, 'iconSize', '32' ),
				'buttons'   => _gigParam( $this->follow_options, 'followButtons', _gigya_get_json( 'admin/forms/json/default_follow') ),
		);
		if ( !empty($this->follow_options['advanced']) ) {
			$params = array_merge( $params, json_decode( $this->follow_options['advanced'], true ) );
		}

		// Let others plugins to modify the comments parameters.
		$params = apply_filters( 'gigya_follow_params', $params );

		return $params;
	}

}