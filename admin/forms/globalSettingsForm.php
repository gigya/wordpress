<?php
/**
 * Form builder for 'Global Settings' configuration page.
 */
function globalSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GLOBAL );
	$form   = array();

	$form['api_key'] = array(
			'type'  => 'text',
			'label' => __( 'Gigya API Key' ),
			'value' => _gigParam( $values, 'api_key', '' )
	);

	$form['api_secret'] = array(
			'type'  => 'text',
			'label' => __( 'Gigya Secret Key' ),
			'value' => _gigParam( $values, 'api_secret', '' )
	);

	$dataCenter = _gigParam( $values, 'data_center', 'us1.gigya.com' );
	$options = array(
				'us1.gigya.com' => __( 'US Data Center' ),
				'eu1.gigya.com' => __( 'EU Data Center' ),
				'au1.gigya.com' => __( 'AU Data Center' ),
				'other' => __( 'Other' )
	);
	if (!array_key_exists($dataCenter, $options)) {
	     $dataCenter = "other";
	}
	$val = $dataCenter == "other" ? current(explode('.', $values['data_center'])) : "";
	$form['data_center'] = array(
			'type'    => 'select',
			'options' => $options,
			'label'   => __( 'Data Center' ),
			'class'   => 'data_center',
			'value'   => $dataCenter,
			'markup' => "<span class='other_dataCenter'><input type='text' size='5' maxlength='5' class='input-xlarge' id='other_ds' name='other_ds' value='" . $val . "' /> <span>.gigya.com</span><p>Please specify the Gigya data center in which your site is defined. For example: 'EU1'. To verify your site location contact your Gigya implementation manager.</p></span>"
	);

	$form['enabledProviders'] = array(
			'type'  => 'text',
			'label' => __( 'List of providers' ),
			'value' => _gigParam( $values, 'enabledProviders', '*' ),
			'desc'  => __( 'Comma separated list of providers to include. For example: facebook,twitter,google. Leave empty or type * for all providers. See the entire list of available' ) . ' <a href="http://developers.gigya.com/020_Client_API/020_Methods/Socialize.showLoginUI">Providers</a>'
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
			'value'   => _gigParam( $values, 'lang', 'en' ),
			'label'   => __( 'Language' ),
			'desc'    => __( 'Please select the interface language' )
	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'value' => _gigParam( $values, 'advanced', '' ),
			'label' => __( 'Additional Parameters (advanced)' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/030_API_reference/010_Client_API/010_Objects/Conf_object" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	$form['google_analytics'] = array(
			'type'  => 'checkbox',
			'label' => __( "Enable Google Social Analytics" ),
			'value' => _gigParam( $values, 'google_analytics', 0 )
	);

	$form['debug'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gigya debug log' ),
			'value' => _gigParam( $values, 'debug', 0 ),
			'desc'  => __( 'Log all Gigya\'s requests and responses. You can then find the log' ) . ' <a href="javascript:void(0)" class="gigya-debug-log">' . __( 'here' ) . '</a>'
	);

	if ( get_option( 'gigya_settings_fields' ) ) {
		$form['clean_db'] = array(
				'markup' => '<a href="javascript:void(0)" class="clean-db">Database cleaner after upgrade</a><br><small>Press this button to remove all unnecessary elements of the previous version from your database.Please make sure to backup your database before performing the clean. Learn more about upgrading from the previous version <a href="http://developers.gigya.com/015_Partners/030_CMS_and_Ecommerce_Platforms/030_Wordpress_Plugin#Installing_the_Gigya_WordPress_Plugin">here.</a></small>'
		);
	}

	echo _gigya_form_render( $form, GIGYA__SETTINGS_GLOBAL );
}