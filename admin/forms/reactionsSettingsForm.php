<?php
/**
 * Form builder for 'Reaction Settings' configuration page.
 */
function reactionsSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_REACTIONS );
	$form   = array();

	$form['reactions_plugin'] = array(
			'type'    => 'checkbox',
			'label'   => __( 'Enable Reaction Plugin' ),
			'default' => 0,
			'value'   => _gigParam( $values['reactions_plugin'], 0 )
	);

	$reaction_position_opts = array(
			"none"   => __( "None" ),
			"bottom" => __( "Bottom" ),
			"top"    => __( "Top" ),
	);

	$form['reactions_position'] = array(
			'type'    => 'select',
			'label'   => __( 'Set the position of the reactions widget in a post page' ),
			'options' => $reaction_position_opts,
			'value'   => _gigParam( $values['reactions_position'], 'none' ),
			'desc'    => __( 'You can also find Gigya Reactions widget in the widgets settings page.' )
	);

	$form['reactions_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Providers' ),
			'value' => _gigParam( $values['reactions_providers'], 'reactions,facebook-like,google-plusone,twitter,email' ),
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin' )
	);

	$reaction_counts_opts = array(
			"right" => __( "Right" ),
			"top"   => __( "Top" ),
			"none"  => __( "None" )
	);

	$form['reactions_counts'] = array(
			'type'    => 'select',
			'label'   => __( 'Show Counts' ),
			'options' => $reaction_counts_opts,
			'value'   => _gigParam( $values['reactions_counts'], 'right' )
	);

	$reaction_count_type_opts = array(
			"number"     => __( "Number" ),
			"percentage" => __( "Percentage" )
	);

	$form['reactions_count_type'] = array(
			'type'    => 'select',
			'options' => $reaction_count_type_opts,
			'value'   => _gigParam( $values['reactions_count_type'], 'number' ),
			'label'   => __( 'Count Type' ),
	);

	$reaction_layout_opts = array(
			"horizontal" => __( "Horizontal" ),
			"vertical"   => __( "Vertical" )
	);

	$form['reactions_layout'] = array(
			'type'    => 'select',
			'label'   => __( 'Layout' ),
			'options' => $reaction_layout_opts,
			'value'   => _gigParam( $values['reactions_layout'], 'horizontal' )
	);

	$form['reactions_image'] = array(
			'type'  => 'checkbox',
			'value' => _gigParam( $values['reactions_image'], 0 ),
			'label' => __( 'Set image URL' ),
	);

	$form['reactions_image_url'] = array(
			'type'  => 'text',
			'label' => __( "Default URL of the image to share" ),
			'value' => _gigParam( $values['reactions_image_url'], '' ),
	);

	$form['reactions_multiple'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Allow multiple reactions' ),
			'value' => _gigParam( $values['reactions_multiple'], 1 ),
	);

	$form['reactions_buttons'] = array(
			'type'  => 'textarea',
			'label' => __( 'Reaction Buttons' ),
			'value' => _gigParam( $values['reactions_buttons'], _gigya_get_json( 'admin/forms/json/default_reaction' ) )
	);

	$form['reactions_advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values['reactions_advanced'], '' ),
			'desc'  => __( 'Enter validate JSON format' ) . ' <br> ' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showReactionsBarUI" target="_blank">parameters</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_REACTIONS );
}