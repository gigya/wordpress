<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function gmConfigForm() {
	$values = get_option( GIGYA__SETTINGS_PREFIX );
	_gigya_formEl(
			array(
					'type'    => 'checkbox',
					'id'      => 'gamification_notification',
					'label'   => __( 'Enable Notifications' ),
					'value' => ! empty( $values['gamification_notification'] ) ? $values['gamification_notification'] : 0
			)
	);
}