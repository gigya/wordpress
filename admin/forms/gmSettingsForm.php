<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function gmSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GM );
	$form   = array();

	$form['gamification_plugin'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gamification plugin' ),
			'value' => _gigParam( $values['gamification_plugin'], 0 )
	);

	$form['gamification_notification'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Notifications' ),
			'value' => _gigParam( $values['gamification_notification'], 0 )
	);

	$gm_leadebloard_opts = array(
			"7days" => __( "7 Days" ),
			"all"   => __( "All" )
	);

	$form['gamification_period'] = array(
			'type'    => 'select',
			'options' => $gm_leadebloard_opts,
			'value'   => _gigParam( $values['gamification_plugin'], '7days' ),
			'label'   => __( 'Leaderboard time period' ),
	);

	$form['gamification_count'] = array(
			'type'  => 'text',
			'value' => _gigParam( $values['gamification_plugin'], '12' ),
			'label' => __( 'Leaderboard user count' ),
			'desc'  => __( 'Valid values are between 1 to 23' )
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_GM );
}