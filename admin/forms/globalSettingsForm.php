<?php
/**
 * Form builder for 'Global Settings' configuration page.
 */
function globalSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GLOBAL );
	$form   = array();

	if ( get_option( 'gigya_settings_fields' ) ) {
		$form['clean_db'] = array(
				'markup' => '<a href="javascript:void(0)" class="clean-db">Database cleaner after upgrade</a><br><small>Very recommended to backup your database before preform a clean!</small>'
		);
	}

	$form['api_key'] = array(
			'type'  => 'text',
			'label' => __( 'Gigya API Key' ),
			'value' => _gigParam( $values['api_key'], '' )
	);

	$form['api_secret'] = array(
			'type'  => 'text',
			'label' => __( 'Gigya Secret Key' ),
			'value' => _gigParam( $values['api_secret'], '' )
	);

	$form['data_center'] = array(
			'type'    => 'select',
			'options' => array(
					'us1.gigya.com' => __( 'US Data Center' ),
					'eu1.gigya.com' => __( 'EU Data Center' )
			),
			'label'   => __( 'Data Center' ),
			'class'   => 'data_center',
			'value'   => _gigParam( $values['data_center'], 'us1.gigya.com' )
	);

	$form['providers'] = array(
			'type'  => 'text',
			'label' => __( 'List of providers' ),
			'value' => _gigParam( $values['providers'], '*' ),
			'desc'  => __( 'Comma separated list of login providers that would be included. For example: facebook,twitter,google. Leave empty or type * for all providers. See the entire list of available' ) . ' <a href="http://developers.gigya.com/020_Client_API/020_Methods/Socialize.showLoginUI">Providers</a>'
	);

	$form['lang'] = array(
			'type'    => 'select',
			'options' => array(
					'en'    => 'English',
					'zh-cn' => 'Chinese',
					'zh-hk' => 'Chinese (Hong Kong)',
					'zh-tw' => 'Chinese (Taiwan)',
					'cs'    => 'Czech',
					'da'    => 'Danish',
					'nl'    => 'Dutch',
					'fi'    => 'Finnish',
					'fr'    => 'French',
					'de'    => 'German',
					'el'    => 'Greek',
					'hu'    => 'Hungarian',
					'id'    => 'Indonesian',
					'it'    => 'Italian',
					'ja'    => 'Japanese',
					'ko'    => 'Korean',
					'ms'    => 'Malay',
					'no'    => 'Norwegian',
					'pl'    => 'Polish',
					'pt'    => 'Portuguese',
					'pt-br' => 'Portuguese (Brazil)',
					'ro'    => 'Romanian',
					'ru'    => 'Russian',
					'es'    => 'Spanish',
					'es-mx' => 'Spanish (Mexican)',
					'sv'    => 'Swedish',
					'tl'    => 'Tagalog (Philippines)',
					'th'    => 'Thai',
					'tr'    => 'Turkish',
					'uk'    => 'Ukrainian',
					'vi'    => 'Vietnamese',
			),
			'value'   => _gigParam( $values['lang'], 'en' ),
			'label'   => __( 'Language' ),
			'desc'    => __( 'Please select the interface language' )
	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'value' => _gigParam( $values['advanced'], '' ),
			'label' => __( 'Additional Parameters (advanced)' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/030_API_reference/010_Client_API/010_Objects/Conf_object" target="_blank">parameters</a>'
	);

	$form['google_analytics'] = array(
			'type'  => 'checkbox',
			'label' => __( "Enable Google Social Analytics" ),
			'value' => _gigParam( $values['google_analytics'], 0 )
	);

	$form['debug'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gigya debug log' ),
			'value' => _gigParam( $values['debug'], 0 ),
			'desc'  => __( 'Log all Gigya\'s requests and responses. You can then find the log' ) . ' <a href="javascript:void(0)" class="gigya-debug-log">' . __( 'here' ) . '</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_GLOBAL );
}