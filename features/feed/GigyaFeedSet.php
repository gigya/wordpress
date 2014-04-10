<?php

/**
 * @file
 * GigyaFeedSet.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaFeedSet {

	public function __construct() {

		// Get settings variables.
		$this->feed_options = get_option( GIGYA__SETTINGS_FEED );

		// Load custom Gigya feed script.
		wp_enqueue_script( 'gigya_feed_js', GIGYA__PLUGIN_URL . 'features/feed/gigya_feed.js' );
		wp_enqueue_style( 'gigya_feed_css', GIGYA__PLUGIN_URL . 'features/feed/gigya_feed.css' );

	}

	/**
	 * Generate the parameters for the feed plugin.
	 * @return array
	 */
	public function getParams() {

		// The parameters array.
		$params = array(
				'tabOrder' => _gigParam( $this->feed_options['feed_tabs'], 'everyone,friends,me' ),
				'width'    => _gigParam( $this->feed_options['feed_width'], '170' ),
				'height'   => _gigParam( $this->feed_options['feed_height'], '270' ),
				'siteName' => get_option( 'blogname', '' )
		);

		return $params;
	}

}