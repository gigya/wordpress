<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function feedSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_FEED );
	$form   = array();

	$form['feed_plugin'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Activity Feed' ),
			'value' => _gigParam( $values['feed_plugin'], 0 )
	);

	$form['feed_tabs'] = array(
			'type'  => 'text',
			'label' => __( 'Tabs and order' ),
			'value' => _gigParam( $values['feed_tabs'], 'everyone,friends,me' ),
			'desc'  => __( 'A comma delimited list of tabs names that defines which tabs to show and the tabs order. The optional tabs names are: "everyone", "friends", "me".' )
	);

	$form['feed_width'] = array(
			'type'  => 'text',
			'label' => __( 'Custom Width' ),
			'value' => _gigParam( $values['feed_width'], '309' ),
			'desc'  => __( 'The width of the plugin in px' ),
			'class' => 'size'
	);

	$form['feed_height'] = array(
			'type'  => 'text',
			'label' => __( 'Custom Height' ),
			'value' => _gigParam( $values['feed_height'], '270' ),
			'desc'  => __( 'The height of the plugin in px' ),
			'class' => 'size'
	);

	$feed_privacy_opts = array(
			"private" => __( "Private" ),
			"public"  => __( "Public" ),
			"friends" => __( "Friends" )
	);

	$form['feed_privacy'] = array(
			'type'    => 'select',
			'options' => $feed_privacy_opts,
			'value'   => _gigParam( $values['feed_privacy'], 'private' ),
			'label'   => __( 'Activity Feed privacy level' ),
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_FEED );
}