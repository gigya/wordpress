<?php

/**
 * Adds ScreenSetWidget widget
 */
class GigyaScreenSet_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$args = array(
			'description' => __( 'Custom Gigya Screen-Set' )
		);
		parent::__construct( 'gigya_screenset', __( 'Gigya ScreenSet' ), $args );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		wp_register_script( $args['widget_id'], GIGYA__PLUGIN_URL . 'features/raas/gigya_custom_screenset.js' );

		echo '<div id="' . $instance['container_id'] . '">';

		if ( ! empty( $instance['type'] ) and $instance['type'] == 'popup' ) {
			if ( empty( $instance['link_id'] ) ) {
				$instance['link_id'] = 'gigya-screenset-popup-' . rand( 1000, 9999 );
			}

			echo '<a id="' . $instance['link_id'] . '" class="' . ( ! empty( $instance['link_class'] ) ? $instance['link_class'] : '' ) . '" href="#">' . $instance['title'] . '</a>';
		}

		echo '</div>';

		$custom_screen_sets = get_option( GIGYA__SETTINGS_SCREENSETS )['custom_screen_sets'];
		foreach ( $custom_screen_sets as $screen_set ) {
			if ( $screen_set['desktop'] == $instance['screenset_id'] ) {
				$instance['mobile_screenset_id'] = ( ! empty( $instance['screenset_id'] ) ) ? $screen_set['mobile'] : $screen_set['desktop'];
			}
		}

		wp_localize_script( $args['widget_id'], 'gigyaScreenSetParams', $instance );
		wp_enqueue_script( $args['widget_id'] );
	}

	public function form( $instance ) {
		$form = array();

		$form[ $this->get_field_id( 'title' ) ] = array(
			'type'     => 'text',
			'name'     => $this->get_field_name( 'title' ),
			'value'    => esc_attr( _gigParam( $instance, 'title', '' ) ),
			'label'    => __( 'Title' ),
			'class'    => 'size',
			'required' => true,
		);

		$custom_screen_sets = get_option( GIGYA__SETTINGS_SCREENSETS )['custom_screen_sets'];
		$desktop_screen_sets = array_column($custom_screen_sets, 'desktop');
		$form[ $this->get_field_id( 'screenset_id' ) ] = array(
			'type'    => 'select',
			'name'    => $this->get_field_name( 'screenset_id' ),
			'label'   => __( 'Screen-Set ID' ),
			'options' => array_combine($desktop_screen_sets, $desktop_screen_sets),
			'value'   => esc_attr( _gigParam( $instance, 'screenset_id', '' ) ),
		);

		$form[ $this->get_field_id( 'container_id' ) ] = array(
			'type'  => 'text',
			'name'  => $this->get_field_name( 'container_id' ),
			'value' => esc_attr( _gigParam( $instance, 'container_id', '' ) ),
			'label' => __( 'Container ID' ),
			'class' => 'size',
		);

		$form[ $this->get_field_id( 'type' ) ] = array(
			'type'    => 'select',
			'name'    => $this->get_field_name( 'type' ),
			'label'   => __( 'Type' ),
			'options' => array(
				'embed' => __( 'Embed' ),
				'popup' => __( 'Popup' ),
			),
			'value'   => esc_attr( _gigParam( $instance, 'type', '' ) ),
		);

		$form[$this->get_field_id( 'link_id' )] = array(
			'type'  => 'text',
			'name'  => $this->get_field_name( 'link_id' ),
			'value' => esc_attr( _gigParam( $instance, 'link_id', '' ) ),
			'label' => __( 'Link ID' ),
			'class' => 'size',
			'depends_on' => array( $this->get_field_name( 'type' ), 'popup' ),
		);

		$form[$this->get_field_id( 'link_class' )] = array(
			'type'  => 'text',
			'name'  => $this->get_field_name( 'link_class' ),
			'value' => esc_attr( _gigParam( $instance, 'link_class', '' ) ),
			'label' => __( 'Link CSS Class' ),
			'class' => 'size',
			'depends_on' => array( $this->get_field_name( 'type' ), 'popup' ),
		);

		echo _gigya_form_render( $form );
	}

	/**
	 * @param array $input_values
	 * @param array $db_values
	 *
	 * @return array
	 */
	public function update( $input_values, $db_values ) {
		$valid = true;

		$instance = array();
		if ( ! empty( $input_values ) and ! empty( $db_values ) ) {
			$instance = array_merge( $db_values, $input_values );
		} elseif ( ! empty( $input_values ) ) {
			$instance = $input_values;
		} elseif ( ! empty( $db_values ) ) /* If all values were reset it will just return to the previous state without further validation */ {
			return $db_values;
		}

		if ( empty( $instance['title'] ) or empty( $instance['screenset_id'] ) or empty( $instance['container_id'] ) ) {
			$valid = false;
		}

		if ( ! $valid ) {
			return ( empty( $db_values ) ) ? array() : $db_values;
		}

		return $instance;
	}
}