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
				'description' => __( 'Gigya\'s Comments' )
		);
		parent::__construct( 'gigya_comments', __( 'Gigya Comments' ), $args );
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

		// Get the data from the argument.
		require_once GIGYA__PLUGIN_DIR . 'features/comments/GigyaCommentsSet.php';
		$comments = new GigyaCommentsSet();
		$data     = $comments->getParams();

		// Override params or take the defaults.
		if ( ! empty( $instance['override'] ) ) {
			foreach ( $instance as $key => $value ) {
				if ( ! empty( $value ) ) {
					$data[$key] = esc_attr( $value );
				}
			}
		}

		$output .= $args['before_widget'];
		if ( ! empty( $title ) ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}

		$output .= _gigya_render_tpl( 'admin/tpl/comments.tpl.php', array( 'data' => $data ) );

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
				'value' => esc_attr( _gigParam( $instance, 'title', '' ) ),
				'label' => __( 'Title' ),
				'class' => 'size',
				'name'  => $this->get_field_name( 'title' )
		);

		$form[$this->get_field_id( 'override' )] = array(
				'type'  => 'checkbox',
				'value' => esc_attr( _gigParam( $instance, 'override', '' ) ),
				'label' => __( 'Override' ),
				'class' => 'gigya-widget-override',
				'name'  => $this->get_field_name( 'override' )
		);

		$form[$this->get_field_id( 'rating' )] = array(
				'type'  => 'checkbox',
				'label' => __( 'Rating Mode' ),
				'value' => esc_attr( _gigParam( $instance, 'rating', 0 ) ),
				'name'  => $this->get_field_name( 'rating' )
		);

		$form[$this->get_field_id( 'categoryID' )] = array(
				'type'  => 'text',
				'label' => __( 'Category ID' ),
				'value' => esc_attr( _gigParam( $instance, 'categoryID', '' ) ),
				'desc'  => __( "The category ID on 'Comments category name' at Gigya's settings" ) . ' ' . '<a href=https://platform.gigya.com/Site/partners/Settings.aspx#cmd=Settings.CommentsSetup>' . __( 'here' ) . '</a>',
				'class' => 'size',
				'name'  => $this->get_field_name( 'categoryID' )

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