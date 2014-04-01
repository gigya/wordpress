<?php
/**
 * Form builder for 'Global Settings' configuration page.
 */
function globalSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GLOBAL );
	$form   = array();

	$form['global_api_key'] = array(
			'type'  => 'text',
			'label' => __( 'Gigya Socialize API Key' ),
			'value' => getParam( $values['global_api_key'], '' )
	);

	$form['global_api_secret'] = array(
			'type'  => 'text',
			'label' => __( 'Gigya Socialize Secret Key' ),
			'value' => getParam( $values['global_api_secret'], '' )
	);

	$data_center_opts = array(
			'us1.gigya.com' => __( 'US Data Center' ),
			'eu1.gigya.com' => __( 'EU Data Center' )
	);

	$form['global_data_center'] = array(
			'type'    => 'select',
			'options' => $data_center_opts,
			'label'   => __( 'Data Center' ),
			'class'   => 'data_center',
			'value'   => getParam( $values['global_data_center'], 'us1.gigya.com' )
	);

	$form['global_providers'] = array(
			'type'  => 'text',
			'label' => __( 'List of providers' ),
			'value' => getParam( $values['global_providers'], '*' ),
			'desc'  => __( 'Comma separated list of networks that would be included. For example: Facebook, Twitter, Yahoo means all networks. See list of available' ) . '<a href="http://developers.gigya.com/020_Client_API/020_Methods/Socialize.showLoginUI">Providers</a>'
	);

	$lang_opts = array(
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
	);

	$form['global_lang'] = array(
			'type'    => 'select',
			'options' => $lang_opts,
			'value'   => getParam( $values['global_lang'], 'en' ),
			'label'   => __( 'Language' ),
			'desc'    => __( 'Please select the interface language' )
	);

	$form['global_params'] = array(
			'type'  => 'textarea',
			'value' => getParam( $values['global_params'], '' ),
			'label' => __( 'Additional Parameters (advanced)' ),
			'desc'  => __( 'Enter one value per line, in the format' ) . ' <strong>key|value</strong> ' . __( 'See list of available' ) . ' <a href="http://developers.gigya.com/030_API_reference/010_Client_API/010_Objects/Conf_object" target="_blank">parameters</a>'
	);

	$form['global_google_analytics'] = array(
			'type'  => 'checkbox',
			'label' => __( "Google's Social Analytics" ),
			'value' => getParam( $values['global_google_analytics'], 0 )
	);

	$form['global_gigya_debug'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gigya debug log' ),
			'value' => getParam( $values['global_gigya_debug'], 0 )
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_GLOBAL );
}