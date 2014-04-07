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

	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {

		// Load custom Gigya comments script.
		wp_enqueue_script( 'gigya_comments_js', GIGYA__PLUGIN_URL . 'features/comments/gigya_comments.js' );
		wp_enqueue_style( 'gigya_comments_css', GIGYA__PLUGIN_URL . 'features/comments/gigya_comments.css' );

//		global $post;
//		$post_id = $post->ID;
		$params  = array(
				'categoryID' => _gigParam( $this->comments_options['comments_cat_id'], '' ),
				'streamID'   => get_the_ID(),
				'scope'      => _gigParam( $this->comments_options['comments_enable_share_activity'], 'both' ),
				'privacy'    => _gigParam( $this->feed_options['feed_privacy'], 'private' ),
				'version'    => 2
		);

		if ( ! empty( $this->comments_options['comments_advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( _gigParam( $this->comments_options['comments_advanced'], '' ) );
			$params   = array_merge( $params, $advanced );
		}

		// Load params to be available on client-side script.
		wp_localize_script( 'gigya_comments_js', 'gigyaCommentsParams', $params );

	}
}