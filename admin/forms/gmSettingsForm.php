<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function gmSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GM );
	$form   = array();

	$form['gamification_notification'] = array(
			'type'  => 'checkbox',
			'id'    => 'gamification_notification',
			'label' => __( 'Enable Notifications' ),
			'value' => ! empty( $values['gamification_notification'] ) ? $values['gamification_notification'] : 0
	);

	GigyaSettings::_gigya_form_render( $form );
}