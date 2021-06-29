<?php

/**
 * Adds ScreenSetWidget widget
 */

use  Gigya\WordPress\GigyaLogger;

class GigyaScreenSet_Widget extends WP_Widget {

	protected $logger;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$args = array(
			'description' => __( 'SAP Customer Data Cloud Screen-Set' )
		);
		parent::__construct( 'gigya_screenset', __( 'SAP CDC ScreenSet' ), $args );
		$this->logger = new GigyaLogger();

	}

	protected function setWidgetMachineName( $widget_id ) {
		$pattern = '/[^a-zA-Z0-9]/';

		return trim( preg_replace( $pattern, '', (string) $widget_id ) );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget( $args, $instance ) {
		if ( ! empty( $instance['container_id'] ) and ! empty( $instance['title'] ) ) {
			wp_register_script( $args['widget_id'], GIGYA__PLUGIN_URL . 'features/raas/gigya_custom_screenset.js' );

			$widget_machine_name = $this->setWidgetMachineName( $args['widget_id'] );

			echo '<div id="' . $instance['container_id'] . '" class="gigya-screenset-widget-outer-div" data-machine-name="' . $widget_machine_name . '">';

			if ( ! empty( $instance['type'] ) and $instance['type'] == 'popup' ) {
				if ( empty( $instance['link_id'] ) ) {
					$instance['link_id'] = 'gigya-screenset-popup-' . rand( 1000, 9999 );
				}

				echo '<a id="' . $instance['link_id'] . '" class="' . ( ! empty( $instance['link_class'] ) ? $instance['link_class'] : '' ) . '" href="#">' . $instance['title'] . '</a>';
			}

			echo '</div>';

			$screen_set_settings = get_option( GIGYA__SETTINGS_SCREENSETS );
			$custom_screen_sets  = array();
			if ( array_key_exists( 'custom_screen_sets', $screen_set_settings ) ) {
				$custom_screen_sets = $screen_set_settings['custom_screen_sets'];
			}
			if ( empty( $custom_screen_sets ) ) {
				$this->logger->debug( 'Screen-set widget error: The custom screen-set table is empty, please add the custom screen-set used in widget ' . $args['widget_id'] . ' to the "Screen-Sets" settings page. ' );
			}

			foreach ( $custom_screen_sets as $screen_set ) {
				if ( ( ! empty( $screen_set['id'] ) ) && ( $screen_set['id'] == $instance['screenset_id'] ) ) {
					$instance['screenset_id']        = $screen_set['desktop'];
					$instance['mobile_screenset_id'] = ( $instance['screenset_id'] !== 'desktop' ) ? $screen_set['mobile'] : $screen_set['desktop'];
					$instance['is_sync_data']        = ( ! empty( $screen_set['is_sync'] ) );
				} else if ( $screen_set['desktop'] == $instance['screenset_id'] ) {
					$instance['mobile_screenset_id'] = ( ! empty( $instance['screenset_id'] ) ) ? $screen_set['mobile'] : $screen_set['desktop'];
					$instance['is_sync_data']        = ( ! empty( $screen_set['is_sync'] ) );
				}
			};

			wp_localize_script( $args['widget_id'], '_gig_' . $widget_machine_name, $instance );
			wp_enqueue_script( $args['widget_id'] );
		}
	}

	public function form( $instance ) {
		$form                          = array();
		$select_attrs                  = array();
		$select_error                  = array();
		$screen_sets_list              = array();
		$selected_screen_set_id        = esc_attr( _gigParam( $instance, 'screenset_id', '' ) );
		$screen_set_settings           = get_option( GIGYA__SETTINGS_SCREENSETS );
		$select_attrs['data-required'] = 'empty-selection';
		if ( array_key_exists( 'custom_screen_sets', $screen_set_settings ) ) {
			$custom_screen_sets = $screen_set_settings['custom_screen_sets'];
		} else {
			$custom_screen_sets = '';
		}
		if ( ! empty( $custom_screen_sets ) ) {
			foreach ( $custom_screen_sets as $screen_set ) {
				if ( empty( $screen_set['desktop'] ) ) {
					continue;
				}
				if ( empty( $screen_set['id'] ) ) {
					$screen_sets_list[ $screen_set['desktop'] ] = $screen_set['desktop'];
				} else {
					$screen_sets_list[ $screen_set['id'] ] = $screen_set['desktop'];
				}
			}
			if ( empty( $selected_screen_set_id ) ) {
				array_unshift( $screen_sets_list, array(
					'value' => '',
					'attrs' => array(
						'disabled' => 'disabled',
						'style'    => 'display: none;',
					)
				) );
			} else if ( ! array_key_exists( $selected_screen_set_id, $screen_sets_list ) ) {
				$form_error                  = array();
				$form_error['error_message'] = $selected_screen_set_id . __( 'Screen-Set found in the widgets below has been removed by your administrator, and might not work on your website. Please check your configuration or contact your administrator.' );
				$form_error['attrs']         = array(
					'id'    => $selected_screen_set_id . '_error_message',
					'class' => 'gigya-error-message-notice-div notice notice-error is-dismissible'
				);

				$select_error['error_message'] = __( 'Screen-Set removed by administrator.' );
				$select_error['attrs']         = array( 'class' => 'gigya-error-message-notice-div' );

				$select_attrs['class'] = 'gigya-wp-field-error';
				array_unshift( $screen_sets_list, array(
						'value' => $selected_screen_set_id,
						'attrs' => array( 'class' => 'invalid-gigya-screen-Set-option', 'selected' => 'true' )
					)
				);
				$this->logger->error( $form_error['error_message'] );
				echo _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $form_error );
			}
		} else {
			$select_error['error_message'] = __( 'Custom Screen-Set not defined.' );
			$select_error['attrs']         = array( 'class' => 'gigya-error-message-notice-div' );

			$select_attrs['class'] = 'gigya-wp-field-error';
		}

		$form[ $this->get_field_id( 'title' ) ]        = array(
			'type'     => 'text',
			'name'     => $this->get_field_name( 'title' ),
			'value'    => ( empty( esc_attr( _gigParam( $instance, 'title', '' ) ) ) ) ? '' : esc_attr( _gigParam( $instance, 'title', '' ) ),
			'label'    => __( 'Title' ),
			'class'    => 'size',
			'required' => true,
		);
		$form[ $this->get_field_id( 'screenset_id' ) ] = array(
			'type'     => 'select',
			'name'     => $this->get_field_name( 'screenset_id' ),
			'label'    => __( 'Screen-Set ID' ),
			'options'  => $screen_sets_list,
			'value'    => esc_attr( _gigParam( $instance, 'screenset_id', '' ) ),
			'required' => 'empty-selection',
			'class'    => 'size',
			'attrs'    => $select_attrs,
			'markup'   => ( ( empty( $select_error ) ) ? '' : _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $select_error ) ),
		);
		$form[ $this->get_field_id( 'container_id' ) ] = array(
			'type'     => 'text',
			'name'     => $this->get_field_name( 'container_id' ),
			'value'    => ( empty( esc_attr( _gigParam( $instance, 'container_id', '' ) ) ) ) ? '' : esc_attr( _gigParam( $instance, 'container_id', '' ) ),
			'label'    => __( 'Container ID' ),
			'required' => true,
			'class'    => 'size',
		);
		$form[ $this->get_field_id( 'type' ) ]         = array(
			'type'     => 'select',
			'name'     => $this->get_field_name( 'type' ),
			'label'    => __( 'Type' ),
			'options'  => array(
				'embed' => __( 'Embed' ),
				'popup' => __( 'Popup' ),
			),
			'class'    => 'size',
			'value'    => empty( esc_attr( _gigParam( $instance, 'type', '' ) ) ) ? '0' : esc_attr( _gigParam( $instance, 'type', '' ) ),
			'required' => true,
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
		$this->logger->info( '"Custom Screen-Sets" widget was saved successfully.' );

		return $instance;
	}
}