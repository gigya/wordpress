<?php
/**
 * Form builder for 'RAAS Settings' configuration page.
 */
function raasSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_RAAS );
	$form   = array();

	$form['raas_plugin'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gigya RAAS' ),
			'value' => ! empty( $values['raas_plugin'] ) ? $values['raas_plugin'] : 0
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_RAAS );
}