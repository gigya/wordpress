<?php
function raasConfigForm() {
	_gigya_formEl(
			array(
					'type'    => 'checkbox',
					'id'    => 'raas_plugin',
					'label' => __( 'Enable Gigya RAAS' )
			)
	);
}