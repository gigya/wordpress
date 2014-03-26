<?php
/**
 * Form builder for 'Share Settings' configuration page.
 */
function shareSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_SHARE );
	$form   = array();

	$share_opts = array(
			"none"   => __( "None" ),
			"bottom" => __( "Bottom" ),
			"top"    => __( "Top" ),
			"both"   => __( "Both" )
	);

	$form['share_plugin'] = array(
			'type'    => 'select',
			'options' => $share_opts,
			'label'   => __( 'Enable Gigya Share Button' ),
			'value'   => ! empty( $values['share_plugin'] ) ? $values['share_plugin'] : $share_opts['bottom']
	);

	$share_counts_opts = array(
			"right" => __( "Right" ),
			"top"   => __( "Top" ),
			"none"  => __( "None" )
	);

	$form['share_show_counts'] = array(
			'type'    => 'select',
			'options' => $share_counts_opts,
			'value'   => ! empty( $values['share_show_counts'] ) ? $values['share_show_counts'] : $share_counts_opts['right'],
			'label'   => __( 'Show Counts' )
	);

	$privacy_opts = array(
			"private" => __( "Private" ),
			"public"  => __( "Public" ),
			"friends" => __( "Friends" )
	);

	$form['share_privacy'] = array(
			'type'    => 'select',
			'options' => $privacy_opts,
			'value'   => ! empty( $values['share_privacy'] ) ? $values['share_privacy'] : $privacy_opts['private'],
			'label'   => __( 'Privacy' ),
	);

	$form['share_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Share Providers' ),
			'value' => ! empty( $values['share_providers'] ) ? $values['share_providers'] : '',
			'desc'  => __( 'for example: share,email,pinterest,twitter-tweet,google-plusone,facebook-like' )
	);

	$form['share_custom'] = array(
			'type'  => 'textarea',
			'label' => __( "Custom Code" ),
			'value' => ! empty( $values['share_custom'] ) ? $values['share_custom'] : ''
	);

	$form['share_advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => ! empty( $values['share_advanced'] ) ? $values['share_advanced'] : '',
			'desc'  => __( 'Enter values in' ) . '<strong>key|value</strong> ' . __( 'format' )
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_SHARE );
}