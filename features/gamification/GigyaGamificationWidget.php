<?php

/**
 * Adds GamificationWidget widget.
 */
class Gigya_Gamification_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$args = array(
				'description' => __( 'Gamification by Gigya' )
		);
		parent::__construct( 'Gigya_Gamification_Widget', __( 'Gigya Gamification' ), $args );
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
		require_once GIGYA__PLUGIN_DIR . 'features/gamification/GigyaGamificationSet.php';
		$gm            = new GigyaGamificationSet();
		$data          = $gm->getParams();
		$data['type']  = esc_attr( $instance['gamification_type'] );
		$data['width'] = esc_attr( $instance['gamification_width'] );

		// Set the output.
		$output .= $args['before_widget'];
		if ( ! empty( $title ) ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}
		$output .= '<div class="gigya-gamification-widget"></div>';
		$output .= '<script class="data-gamification" type="application/json">' . json_encode( $data ) . '</script>';

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

		$type_name  = 'gamification_type';
		$width_name = 'gamification_width';

		$form = array();

		$gm_opts = array(
				'game'         => 'Game status',
				'challenge'    => 'Challenge Status',
				'leaderboard'  => 'Leaderboard',
				'achievements' => 'Achievements'
		);

		$form[$this->get_field_id( $type_name )] = array(
				'type'    => 'select',
				'options' => $gm_opts,
				'value'   => _gigParam( esc_attr( $instance[$type_name] ), 'game' ),
				'label'   => __( 'Type' ),
				'name'    => $this->get_field_name( $type_name )
		);

		$form[$this->get_field_id( $width_name )] = array(
				'type'  => 'text',
				'value' => _gigParam( esc_attr( $instance[$width_name] ), '200' ),
				'label' => __( 'Width' ),
				'class' => 'size',
				'name'  => $this->get_field_name( $width_name )
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
		return $new_instance;
	}

}