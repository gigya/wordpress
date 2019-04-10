<?php
function buildCustomScreenSetRow( $values = array(), $more_options = array(), $more_field_options = array() ) {
	$values             = array_values( $values );
	$more_field_options = array_values( $more_field_options );
	$row                = [
		'type'   => 'dynamic_field_line',
		'fields' => [
			[
				'type'     => 'text',
				'name'     => 'desktop',
				'label'    => 'Desktop Screen-Set',
				'value'    => ( ! empty( $values[0] ) ) ? $values[0] : '',
				'required' => 'line-empty',
			],
			[
				'type'  => 'text',
				'name'  => 'mobile',
				'label' => 'Mobile Screen-Set',
				'value' => ( ! empty( $values[1] ) ) ? $values[1] : '',
			],
			[
				'type'  => 'checkbox',
				'name'  => 'is_sync',
				'label' => 'Sync Data?',
				'value' => ( ! empty( $values[2] ) ) ? $values[2] : 0,
			],
		],
	];

	$row = array_merge( $row, $more_options );

	foreach ( $row['fields'] as $key => $field ) {
		$row['fields'][ $key ] = array_merge( $row['fields'][ $key ], $more_field_options );
	}

	return $row;
}

function buildExistingCustomScreenSetArray( $table_name ) {
	$values = get_option( GIGYA__SETTINGS_SCREENSETS );

	if ( empty( $values[ $table_name ] ) ) {
		return array( buildCustomScreenSetRow( array(), array( 'disabled' => true ) ) );
	} else {
		$rows       = array();
		$total_rows = count( $values[ $table_name ] );
		foreach ( $values[ $table_name ] as $key => $values ) {
			$rows[ $key ] = buildCustomScreenSetRow( $values, ( $total_rows <= 1 ) ? array( 'disabled' => true ) : array() );
		}

		return $rows;
	}
}

/**
 * Form builder for 'Custom Screen-Set Settings' configuration page.
 */
function screenSetSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_SCREENSETS );
	$roles  = get_editable_roles();
	$form   = [];

	$form['raas_txt'] = [
		'markup' => '<small><span>RaaS requires initial configuration in Gigya\'s Admin Console. Screen sets can be defined in the <a class="link-https" target="_blank" rel="external nofollow noopener noreferrer" href="https://console.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://console.gigya.com/site/partners/Settings.aspx#/screen-sets-app/dashboard">UI Builder</a>. The page will display a list of predefined default screen-sets, each with an ID. Click on the "Visual Editor" link next to the screen-set that you want to use, this will open the <a class="external" target="_blank" title="UI Builder" rel="internal" href="https://developers.gigya.com/display/GD/UI+Builder">Visual Editor</a> window. You can modify the screens, or just hit the "Save" button to activate them. Please make sure that the screen-set IDs that are defined below match the IDs of the screen-sets you have configured in the <a class="link-https" target="_blank" rel="external nofollow noopener noreferrer" href="https://console.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://console.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets">UI Builder</a> page.</span></small>',
	];

	$form['raas_screens'] = [
		'markup' => '<h4>' . __( 'Login/Registration Screen Sets' ) . '</h4>',
	];

	$form['raasWebScreen'] = [
		'type'  => 'text',
		'label' => __( 'Web Screen Set ID' ),
		'value' => _gigParam( $values, 'raasWebScreen',
			'Default-RegistrationLogin' ),
	];

	$form['raasMobileScreen'] = [
		'type'  => 'text',
		'label' => __( 'Mobile Screen Set ID' ),
		'value' => _gigParam( $values, 'raasMobileScreen', '' ),
	];

	$form['raasLoginScreen'] = [
		'type'  => 'text',
		'label' => __( 'Login Screen ID' ),
		'value' => _gigParam( $values, 'raasLoginScreen',
			'gigya-login-screen' ),
	];

	$form['raasRegisterScreen'] = [
		'type'  => 'text',
		'label' => __( 'Register Screen ID' ),
		'value' => _gigParam( $values, 'raasRegisterScreen',
			'gigya-register-screen' ),
	];

	$form['raas_profile_screens'] = [
		'markup' => '<h4>' . __( 'Profile Screen Sets' ) . '</h4>',
	];

	$form['raasProfileWebScreen'] = [
		'type'  => 'text',
		'label' => __( 'Web Screen Set ID' ),
		'value' => _gigParam( $values, 'raasProfileWebScreen',
			'Default-ProfileUpdate' ),
	];

	$form['raasProfileMobileScreen'] = [
		'type'  => 'text',
		'label' => __( 'Mobile Screen Set ID' ),
		'value' => _gigParam( $values, 'raasProfileMobileScreen', '' ),
	];

	$form['raas_divs'] = [
		'markup' => '<h4>DIV IDs</h4><small>' . __( 'Specify the DIV IDs in which to embed the screen-sets.' ) . '</small>',
	];

	$form['raasLoginDiv'] = [
		'type'  => 'text',
		'label' => __( 'Login' ),
		'value' => _gigParam( $values, 'raasLoginDiv', 'loginform' ),
	];

	$form['raasRegisterDiv'] = [
		'type'  => 'text',
		'label' => __( 'Register' ),
		'value' => _gigParam( $values, 'raasRegisterDiv', 'registerform' ),
	];

	$form['raasProfileDiv'] = [
		'type'  => 'text',
		'label' => __( 'Profile' ),
		'value' => _gigParam( $values, 'raasProfileDiv', 'profile-page' ),
	];

	$form['raas_screen_sets'] = [
		'markup' => '<h4>Custom Screen-Sets</h4><small>' . __( 'Configure custom screen-sets that will be made available as widgets.' ) . '</small>',
	];

	$table_name                   = 'custom_screen_sets';
	$form['customScreenSetTable'] = [
		'type' => 'table',
		'name' => $table_name,
		'rows' => buildExistingCustomScreenSetArray( $table_name ),
	];

	echo _gigya_form_render( $form, GIGYA__SETTINGS_SCREENSETS );
}
