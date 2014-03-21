<?php
/**
 * Form builder for 'RAAS Settings' configuration page.
 */
function raasSettingsForm() {
	$values = get_option( $_GET['page'] . '-settings' );
	$form = array();

	$form['raas_plugin'] = array(
			'type'  => 'checkbox',
			'id'    => 'raas_plugin',
			'label' => __( 'Enable Gigya RAAS' ),
			'value' => ! empty( $values['raas_plugin'] ) ? $values['raas_plugin'] : 0
	);

	GigyaSettings::_gigya_form_render($form);
}