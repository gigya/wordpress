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
//			"both"   => __( "Both" )
	);

	$form['share_position'] = array(
			'type'    => 'select',
			'options' => $share_opts,
			'label'   => __( 'Set the position of the share widget in a post page' ),
			'value'   => getParam( $values['share_position'], 'none' ),
			'desc'    => __( 'You can also find Gigya Share widget in the widgets settings page, and position it through there.' )
	);

	$form['share_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Share Providers' ),
			'value' => getParam( $values['share_providers'], 'share,facebook-like,google-plusone,twitter,email' ),
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
			'value'   => getParam( $values['share_counts'], 'right' ),
			'label'   => __( 'Show Counts' )
	);

	$layout_opts = array(
			"horizontal" => __( "Horizontal" ),
			"vertical"   => __( "Vertical" ),
	);

	$form['share_layout'] = array(
			'type'    => 'select',
			'options' => $layout_opts,
			'value'   => getParam( $values['share_layout'], 'horizontal' ),
			'label'   => __( 'Privacy' ),
	);

//	$image_opts = array(
//			"default" => __( "Use image tag if exists, first image on post otherwise" ),
//			"first"  => __( "Use first image on the post" ),
//			"url"  => __( "Specify an image URL" )
//	);

	$form['share_image'] = array(
			'type'  => 'checkbox',
//			'options' => $image_opts,
			'value' => getParam( $values['share_image'], 0 ),
			'label' => __( 'Set image URL' ),
	);

	$form['share_image_url'] = array(
			'type'  => 'text',
			'label' => __( "Default URL of the image to share" ),
			'value' => getParam( $values['share_image_url'], '' ),
	);

	$form['share_short_url'] = array(
			'type'  => 'checkbox',
			'label' => __( "Share using short URLs" ),
			'value' => getParam( $values['share_short_url'], 0 )
	);

	$form['share_advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => getParam( $values['share_advanced'], '' ),
			'desc'  => __( 'Enter validate JSON format' )
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_SHARE );
}