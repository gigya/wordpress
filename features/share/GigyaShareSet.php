<?php

/**
 * @file
 * GigyaShareSet.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaShareSet {

	public function __construct() {

		// Get settings variables.
		$this->share_options = get_option( GIGYA__SETTINGS_SHARE );

	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {

		// Load custom Gigya login script.
		wp_enqueue_script( 'gigya_share_js', GIGYA__PLUGIN_URL . 'features/share/gigya_share.js' );
		wp_enqueue_style( 'gigya_share_css', GIGYA__PLUGIN_URL . 'features/share/gigya_share.css' );

		$params = array(
				'containerID'  => 'gigya-share',
				'postId'       => get_the_ID(),
				'layout'       => getParam( $this->share_options['share_layout'], 'horizontal' ),
				'showCounts'   => getParam( $this->share_options['share_counts'], 'right' ),
				'shareButtons' => getParam( $this->share_options['share_providers'], 'share,facebook-like,google-plusone,twitter,email' ),
				'shortURLs'    => ! empty( $this->share_options['share_short_url'] ) ? 'always' : 'never',

				'ua'           => array(
						'linkBack'  => the_permalink(),
						'postTitle' => get_the_title(),
						'postDesc'  => the_excerpt(),
						'imageBy'   => getParam( $this->share_options['share_image'], 'default' ),
						'imageURL'  => getParam( $this->share_options['share_image_url'], '' )
				),
		);

		if ( ! empty( $this->share_options['share_advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( getParam( $this->share_options['share_advanced'], '' ) );
			$params   = array_merge( $params, $advanced );
		}

		// Load params to be available on client-side script.
		wp_localize_script( 'gigya_share_js', 'gigyaShareParams', $params );

	}
}