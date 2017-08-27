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
		// Load custom Gigya share script.
		wp_enqueue_script( 'gigya_share_js', GIGYA__PLUGIN_URL . 'features/share/gigya_share.js' );
		wp_enqueue_style( 'gigya_share_css', GIGYA__PLUGIN_URL . 'features/share/gigya_share.css' );

	}

	/**
	 * Generate the parameters for the share-bar plugin.
	 * @return array
	 */
	public function getParams() {

		// The current post.
		global $post;

		// Set image path.
		$image_by = _gigParam( $this->share_options, 'image', '0' );
		if ( ! empty( $image_by ) ) {
			$img = _gigParam( $this->share_options, 'imageURL', get_bloginfo( 'wpurl' ) . '/' . WPINC . '/images/blank.gif' );
		} else {
			$img = $this->getImage( $post );
		}

		// The parameters array.
		$params = array(
//				'postId'       => get_the_ID(),
				'layout'       => _gigParam( $this->share_options, 'layout', 'horizontal' ),
				'showCounts'   => _gigParam( $this->share_options, 'showCounts', 'right' ),
				'shareButtons' => _gigParam( $this->share_options, 'shareButtons', 'share,facebook-like,google-plusone,twitter,email' ),
				'shortURLs'    => ! empty( $this->share_options['shortURLs'] ) ? 'always' : 'never',
				'ua'           => array(
						'linkBack'  => esc_url( apply_filters( 'the_permalink', get_permalink() ) ),
						'postTitle' => get_the_title(),
						'postDesc'  => _gigParam( $post, 'post_excerpt', '' ),
						'imageURL'  => $img
				),
		);

		// Add advanced parameters if exist.
		if ( ! empty( $this->share_options['advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( _gigParam( $this->share_options, 'advanced', '' ) );
			$params   = array_merge( $params, $advanced );
		}

		// Let others plugins to modify the share parameters.
		$params = apply_filters( 'gigya_share_params', $params );

		return $params;
	}

	/**
	 * Set the share-bar on top or under the content area.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function setDefaultPosition( $content ) {
		$position = $this->share_options['position'];
		if ( ! empty( $position ) && $position != 'none' ) {

			// Get the share widget content.
			$args = array(
					'before_widget' => '<div class="widget">',
					'after_widget'  => "</div>",
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '</h2>'
			);

			// Add param to instance.
			$instance = array(
					'params' => $this->getParams()
			);

			// Get the widget.
			$widget = GigyaShare_Widget::getContent( $args, $instance );

			// Set share widget position on post page.
			switch ( $position ) {
				case 'top':
					$content = $widget . $content;
					break;

				case 'bottom':
					$content = $content . $widget;
					break;

				case 'both':
					$content = $widget . $content . $widget;
					break;
			}
		}

		// The content with the share-bar.
		return $content;
	}

	/**
	 * Get the image path.
	 *
	 * @param $post
	 *
	 * @return string
	 */
	function getImage( $post ) {

		// Check if there post thumbnail.
		if ( has_post_thumbnail( $post->ID ) ) {
			return wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
		}

		// Check if there attachments.
		$attachments = get_posts( array(
						'order'          => 'ASC',
						'post_type'      => 'attachment',
						'post_parent'    => $post->ID,
						'post_mime_type' => 'image',
						'post_status'    => NULL
				)
		);

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				return wp_get_attachment_url( $attachment->ID, 'thumbnail', FALSE, FALSE );
			}
		}

		// Search for image in the code.
		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
		if ( ! empty( $matches[1][0] ) ) {
			return $matches[1][0];
		}

		// No image was found, use WP default blank image.
		return get_bloginfo( 'wpurl' ) . '/' . WPINC . '/images/blank.gif';
	}

}