<?php
/**
 * Form builder for 'Global Settings' configuration page.
 */
function globalSettingsForm() {

    $values = _getGigyaSettingsValues( GIGYA__SETTINGS_GLOBAL );
	$form   = array();

	$form['api_key'] = array(
		'type'  => 'text',
		'label' => __( 'API Key' ),
		'size'  => 64,
		'style' => 'font-family: monospace',
		'value' => _gigParam( $values, 'api_key', '' )
	);

	$form['user_key'] = array(
		'type'  => 'text',
		'label' => __( 'User Key' ),
		'value' => trim(_gigParam( $values, 'user_key', '' ))
	);

	if ( current_user_can( GIGYA__SECRET_PERMISSION_LEVEL ) || current_user_can( CUSTOM_GIGYA_EDIT_SECRET ) ) {
		$form['auth_mode'] = array(
			'type'    => 'radio',
			'options' => array(
				'user_secret' => __( 'User key + secret key' ),
				'user_rsa'    => __( 'User key + RSA private key' ),
			),
			'value'   => _gigParam( $values, 'auth_mode', 'user_rsa' ),
			'id'      => 'auth-mode',
		);

		$form['api_secret']      = array(
			'type'       => 'password',
			'label'      => __( 'User Secret' ),
			'value'      => '',
			'desc'       => 'Secret key: ' . _gigParam( $values, 'api_secret', '', true ),
			'depends_on' => [ 'auth_mode', 'user_secret' ],
		);
		$is_private_key_entered = ( ! empty( _gigParam( $values, 'rsa_private_key', '' ) ) );
		$form['rsa_private_key'] = array(
			'type'        => 'textarea',
			'label'       => __( 'RSA Private Key' ),
			'value'       => '',
			'desc'        => 'Private key ' . ( $is_private_key_entered ? '' : 'not ' ) . 'entered',
			'class'       => 'rsa-private-key',
			'depends_on'  => [ 'auth_mode', 'user_rsa' ],
			'placeholder' => ( $is_private_key_entered )
				? 'SAP Customer Data Cloud RSA private key has been entered'
				:'Enter your RSA private key, as provided by SAP Customer Data Cloud',
		);
	} else { /* No permissions to modify secret key / RSA private key -- read-only mode */
		$form['api_secret'] = array(
			'type'       => 'customText',
			'label'      => __( 'Secret Key' ),
			'class'      => 'secret_key_placeholder',
			'size'       => 100,
			'id'         => 'secret_key_placeholder',
			'depends_on' => [ 'auth_mode', 'user_secret' ],
		);

		$form['rsa_private_key'] = array(
			'type'       => 'customText',
			'label'      => __( 'RSA Private Key' ),
			'class'      => 'rsa_private_key_placeholder',
			'size'       => 100,
			'id'         => 'rsa_private_key_placeholder',
			'depends_on' => [ 'auth_mode', 'user_rsa' ],
		);
	}

	$dataCenter = _gigParam( $values, 'data_center', 'us1.gigya.com' );
	$options = array(
				'us1.gigya.com' => __( 'US Data Center' ),
				'eu1.gigya.com' => __( 'EU Data Center' ),
				'au1.gigya.com' => __( 'AU Data Center' ),
				'ru1.gigya.com' => __( 'RU Data Center' ),
				'cn1.sapcdm.cn' => __( 'CN Data Center' ),
				'other' => __( 'Other' )
	);
	if (!array_key_exists($dataCenter, $options)) {
	     $dataCenter = "other";
	}
	$form['data_center'] = array(
			'type'    => 'select',
			'options' => $options,
			'label'   => __( 'Data Center' ),
			'class'   => 'data_center',
			'value'   => $dataCenter,
	);

	$form['other_ds'] = array(
		'type'       => 'text',
		'class'      => 'other-data-center',
		'value'      => _gigParam( $values, 'other_ds', 'us1.gigya.com' ),
		'depends_on' => [ 'data_center', 'other' ],
	);

	$form['enabledProviders'] = array(
			'type'  => 'text',
			'label' => __( 'List of providers' ),
			'value' => _gigParam( $values, 'enabledProviders', '*' ),
			'desc'  => __( 'Comma separated list of providers to include. For example: facebook,twitter,google. Leave empty or type * for all providers. See the entire ' ) . ' <a href="https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/417916f470b21014bbc5a10ce4041860.html">list of available providers</a>.'
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
			'desc'    => __( 'Please select the interface language' ),
			'id' => 'global-language',

	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'class' => 'json',
			'value' => _gigParam( $values, 'advanced', '' ),
			'label' => __( 'Additional Parameters (advanced)' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available ' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/417fa48b70b21014bbc5a10ce4041860.html" target="_blank" rel="noopener noreferrer">' . __( 'parameters' ) . '</a>'
	);

	$form['log_level'] = array(
		'type'    => 'select',
		'options' => array(
			'error' => 'Error Only',
			'info'  => 'Info',
			'debug' => 'Debug'
		),
		'value'   => _gigParam( $values, 'log_level', 'info' ),
		'label'   => __( 'Log Level' ),
	);

	$end_of_desc = '';
	if ( file_exists( GIGYA__LOG_FILE ) and ( $file_size = filesize( GIGYA__LOG_FILE ) ) !== false ) {
		if ( $file_size < 1024 * 1024 ) {
			$file_size = round( $file_size / 1024, 1 ) . __( ' KB.' );
		} else {
			$file_size = round( $file_size / ( 1024 * 1024 ), 1 ) . __( ' MB.' );

		}
		$end_of_desc = '<br>' . __( 'The path to the file: ' ) . '<a class="gigya-debug-log" href="#">' . GIGYA__LOG_FILE . '</a>' . ( ( $file_size !== false )
				? ( __( ' and the size is: ' ) . $file_size ) : '' );
	}

	//$end_of_desc        .= '<br>' . __( 'for more information click ' ) . '<a href="">here.</a>'; un comment when there is a new documentation link for this feature.
	$debug_mode_warning = '<strong class="gigya-raas-warn">' . __( 'WARNING: The log file size caused by frequent debug logging may significantly degrade site performance. It is not recommended for production use, or for prolonged periods.' ) . '</strong>';

	$form['log_level_desc_error'] = array(
		'type'       => 'customDescription',
		'label'      => __( 'logLevelDesc' ),
		'desc'       => __( 'This level is for general site errors only.' ) . '<br>' . __( 'Logs site-wide errors related to the SAP CDC plugin.' ) . $end_of_desc,
		'small'      => true,
		'depends_on' => [ 'log_level', 'error' ],
	);
	$form['log_level_desc_info']  = array(
		'type'       => 'customDescription',
		'label'      => __( 'logLevelDesc' ),
		'desc'       => __( 'Logs all actions done by the administrator in the SAP CDC plugin. ' ) . $end_of_desc,
		'small'      => true,
		'depends_on' => [ 'log_level', 'info' ],
	);
	$form['log_level_desc_debug'] = array(
		'type'       => 'customDescription',
		'label'      => __( 'logLevelDesc' ),
		'desc'       => $debug_mode_warning . '<br>' . __( 'Logs all interactions with SAP CDC, i.e. every call made to SAP CDC is logged.' ) . $end_of_desc,
		'small'      => true,
		'depends_on' => [ 'log_level', 'debug' ],
	);


	$form['google_analytics'] = array(
		'type'  => 'checkbox',
		'label' => __( "Enable Google Social Analytics" ),
		'value' => _gigParam( $values, 'google_analytics', 0 )
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

	if ( get_option( 'gigya_settings_fields' ) ) {
		$form['clean_db'] = array(
			'markup' => '<a href="javascript:void(0)" class="clean-db">Database cleaner after upgrade</a><br><small>Press this button to remove all unnecessary elements of the previous version from your database.Please make sure to backup your database before performing the clean.For more information about upgrading from the previous version <a href="https://github.com/gigya/wordpress/wiki#installing-the-gigya-plugin-for-wordpress-1">here.</a></small>'
		);
	}

	echo _gigya_form_render( $form, GIGYA__SETTINGS_GLOBAL );
}
