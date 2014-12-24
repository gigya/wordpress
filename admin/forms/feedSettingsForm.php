<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function feedSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_FEED );
	$form   = array();

	$form['on'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Activity Feed Plugins' ),
			'value' => _gigParamDefaultOn( $values, 'on' ),
			'desc'  => __( 'Enable Sharing to Activity Feed.' )
	);

	$form['privacy'] = array(
			'type'    => 'select',
			'options' => array(
					"private" => __( "Private" ),
					"public"  => __( "Public" ),
					"friends" => __( "Friends" )
			),
			'value'   => _gigParam( $values, 'privacy', 'private' ),
			'label'   => __( 'Activity Feed privacy level' ),
	);

	$form['scope'] = array(
			'type'    => 'select',
			'options' => array(
					"both"     => __( "both" ),
					"external" => __( "External" )
			),
			'value'   => _gigParam( $values, 'scope', 'external' ),
			'label'   => __( 'Enable Sharing to Activity Feed' ),
			'desc'    => __( 'When publishing feed items, like comment and reactions, by default the feed items are published to social networks only and will not appear<br> on the site\'s Activity Feed plugin ("External"). To change this behavior, you must change the publish scope to "Both"' )
	);

	$form['tabOrder'] = array(
			'type'  => 'text',
			'label' => __( 'Tabs and order' ),
			'value' => _gigParam( $values, 'tabOrder', 'everyone,friends,me' ),
			'desc'  => __( 'A comma delimited list of tab names that defines which tabs to show and the tab order. The optional tab names are: "everyone", "friends", "me".' )
	);

	$form['width'] = array(
			'type'  => 'text',
			'label' => __( 'Custom Width' ),
			'value' => _gigParam( $values, 'width', '170' ),
			'desc'  => __( 'The width of the plugin in px' ),
			'class' => 'size'
	);

	$form['height'] = array(
			'type'  => 'text',
			'label' => __( 'Custom Height' ),
			'value' => _gigParam( $values, 'height', '270' ),
			'desc'  => __( 'The height of the plugin in px' ),
			'class' => 'size'
	);

	$form['advanced'] = array(
		'type'  => 'textarea',
		'value' => _gigParam( $values, 'advanced', '' ),
		'label' => __( 'Additional Parameters (advanced)' ),
		'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showFeedUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_FEED );
}