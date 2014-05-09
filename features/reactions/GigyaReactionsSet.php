<?php

/**
 * @file
 * GigyaReactionsSet.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaReactionsSet {

	public function __construct() {

		// Get settings variables.
		$this->reactions_options = get_option( GIGYA__SETTINGS_REACTIONS );
		$this->feed_options      = get_option( GIGYA__SETTINGS_FEED );

		// Load custom Gigya reactions script.
		wp_enqueue_script( 'gigya_reactions_js', GIGYA__PLUGIN_URL . 'features/reactions/gigya_reactions.js' );
		wp_enqueue_style( 'gigya_reactions_css', GIGYA__PLUGIN_URL . 'features/reactions/gigya_reactions.css' );

	}

	/**
	 * Generate the parameters for the reactions-bar plugin.
	 * @return array
	 */
	public function getParams() {

		// The current post.
		global $post;

		// Set image path.
		$image_by = _gigParam( $this->reactions_options, 'image', '0' );
		if ( ! empty( $image_by ) ) {
			$img = _gigParam( $this->reactions_options, 'imageURL', get_bloginfo( 'wpurl' ) . '/' . WPINC . '/images/blank.gif' );
		} else {
			$img = $this->getImage( $post );
		}

		// Unique bar ID.
		$bar_id = 'bar-' . get_the_ID();

		// The parameters array.
		$params = array(
				'barID'             => $bar_id,
				'layout'            => _gigParam( $this->reactions_options, 'layout', 'horizontal' ),
				'showCounts'        => _gigParam( $this->reactions_options, 'showCounts', 'right' ),
				'countType'         => _gigParam( $this->reactions_options, 'countType', 'right' ),
				'enabledProviders'  => _gigParam( $this->reactions_options, 'enabledProviders', 'reactions,facebook-like,google-plusone,twitter,email' ),
				'multipleReactions' => _gigParam( $this->reactions_options, 'multipleReactions', 0 ),
				'scope'             => _gigParam( $this->feed_options, 'scope', 'external' ),
				'privacy'           => _gigParam( $this->feed_options, 'privacy', 'private' ),
				'ua'                => array(
						'linkBack'  => esc_url( apply_filters( 'the_permalink', get_permalink() ) ),
						'postTitle' => get_the_title(),
						'postDesc'  => _gigParam( $post, 'post_excerpt', '' ),
						'imageURL'  => $img
				),
		);

		// Add reactions buttons parameters if exist.
		if ( ! empty( $this->reactions_options['buttons'] ) ) {
			$buttons             = gigyaCMS::parseJSON( _gigParam( $this->reactions_options, 'buttons', _gigya_get_json( 'admin/forms/json/default_reaction' ) ) );
			$params['reactions'] = $buttons;
		}

		// Add advanced parameters if exist.
		if ( ! empty( $this->reactions_options['advanced'] ) ) {
			$advanced = gigyaCMS::parseJSON( _gigParam( $this->reactions_options, 'advanced', '' ) );
			$params   = array_merge( $params, $advanced );
		}

		// Let others plugins to modify the reactions parameters.
		$params = apply_filters( 'gigya_reactions_params', $params );

		return $params;
	}

	/**
	 * Set the reactions-bar on top or under the content area.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function setDefaultPosition( $content ) {
		$position = $this->reactions_options['position'];
		if ( ! empty( $position ) && $position != 'none' ) {

			// Get the reactions widget content.
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
			$widget = GigyaReactions_Widget::getContent( $args, $instance );

			// Set reactions widget position on post page.
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

		// The content with the reactions-bar.
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