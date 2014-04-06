<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function gmSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GM );
	$form   = array();

	$form['gamification_notification'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Notifications' ),
			'value' => _gigParam( $values['gamification_notification'], 0 )
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_GM );
}