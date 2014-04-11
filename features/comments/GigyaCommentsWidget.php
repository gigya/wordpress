<?php

/**
 * Adds commentsWidget widget.
 */
class Gigya_Comments_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$args = array(
				'description' => __( 'Gigya\'s Comments' )
		);
		parent::__construct( 'Gigya_Comments_Widget', __( 'Gigya Comments' ), $args );
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
		$comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
		if ( ! empty( $comments_options['rating'] ) ) {
			$output .= '<div class="gigya-rating-widget"></div>';
		}
		$output .= '<div class="gigya-comments-widget"></div>';
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
	public function form( $instance ) {

		$form = array();

		$form[$this->get_field_id( 'title' )] = array(
				'type'  => 'text',
				'value' => _gigParam( esc_attr( $instance['title'] ), '' ),
				'label' => __( 'Title' ),
				'class' => 'size',
				'name'  => $this->get_field_name( 'title' )
		);

		echo _gigya_form_render( $form );
	}

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
	public function update( $new_instance, $old_instance ) {
		$new_instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $new_instance;
	}

}