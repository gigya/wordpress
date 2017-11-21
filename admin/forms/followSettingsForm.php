<?php
/**
 * Form builder for 'Share Settings' configuration page.
 */
function followSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_FOLLOW );
	$form   = array();

	$form['on'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Follow Bar Plugin' ),
			'value' => _gigParamDefaultOn( $values, 'on' )
	);


	$form['followButtons'] = array(
			'type'  => 'textarea',
			'label' => __( 'Follow Buttons' ),
			'value' => _gigParam( $values, 'followButtons', _gigya_get_json( 'admin/forms/json/default_follow' ) ),
			'desc'  => __( 'Please fill valid JSON for follow-bar button as describe' ) . ' ' . '<a href="http://developers.gigya.com/010_Developer_Guide/18_Plugins/050_Follow_Bar#Quick_Start_Implementation" target="_blank" rel="noopener noreferrer">' . __( 'here' ) . '</a>',
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

	$form['iconSize'] = array(
		'type'  => 'text',
		'value' => esc_attr( _gigParam( $values, 'iconSize', 32 ) ),
		'label' => __( 'Icon size' ),
	    'desc'  => __( 'The size of the follow icons' )
	);


	$form['advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values, 'advanced', '' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/010_Socialize/socialize.showFollowBarUI" target="_blank" rel="noopener noreferrer">' . __( 'parameters' ) . '</a>'
	);

    // use this field in multisite to flag when sub site settings are saved locally for site
    if ( is_multisite() && !$values['sub_site_settings_saved'] ) {
        $form['sub_site_settings_saved'] = array(
            'type' => 'hidden',
            'id' => 'sub_site_settings_saved',
            'value' => 1,
            'msg' => 1,
            'msg_txt' => 'Settings are set to match the main site. once saved they will become independent',
            'class' => 'gigya-raas-warn'
        );
    }

	echo _gigya_form_render( $form, GIGYA__SETTINGS_FOLLOW );
}