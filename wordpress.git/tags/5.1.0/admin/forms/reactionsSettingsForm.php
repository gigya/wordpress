<?php
/**
 * Form builder for 'Reaction Settings' configuration page.
 */
function reactionsSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_REACTIONS );
	$form   = array();

	$form['on'] = array(
			'type'    => 'checkbox',
			'label'   => __( 'Enable Reactions Plugin' ),
			'default' => 0,
			'value'   => _gigParamDefaultOn( $values, 'on' )
	);

	$form['position'] = array(
			'type'    => 'select',
			'label'   => __( 'Set the position of the Reactions in a post page' ),
			'options' => array(
					"none"   => __( "None" ),
					"bottom" => __( "Bottom" ),
					"top"    => __( "Top" ),
					"both"   => __( "Both" ),
			),
			'value'   => _gigParam( $values, 'position', 'none' ),
			'desc'    => sprintf( __( 'You can also add and position Gigya Reactions using the %s settings page.' ), '<a href="' . admin_url( 'widgets.php' ) . '">' . __( 'Widgets' ) . '</a>' )
	);

	$form['enabledProviders'] = array(
			'type'  => 'text',
			'label' => __( 'Providers' ),
			'value' => _gigParam( $values, 'enabledProviders', '*' ),
			'desc'  => __( 'Comma separated list of share providers to include. For example: facebook,twitter,linkedin. Leave empty or type * for all providers. See the entire list of available' ) . ' <a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showReactionsBarUI">Providers</a>'
	);

	$form['showCounts'] = array(
			'type'    => 'select',
			'label'   => __( 'Show Counts' ),
			'options' => array(
					"right" => __( "Right" ),
					"top"   => __( "Top" ),
					"none"  => __( "None" )
			),
			'value'   => _gigParam( $values, 'showCounts', 'right' )
	);

	$form['countType'] = array(
			'type'    => 'select',
			'options' => array(
					"number"     => __( "Number" ),
					"percentage" => __( "Percentage" )
			),
			'value'   => _gigParam( $values, 'countType', 'number' ),
			'label'   => __( 'Count Type' ),
	);

	$form['layout'] = array(
			'type'    => 'select',
			'label'   => __( 'Layout' ),
			'options' => array(
					"horizontal" => __( "Horizontal" ),
					"vertical"   => __( "Vertical" )
			),
			'value'   => _gigParam( $values, 'layout', 'horizontal' )
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

	$form['multipleReactions'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Allow multiple reactions' ),
			'value' => _gigParam( $values, 'multipleReactions', 0 ),
	);

	$form['buttons'] = array(
			'type'  => 'textarea',
			'label' => __( 'Reaction Buttons' ),
			'value' => _gigParam( $values, 'buttons', _gigya_get_json( 'admin/forms/json/default_reaction' ) ),
			'desc'  => sprintf( __( 'Please enter an array of %s, representing the buttons to display in the Reactions bar.' ), '<a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showReactionsBarUI#Reaction_Object">' . _( 'Reaction objects' ) . '</a>' )
	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values, 'advanced', '' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showReactionsBarUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_REACTIONS );
}