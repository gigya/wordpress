<?php
/**
 * Form builder for 'RAAS Settings' configuration page.
 */
function raasConfigForm() {
	$values = get_option( GIGYA__SETTINGS_PREFIX );
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'raas_plugin',
					'label' => __( 'Enable Gigya RAAS' ),
					'value' => ! empty( $values['raas_plugin'] ) ? $values['raas_plugin'] : 0
			)
	);
}