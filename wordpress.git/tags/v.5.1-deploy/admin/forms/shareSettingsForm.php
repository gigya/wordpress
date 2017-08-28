<?php
/**
 * Form builder for 'Share Settings' configuration page.
 */
function shareSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_SHARE );
	$form   = array();

	$form['on'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Share Bar Plugin' ),
			'value' => _gigParamDefaultOn( $values, 'on' )
	);

	$form['position'] = array(
			'type'    => 'select',
			'options' => array(
					"none"   => __( "None" ),
					"bottom" => __( "Bottom" ),
					"top"    => __( "Top" ),
					"both"   => __( "Both" ),
			),
			'label'   => __( 'Set the position of the Share Bar in a post page' ),
			'value'   => _gigParam( $values, 'position', 'none' ),
			'desc'    => sprintf( __( 'You can also add and position Gigya Share Bar using the %s settings page.' ), '<a href="' . admin_url( 'widgets.php' ) . '">' . __( 'Widgets' ) . '</a>' )
	);

	$form['shareButtons'] = array(
			'type'  => 'text',
			'label' => __( 'Share Providers' ),
			'value' => _gigParam( $values, 'shareButtons', 'share,facebook-like,google-plusone,twitter,email' ),
			'desc'  => __( 'For example: share,email,pinterest,twitter-tweet,google-plusone,facebook-like.' )
	);

	$form['showCounts'] = array(
			'type'    => 'select',
			'options' => array(
					"right" => __( "Right" ),
					"top"   => __( "Top" ),
					"none"  => __( "None" )
			),
			'value'   => _gigParam( $values, 'showCounts', 'right' ),
			'label'   => __( 'Show Counts' )
	);

	$form['layout'] = array(
			'type'    => 'select',
			'options' => array(
					"horizontal" => __( "Horizontal" ),
					"vertical"   => __( "Vertical" ),
			),
			'value'   => _gigParam( $values, 'layout', 'horizontal' ),
			'label'   => __( 'Layout' ),
	);

	$form['image'] = array(
			'type'  => 'checkbox',
			'value' => _gigParam( $values, 'image', 0 ),
			'label' => __( 'Set image URL' ),
			'class' => 'conditional'
	);

	$form['imageURL'] = array(
			'type'  => 'text',
			'label' => __( "Default URL of the image to share" ),
			'value' => _gigParam( $values, 'imageURL', '' ),
	);

	$form['shortURLs'] = array(
			'type'  => 'checkbox',
			'label' => __( "Share using short URLs" ),
			'value' => _gigParam( $values, 'shortURLs', 0 )
	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values, 'advanced', '' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/020_Methods/socialize.showShareBarUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_SHARE );
}