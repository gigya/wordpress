<?php

/**
 * Adds LoginWidget widget
 */
class GigyaRaas_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$args = array(
				'description' => __( 'RaaS Login by SAP Customer Data Cloud' )
		);
		parent::__construct( 'gigya_raas', __( 'SAP CDC RaaS' ), $args );
	}

	/**
	 * Front-end display of widget
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

		// Set the output.
		$output .= $args['before_widget'];
		if ( ! empty( $title ) ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}
		if ( ! is_user_logged_in() ) {
			$output .= '<div class="gigya-raas-widget">';
			$output .= '<a href="wp-login.php">' . __('Login') . '</a> | ';
			$output .= '<a href="wp-login.php?action=register">' . __('Register') . '</a>';
			$output .= '</div>';
		} else {
			$current_user = wp_get_current_user();
			$output .= '<div class="gigya-wp-account-widget">';
			$output .= '<a class="gigya-wp-avatar" href="' . user_admin_url( 'profile.php' ) . '">' . get_avatar( $current_user->ID ) . '</a>';
			$output .= '<div class="gigya-wp-info">';
			$output .= '<a class="gigya-wp-name" href="' . user_admin_url( 'profile.php' ) . '">' . $current_user->display_name . '</a>';
			$output .= '<a class="gigya-wp-logout" href="' . wp_logout_url() . '">' . __( 'Log Out' ) . '</a>';
			$output .= '</div></div>';
		}

		$output .= $args['after_widget'];

		return $output;
	}

	/**
	 * Back-end widget form
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		$form = array();

		$form[ $this->get_field_id( 'title' ) ] = array(
			'type'  => 'text',
			'value' => esc_attr( _gigParam( $instance, 'title', '' ) ),
			'label' => __( 'Title' ),
			'class' => 'size',
			'name'  => $this->get_field_name( 'title' )
		);

		$form[ $this->get_field_id( 'override' ) ] = array(
			'type'  => 'checkbox',
			'value' => esc_attr( _gigParam( $instance, 'override', '' ) ),
			'label' => __( 'Override' ),
			'class' => 'gigya-widget-override',
			'name'  => $this->get_field_name( 'override' )
		);

		$form[ $this->get_field_id( 'width' ) ] = array(
			'type'  => 'text',
			'value' => esc_attr( _gigParam( $instance, 'width', '' ) ),
			'label' => __( 'Width' ),
			'class' => 'size',
			'name'  => $this->get_field_name( 'width' )
		);

		$form[ $this->get_field_id( 'height' ) ] = array(
			'type'  => 'text',
			'value' => esc_attr( _gigParam( $instance, 'height', '' ) ),
			'label' => __( 'Height' ),
			'class' => 'size',
			'name'  => $this->get_field_name( 'height' )
		);

		echo _gigya_form_render( $form );
	}

	/**
	 * Sanitize widget form values as they are saved
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