<?php
/**
 * Form builder for 'Reaction Settings' configuration page.
 */
function reactionsSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_REACTIONS );
	$form   = array();

	$form['reaction_plugin'] = array(
			'type'    => 'checkbox',
			'label'   => __( 'Enable Reaction Plugin' ),
			'default' => 0,
			'value'   => ! empty( $values['reaction_plugin'] ) ? $values['reaction_plugin'] : 0
	);

	$reaction_position_opts    = array(
			"bottom" => __( "Bottom" ),
			"top"    => __( "Top" ),
			"both"   => __( "Both" )
	);
	$form['reaction_position'] = array(
			'type'    => 'select',
			'label'   => __( 'Position' ),
			'options' => $reaction_position_opts,
			'value'   => ! empty( $values['reaction_position'] ) ? $values['reaction_position'] : $reaction_position_opts['bottom']
	);

	$reaction_counts_opts = array(
			"right" => __( "Right" ),
			"top"   => __( "Top" ),
			"none"  => __( "None" )
	);

	$form['reaction_position'] = array(
			'type'    => 'select',
			'label'   => __( 'Show Counts' ),
			'options' => $reaction_counts_opts,
			'value'   => ! empty( $values['reaction_position'] ) ? $values['reaction_position'] : $reaction_counts_opts['right']
	);

	$reaction_layout_opts = array(
			"horizontal" => __( "Horizontal" ),
			"vertical"   => __( "Vertical" )
	);

	$form['reaction_layout'] = array(
			'type'    => 'select',
			'label'   => __( 'Layout' ),
			'options' => $reaction_layout_opts,
			'value'   => ! empty( $values['reaction_layout'] ) ? $values['reaction_layout'] : $reaction_layout_opts['horizontal']
	);

	$form['reaction_buttons'] = array(
			'type'  => 'textarea',
			'label' => __( 'Reaction Buttons' ),
			'value' => ! empty( $values['reaction_buttons'] ) ? $values['reaction_buttons'] : _gigya_get_json( 'admin/forms/json/default_reaction' )
	);

	$form['reaction_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Providers' ),
			'value' => ! empty( $values['reaction_providers'] ) ? $values['reaction_providers'] : '',
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook,twitter,google,linkedin' )
	);

	$reaction_share_opts = array(
			"both"     => __( "both" ),
			"external" => __( "External" )
	);

	$form['reaction_enable_share_activity'] = array(
			'type'    => 'select',
			'options' => $reaction_share_opts,
			'value'   => ! empty( $values['reaction_enable_share_activity'] ) ? $values['reaction_enable_share_activity'] : $reaction_share_opts['external'],
			'label'   => __( 'Enable Sharing to Activity Feed' ),
			'desc'    => 'When publishing feed items, by default the feed items are published to social networks only and will not appear on the site\'s Activity Feed plugin. To change this behavior, you must change the publish scope to "Both"'
	);

	$reaction_count_opts = array(
			"number"     => __( "Number" ),
			"percentage" => __( "Percentage" )
	);

	$form['reaction_count_type'] = array(
			'type'    => 'select',
			'options' => $reaction_count_opts,
			'value'   => ! empty( $values['reaction_count_type'] ) ? $values['reaction_count_type'] : $reaction_count_opts['number'],
			'label'   => __( 'Count Type' ),
	);

	$reaction_privacy_opts = array(
			"private" => __( "Private" ),
			"public"  => __( "Public" ),
			"friends" => __( "Friends" )
	);

	$form['reaction_privacy'] = array(
			'type'    => 'select',
			'options' => $reaction_privacy_opts,
			'value'   => ! empty( $values['reaction_privacy'] ) ? $values['reaction_privacy'] : $reaction_privacy_opts['private'],
			'label'   => __( 'Privacy' ),
	);

	$form['reactions_custom_code'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => ! empty( $values['reactions_custom_code'] ) ? $values['reactions_custom_code'] : '',
			'desc'  => __( 'Enter values in' ) . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __( 'format' ) . '<br>' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showReactionsBarUI" target="_blank">parameters</a>'
	);

	$form['reaction_multiple'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Allow multiple reactions' ),
			'value' => ! empty( $values['reaction_multiple'] ) ? $values['reaction_multiple'] : 1,
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_REACTIONS );
}