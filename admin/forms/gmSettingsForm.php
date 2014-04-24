<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function gmSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GM );
	$form   = array();

	$form['on'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gamification Plugins' ),
			'value' => $values['on'] === '0' ? '0' : '1'
	);

	$form['notification'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Notifications' ),
			'value' => _gigParam( $values['notification'], 0 )
	);

	$form['period'] = array(
			'type'    => 'select',
			'options' => array(
					"7days" => __( "7 Days" ),
					"all"   => __( "All" )
			),
			'value'   => _gigParam( $values['period'], '7days' ),
			'label'   => __( 'Leaderboard time period' ),
	);

	$form['totalCount'] = array(
			'type'  => 'text',
			'value' => _gigParam( $values['totalCount'], '12' ),
			'label' => __( 'Leaderboard user count' ),
			'desc'  => __( 'Valid values are between 1 to 23' )
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_GM );
}