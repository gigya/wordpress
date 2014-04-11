<?php

/**
 * @file
 * GigyaCommentsSet.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaCommentsSet {

	public function __construct() {

		// Get settings variables.
		$this->comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
		$this->feed_options     = get_option( GIGYA__SETTINGS_FEED );

	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {

		// Load custom Gigya comments script.
		wp_enqueue_script( 'gigya_comments_js', GIGYA__PLUGIN_URL . 'features/comments/gigya_comments.js' );
		wp_enqueue_style( 'gigya_comments_css', GIGYA__PLUGIN_URL . 'features/comments/gigya_comments.css' );

		$params = $this->getParams();

		// Load params to be available on client-side script.
		wp_localize_script( 'gigya_comments_js', 'gigyaCommentsParams', $params );

	}

	/**
	 * Generate the parameters for the Comments plugin.
	 * @return array
	 */
	public function getParams() {
		$params = array(
				'categoryID' => _gigParam( $this->comments_options['categoryID'], '' ),
				'rating'     => _gigParam( $this->comments_options['rating'], 0 ),
				'streamID'   => get_the_ID(),
				'scope'      => _gigParam( $this->feed_options['scope'], 'external' ),
				'privacy'    => _gigParam( $this->feed_options['privacy'], 'private' ),
				'version'    => 2,
		);

		if ( ! empty( $this->comments_options['comments_advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( _gigParam( $this->comments_options['comments_advanced'], '' ) );
			$params   = array_merge( $params, $advanced );
		}

		// Let others plugins to modify the comments parameters.
		// For example:
		// $params['useSiteLogin'] = true;
		// $params['onSiteLoginClicked'] = 'onSiteLoginHandler';
		// To registering to the onSiteLoginClicked event.
		$params = apply_filters( 'gigya_comments_params', $params );

		return $params;
	}
}