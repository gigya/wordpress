<?php

/**
 * Adds followWidget widget.
 */
class GigyaFollow_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$args = array(
				'description' => __( 'Multiple providers Follow Bar buttons by Gigya' )
		);
		parent::__construct( 'gigya_follow', __( 'Gigya Follow Bar' ), $args );
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

		// Load custom Gigya follow script.
		wp_enqueue_script( 'gigya_follow_js', GIGYA__PLUGIN_URL . 'features/follow/gigya_follow.js' );
		wp_enqueue_style( 'gigya_follow_css', GIGYA__PLUGIN_URL . 'features/follow/gigya_follow.css' );

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
		require_once GIGYA__PLUGIN_DIR . 'features/follow/GigyaFollowSet.php';
		$follow = new GigyaFollowSet();
		$data = $follow->getParams();

		if (!empty($data['on'])) {
			// Override params or take the defaults.
			if ( ! empty( $instance['override'] ) ) {
				foreach ( $instance as $key => $value ) {
					if ( ! empty( $value ) ) {
						if ( $key == 'buttons' ) {
							$data[ $key ] = $value;
						} else {
							$data[ $key ] = esc_attr( $value );
						}
					}
				}
			}

			// Set the output.
			$output .= $args['before_widget'];
			if ( ! empty( $title ) ) {
				$output .= $args['before_title'] . $title . $args['after_title'];
			}
			$output .= '<div class="gigya-follow-widget"></div>';
			$output .= '<script class="data-follow" type="application/json">' . json_encode( $data ) . '</script>';

			$output .= $args['after_widget'];

			return $output;
		}
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

		$form[$this->get_field_id( 'iconSize' )] = array(
				'type'  => 'text',
				'value' => esc_attr( _gigParam( $instance, 'iconSize', 32 ) ),
				'label' => __( 'Icon size' ),
				'class' => 'size',
				'name'  => $this->get_field_name( 'iconSize' )
		);

		$form[$this->get_field_id( 'layout' )] = array(
				'type'    => 'select',
				'options' => array(
						'horizontal' => __( 'Horizontal' ),
						'vertical'   => __( 'Vertical' )
				),
				'value'   => esc_attr( _gigParam( $instance, 'layout', 'horizontal' ) ),
				'label'   => __( 'Layout' ),
				'class'   => 'size',
				'name'    => $this->get_field_name( 'layout' )
		);

		$form[$this->get_field_id( 'followButtons' )] = array(
				'type'  => 'textarea',
				'value' => _gigParam( $instance, 'buttons', _gigya_get_json( 'admin/forms/json/default_follow' ) ),
				'label' => __( 'Follow Bar buttons' ),
				'desc'  => __( 'Please fill valid JSON for follow-bar button as describe' ) . ' ' . '<a href="http://developers.gigya.com/010_Developer_Guide/18_Plugins/050_Follow_Bar#Quick_Start_Implementation">' . __( 'here' ) . '</a>',
				'name'  => $this->get_field_name( 'buttons' ),
				'class' => 'json'
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