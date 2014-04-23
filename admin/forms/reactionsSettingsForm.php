<?php
/**
 * Form builder for 'Reaction Settings' configuration page.
 */
function reactionsSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_REACTIONS );
	$form   = array();

	$form['on'] = array(
			'type'    => 'checkbox',
			'label'   => __( 'Enable Reaction Plugin' ),
			'default' => 0,
			'value'   => $values['on'] === '0' ? '0' :'1'
	);

	$form['position'] = array(
			'type'    => 'select',
			'label'   => __( 'Set the position of the reactions widget in a post page' ),
			'options' => array(
					"none"   => __( "None" ),
					"bottom" => __( "Bottom" ),
					"top"    => __( "Top" ),
					"both"   => __( "Both" ),
			),
			'value'   => _gigParam( $values['position'], 'none' ),
			'desc'    => __( 'You can also find Gigya Reactions widget in the widgets settings page.' )
	);

	$form['enabledProviders'] = array(
			'type'  => 'text',
			'label' => __( 'Providers' ),
			'value' => _gigParam( $values['enabledProviders'], 'reactions,facebook-like,google-plusone,twitter,email' ),
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin' )
	);

	$form['showCounts'] = array(
			'type'    => 'select',
			'label'   => __( 'Show Counts' ),
			'options' => array(
					"right" => __( "Right" ),
					"top"   => __( "Top" ),
					"none"  => __( "None" )
			),
			'value'   => _gigParam( $values['showCounts'], 'right' )
	);

	$form['countType'] = array(
			'type'    => 'select',
			'options' => array(
					"number"     => __( "Number" ),
					"percentage" => __( "Percentage" )
			),
			'value'   => _gigParam( $values['countType'], 'number' ),
			'label'   => __( 'Count Type' ),
	);

	$form['layout'] = array(
			'type'    => 'select',
			'label'   => __( 'Layout' ),
			'options' => array(
					"horizontal" => __( "Horizontal" ),
					"vertical"   => __( "Vertical" )
			),
			'value'   => _gigParam( $values['layout'], 'horizontal' )
	);

	$form['image'] = array(
			'type'  => 'checkbox',
			'value' => _gigParam( $values['image'], 0 ),
			'label' => __( 'Set image URL' ),
			'class' => 'conditional'
	);

	$form['imageURL'] = array(
			'type'  => 'text',
			'label' => __( "Default URL of the image to share" ),
			'value' => _gigParam( $values['imageURL'], '' ),
	);

	$form['multipleReactions'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Allow multiple reactions' ),
			'value' => _gigParam( $values['multipleReactions'], 0 ),
	);

	$form['buttons'] = array(
			'type'  => 'textarea',
			'label' => __( 'Reaction Buttons' ),
			'value' => _gigParam( $values['buttons'], _gigya_get_json( 'admin/forms/json/default_reaction' ) )
	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values['advanced'], '' ),
			'desc'  => __( 'Enter validate JSON format' ) . ' <br> ' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showReactionsBarUI" target="_blank">parameters</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_REACTIONS );
}