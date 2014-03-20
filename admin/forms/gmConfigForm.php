<?php
function gmConfigForm() {
	_gigya_formEl(
			array(
					'type'    => 'checkbox',
					'id'      => 'gamification_notification',
					'label'   => __( 'Enable Notifications' ),
					'default' => 1
			)
	);
}