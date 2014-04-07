<?php
/**
 * Form builder for 'Share Settings' configuration page.
 */
function shareSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_SHARE );
	$form   = array();

	$form['share_plugin'] = array(
			'type'    => 'checkbox',
			'label'   => __( 'Enable Share Plugin' ),
			'default' => 0,
			'value'   => _gigParam( $values['share_plugin'], 0 )
	);

	$share_opts = array(
			"none"   => __( "None" ),
			"bottom" => __( "Bottom" ),
			"top"    => __( "Top" ),
	);

	$form['share_position'] = array(
			'type'    => 'select',
			'options' => $share_opts,
			'label'   => __( 'Set the position of the share widget in a post page' ),
			'value'   => _gigParam( $values['share_position'], 'none' ),
			'desc'    => __( 'You can also find Gigya Share widget in the widgets settings page.' )
	);

	$form['share_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Share Providers' ),
			'value' => _gigParam( $values['share_providers'], 'share,facebook-like,google-plusone,twitter,email' ),
			'desc'  => __( 'for example: share,email,pinterest,twitter-tweet,google-plusone,facebook-like' )
	);

	$share_counts_opts = array(
			"right" => __( "Right" ),
			"top"   => __( "Top" ),
			"none"  => __( "None" )
	);

	$form['share_counts'] = array(
			'type'    => 'select',
			'options' => $share_counts_opts,
			'value'   => _gigParam( $values['share_counts'], 'right' ),
			'label'   => __( 'Show Counts' )
	);

	$layout_opts = array(
			"horizontal" => __( "Horizontal" ),
			"vertical"   => __( "Vertical" ),
	);

	$form['share_layout'] = array(
			'type'    => 'select',
			'options' => $layout_opts,
			'value'   => _gigParam( $values['share_layout'], 'horizontal' ),
			'label'   => __( 'Layout' ),
	);

	$form['share_image'] = array(
			'type'  => 'checkbox',
			'value' => _gigParam( $values['share_image'], 0 ),
			'label' => __( 'Set image URL' ),
	);

	$form['share_image_url'] = array(
			'type'  => 'text',
			'label' => __( "Default URL of the image to share" ),
			'value' => _gigParam( $values['share_image_url'], '' ),
	);

	$form['share_short_url'] = array(
			'type'  => 'checkbox',
			'label' => __( "Share using short URLs" ),
			'value' => _gigParam( $values['share_short_url'], 0 )
	);

	$form['share_advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values['share_advanced'], '' ),
			'desc'  => __( 'Enter validate JSON format' )
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_SHARE );
}