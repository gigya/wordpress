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

		global $post;

		// Load custom Gigya share script.
		wp_enqueue_script( 'gigya_share_js', GIGYA__PLUGIN_URL . 'features/share/gigya_share.js' );
		wp_enqueue_style( 'gigya_share_css', GIGYA__PLUGIN_URL . 'features/share/gigya_share.css' );

		$params = array(
				'postId'       => get_the_ID(),
				'layout'       => _gigParam( $this->share_options['share_layout'], 'horizontal' ),
				'showCounts'   => _gigParam( $this->share_options['share_counts'], 'right' ),
				'shareButtons' => _gigParam( $this->share_options['share_providers'], 'share,facebook-like,google-plusone,twitter,email' ),
				'shortURLs'    => ! empty( $this->share_options['share_short_url'] ) ? 'always' : 'never',
				'ua'           => array(
						'linkBack'  => esc_url( apply_filters( 'the_permalink', get_permalink() ) ),
						'postTitle' => get_the_title(),
						'postDesc'  => _gigParam( $post->post_excerpt, '' ),
						'imageBy'   => _gigParam( $this->share_options['share_image'], 'default' ),
						'imageURL'  => _gigParam( $this->share_options['share_image_url'], '' )
				),
		);

		if ( ! empty( $this->share_options['share_advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( _gigParam( $this->share_options['share_advanced'], '' ) );
			$params   = array_merge( $params, $advanced );
		}

		// Load params to be available on client-side script.
		wp_localize_script( 'gigya_share_js', 'gigyaShareParams', $params );

	}

	public function setDefaultPosition( $content ) {
		$position = $this->share_options['share_position'];
		if ( ! empty( $position ) && $position != 'none' ) {

			// Get the share widget content.
//			$widget = the_widget( "GigyaShare_Widget" );
			$args = array(
					'before_widget' => '<div class="widget">',
					'after_widget'  => "</div>",
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '</h2>'
			);

			$widget = GigyaShare_Widget::getContent( $args, array() );

			// Set share widget position on post page.
			switch ( $position ) {
				case 'top':
					$content = $widget . $content;
					break;
				case 'bottom':
					$content = $content . $widget;
					break;
			}
		}

		return $content;
	}
}