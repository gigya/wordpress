<?php
/**
 * Form builder for 'Global Settings' configuration page.
 */
function globalConfigForm() {
	$values = get_option( GIGYA__SETTINGS_PREFIX );
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'global_api_key',
					'label' => __( 'Gigya Socialize API Key' ),
					'value' => !empty($values['global_api_key']) ? $values['global_api_key'] : ''
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'global_secret_key',
					'label' => __( 'Gigya Socialize Secret Key' ),
					'value' => !empty($values['global_secret_key']) ? $values['global_secret_key'] : ''
			)
	);
	$data_center_opts = array(
			'us1.gigya.com' => __( 'US Data Center' ),
			'eu1.gigya.com' => __( 'EU Data Center' )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'global_data_center',
					'options' => $data_center_opts,
					'label'   => __( 'Data Center' ),
					'class'   => 'data_center',
					'value' => !empty($values['global_data_center']) ? $values['global_data_center'] : 'us1.gigya.com'
			)
	);
	_gigya_formEl(
			array(
					'type'    => 'text',
					'id'      => 'global_providers',
					'label'   => __( 'List of providers' ),
					'value' => !empty($values['global_providers']) ? $values['global_providers'] : '*',
					'desc'    => __( 'Comma separated list of networks that would be included. For example: Facebook, Twitter, Yahoo means all networks. See list of available' ) . '<a href="http://developers.gigya.com/020_Client_API/020_Methods/Socialize.showLoginUI">Providers</a>'
			)
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
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'global_lang',
					'options' => $lang_opts,
					'value' => !empty($values['global_lang']) ? $values['global_lang'] : 'en',
					'label'   => __( 'Language' ),
					'desc'    => __( 'Please select the interface language' )
			)
	);
	$short_url_opts = array(
			'always'       => __( 'Always' ),
			'whenRequired' => __( 'When Required' ),
			'never'        => __( 'Never' )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'global_short_url',
					'options' => $short_url_opts,
					'value' => !empty($values['global_short_url']) ? $values['global_short_url'] : $short_url_opts['never'],
					'label'   => __( 'shortURL' ),
					'desc'    => __( 'Please select the interface language' )
			)
	);
	_gigya_formEl(
			array(
					'type'    => 'textarea',
					'id'      => 'global_params',
					'value' => !empty($values['global_params']) ? $values['global_params'] : '',
					'label'   => __( 'Additional Parameters (advanced)' ),
					'desc'    => __( 'Enter values in' ) . ' <strong>key1=value1|key2=value2...keyX=valueX</strong> format<br /> ' . __( 'See list of available' ) . ' <a href="http://developers.gigya.com/030_API_reference/010_Client_API/010_Objects/Conf_object" target="_blank">parameters</a>'
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'global_google_analytics',
					'label' => __( "Google's Social Analytics" ),
					'value' => !empty($values['global_google_analytics']) ? $values['global_google_analytics'] : 0
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'global_gigya_debug',
					'label' => __( 'Enable Gigya debug log' ),
					'value' => !empty($values['global_gigya_debug']) ? $values['global_gigya_debug'] : 0
			)
	);
}