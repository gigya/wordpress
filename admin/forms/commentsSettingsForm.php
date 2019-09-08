<?php
/**
 * Form builder for 'Comment Settings' configuration page.
 */
function commentsSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_COMMENTS );
	$form   = array();

	$form['on'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable SAP CDC Comments' ),
			'value' => _gigParamDefaultOn( $values, 'on' )
	);

	$form['rating'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Rating & Reviews mode' ),
			'value' => _gigParam( $values, 'rating', 0 ),
			'desc'  => sprintf( __( 'Checking this button will change the mode of the Comment plugin to Rating & Reviews. Please make sure that the Category ID defined below is set to Rating & Reviews mode in the %s.' ), '<a href="https://console.gigya.com/Site/partners/Settings.aspx#cmd%3DSettings.CommentsSetup">' . __( 'SAP Customer Data Cloud platform' ) . '</a>' )
	);

	$form['categoryID'] = array(
			'type'  => 'text',
			'label' => __( 'Category ID' ),
			'value' => _gigParam( $values, 'categoryID', '' ),
			'desc'  => sprintf( __( "Copy the ID under 'Comments category name' from %s." ), '<a href="https://console.gigya.com/Site/partners/Settings.aspx#cmd%3DSettings.CommentsSetup">' . __( 'SAP Customer Data Cloud platform' ) . '</a>' )
	);

	$form['enabledShareProviders'] = array(
			'type'  => 'text',
			'label' => __( 'Providers' ),
			'value' => _gigParam( $values, 'enabledShareProviders', '*' ),
			'desc'  => __( 'Comma separated list of share providers to include. For example: facebook,twitter,linkedin. Leave empty or type * for all providers.' )
	);

	$form['position'] = array(
			'type'    => 'select',
			'options' => array(
					'under' => __( 'Under Post' ),
					'none'  => __( 'None' )
			),
			'label'   => __( 'Set the position of the Comments in a post page' ),
			'value'   => _gigParam( $values, 'position', 'under' ),
			'desc'    => sprintf( __( 'You can also add and position Comments using the %s settings page.' ), '<a href="' . admin_url( 'widgets.php' ) . '">' . __( 'Widgets' ) . '</a>' )
	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values, 'advanced', '' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="https://developers.gigya.com/display/GD/comments.showCommentsUI+JS" target="_blank" rel="noopener noreferrer">' . __( 'parameters' ) . '</a>'
	);

	/* Use this field in multisite to flag when sub site settings are saved locally for site */
	if ( is_multisite() ) {
		$form['sub_site_settings_saved'] = array(
			'type'  => 'hidden',
			'id'    => 'sub_site_settings_saved',
			'value' => 1,
			'class' => 'gigya-raas-warn'
		);

		if ( empty( $values['sub_site_settings_saved'] ) ) {
			$form['sub_site_settings_saved']['msg']     = 1;
			$form['sub_site_settings_saved']['msg_txt'] = __( 'Settings are set to match the main site. Once saved they will become independent' );
		}
	}

	echo _gigya_form_render( $form, GIGYA__SETTINGS_COMMENTS );
}