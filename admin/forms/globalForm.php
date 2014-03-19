<?php
function globalSectionCallback() {
	echo '<div class="well">';
	_gigya_formEl(
			array(
					'type' => 'text',
					'id' => 'api_key',
					'label' => 'Gigya Socialize API Key'
			)
	);
	_gigya_formEl(
			array(
					'type' => 'text',
					'id' => 'secret_key',
					'label' => 'Gigya Socialize Secret Key'
			)
	);
	$data_center_opts = array(
			'us1.gigya.com' => 'US Data Center',
			'eu1.gigya.com' => 'EU Data Center',
	);
	_gigya_formEl(
			array(
					'type' => 'select',
					'id' => 'data_center',
					'options' => $data_center_opts,
					'label' => 'Data Center',
					'class' => 'data_center'
			)
	);
	_gigya_formEl(
			array(
					'type' => 'text',
					'id' => 'providers',
					'default' => '*',
					'label' => 'List of providers',
					'desc' => 'Comma separated list of networks that would be included. For example: Facebook, Twitter, Yahoo means all networks. See list of available' . '<a href="http://developers.gigya.com/020_Client_API/020_Methods/Socialize.showLoginUI">Providers</a>'
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
					'type' => 'select',
					'id' => 'lang',
					'options' => $lang_opts,
					'default' => 'en',
					'label' => 'Language',
					'desc' => 'Please select the interface language'
			)
	);
	$short_url_opts = array(
			'always' => 'Always',
			'whenRequired' => 'When Required',
			'never' => 'Never'
	);
	_gigya_formEl(
			array(
					'type' => 'select',
					'id' => 'short_url',
					'options' => $short_url_opts,
					'default' => $short_url_opts['never'],
					'label' => 'shortURL',
					'desc' => 'Please select the interface language'
			)
	);
	_gigya_formEl(
			array(
					'type' => 'textarea',
					'id' => 'global_params',
					'default' => $short_url_opts['never'],
					'label' => 'Additional Parameters (advanced)',
					'desc' => 'Enter values in ' . '<strong>key1=value1|key2=value2...keyX=valueX</strong> format<br />' . ' See list of available <a href="http://developers.gigya.com/030_API_reference/010_Client_API/010_Objects/Conf_object" target="_blank">parameters</a>'
			)
	);
	_gigya_formEl(
			array(
					'type' => 'checkbox',
					'id' => 'google_analytics',
					'label' => "Google's Social Analytics",
			)
	);
	_gigya_formEl(
			array(
					'type' => 'checkbox',
					'id' => 'gigya_debug',
					'label' => "Enable Gigya debug log",
			)
	);
	echo '</div>';
}