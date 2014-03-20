<?php
function shareConfigForm() {
	$values     = get_option( GIGYA__SETTINGS_PREFIX );
	$share_opts = array(
			"none"   => __( "None" ),
			"bottom" => __( "Bottom" ),
			"top"    => __( "Top" ),
			"both"   => __( "Both" )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'share_plugin',
					'options' => $share_opts,
					'label'   => __( 'Enable Gigya Share Button' ),
					'value'   => ! empty( $values['share_plugin'] ) ? $values['share_plugin'] : $share_opts['bottom']
			)
	);
	$share_counts_opts = array(
			"right" => __( "Right" ),
			"top"   => __( "Top" ),
			"none"  => __( "None" )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'share_show_counts',
					'options' => $share_counts_opts,
					'value'   => ! empty( $values['share_show_counts'] ) ? $values['share_show_counts'] : $share_counts_opts['right'],
					'label'   => __( 'Show Counts' )
			)
	);
	$connect_without_opts = array(
			'tempUser'          => __( 'Temp User' ),
			'alwaysLogin'       => __( 'Always Login' ),
			'loginExistingUser' => __( 'Login Existing User' ),
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'connect_without',
					'options' => $connect_without_opts,
					'value'   => ! empty( $values['connect_without'] ) ? $values['connect_without'] : $connect_without_opts['loginExistingUser'],
					'label'   => __( 'Connect Without Login Behavior' ),
			)
	);
	$privacy_opts = array(
			"private" => __( "Private" ),
			"public"  => __( "Public" ),
			"friends" => __( "Friends" )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'share_privacy',
					'options' => $privacy_opts,
					'value'   => ! empty( $values['share_privacy'] ) ? $values['share_privacy'] : $privacy_opts['private'],
					'label'   => __( 'Privacy' ),
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'share_providers',
					'label' => __( 'Share Providers' ),
					'value' => ! empty( $values['share_providers'] ) ? $values['share_providers'] : '',
					'desc'  => __( 'for example: share,email,pinterest,twitter-tweet,google-plusone,facebook-like' )
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'textarea',
					'id'    => 'share_custom',
					'label' => __( "Custom Code" ),
					'value' => ! empty( $values['share_custom'] ) ? $values['share_custom'] : ''
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'textarea',
					'id'    => 'share_advanced',
					'label' => __( "Additional Parameters (advanced)" ),
					'value' => ! empty( $values['share_advanced'] ) ? $values['share_advanced'] : '',
					'desc'  => __( 'Enter values in' ) . '<strong>key|value</strong> ' . __( 'format' )
			)
	);
}