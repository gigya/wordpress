<?php

/**
 * Adds commentsWidget widget.
 */
class GigyaComments_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$args = array(
				'description' => __( 'Multiple providers Share buttons by Gigya' )
		);
		parent::__construct( 'GigyaShare_Widget', __( 'Gigya Share' ), $args );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $this->getContent( $args, $instance );
	}

	/**
	 * @param $args
	 * @param $instance
	 *
	 * @return string
	 */
	public function getContent( $args, $instance ) {
		$output = '';
		$title  = apply_filters( 'widget_title', $instance['title'] );

		$output .= $args['before_widget'];
		if ( ! empty( $title ) ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}
		$output .= '<div class="gigya-share-widget"></div>';
		$output .= $args['after_widget'];

		return $output;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string|void
	 */
//	public function form( $instance ) {
//		if ( isset( $instance['title'] ) ) {
//			$title = $instance['title'];
//		} else {
//			$title = __( 'New title' );
//		}
//
//		$output = '';
//		$output .= '<p>';
//		$output .= '<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Title:' ) . '</label>';
//		$output .= '<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $title ) . '">';
//		$output .= '</p>';
//
//		echo $output;
//	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
//	public function update( $new_instance, $old_instance ) {
//		$instance          = array();
//		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
//
//		return $instance;
//	}

}