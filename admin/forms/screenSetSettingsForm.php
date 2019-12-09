<?php
function buildCustomScreenSetRow( $screenSetList, $values = array(), $more_options = array(), $more_field_options = array() ) {
	$more_field_options = array_values( $more_field_options );
	$desktop_list       = $screenSetList;
	$mobile_list        = $screenSetList;
	array_unshift( $mobile_list, array( 'label' => 'Use Desktop Screen-Set' ), array(
		'label' => '____________________________________',
		'attrs' => array( 'disabled' => 'disabled' )
	) );
	$screen_set_exists_desktop = in_array( $values['desktop'], array_column( $desktop_list, 'label' ) ) || empty( $values['desktop'] );
	$screen_set_exists_mobile  = in_array( $values['mobile'], array_column( $mobile_list, 'label' ) ) || empty( $values['mobile'] );
	$desktop_markup     = '';
	$mobile_markup      = '';
	$screen_set_error_markup = '<h6 id="setting-error-api_validate" class="error notice settings-error notice is-dismissible style" style = "border-left-color : #dc3232 "> 
							<p>
							<strong>' . __( 'Screen-Set does not exist' ) . ' </strong>
							</p>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
						</h6>';


	if ( ! $screen_set_exists_desktop ) {
		$desktop_markup = $screen_set_error_markup;
		array_unshift( $desktop_list, array(
			'label' => $values['desktop'],
			'attrs' => array( 'style' => "color: red !important" )
		) );
	}

	if ( ! $screen_set_exists_mobile ) {
		$mobile_markup = $screen_set_error_markup;
		array_unshift( $mobile_list, array(
			'label' => $values['mobile'],
			'attrs' => array( 'style' => "color: red !important" )
		) );
	};

	$row = [
		'type'   => 'dynamic_field_line',
		'fields' => [
			[ /* Desktop screen-set */
				'type'     => 'select',
				'name'     => 'desktop',
				'label'    => __( 'Desktop Screen-Set' ),
				'value'    => ( ( ! empty( $values['desktop'] ) ) ? $values['desktop'] : '' ),
				'options'  => $desktop_list,
				'required' => 'empty-selection',
				'markup'   => $desktop_markup,
				'attrs'    => array( 'class' => 'custom-screen-set-select-width ' . ( ( $screen_set_exists_desktop ) ? null : ' gigya-wp-field-error' ) ),
			],
			[ /* Mobile screen-set */
				'type'    => 'select',
				'name'    => 'mobile',
				'label'   => __( 'Mobile Screen-Set' ),
				'value'   => ( ( ! empty( $values['mobile'] ) ) ? $values['mobile'] : '' ),
				'options' => $mobile_list,
				'markup'  => $mobile_markup,
				'attrs'   => array( 'class' => 'custom-screen-set-select-width ' . ( ( $screen_set_exists_mobile ) ? null : ' gigya-wp-field-error' ) ),

			],
			[
				'type'  => 'checkbox',
				'name'  => 'is_sync',
				'label' => 'Sync Data?',
				'value' => ( ! empty( $values['is_sync'] ) ) ? $values['is_sync'] : 0,
			],
		],
	];

	$row = array_merge( $row, $more_options );

	foreach ( $row['fields'] as $key => $field ) {
		$row['fields'][ $key ] = array_merge( $row['fields'][ $key ], $more_field_options );
	}

	return $row;
}

function buildExistingCustomScreenSetArray( $table_name, $screen_set_list ) {
	$values = get_option( GIGYA__SETTINGS_SCREENSETS );

	if ( empty( $values[ $table_name ] ) ) {
		return array( buildCustomScreenSetRow( $screen_set_list, array(), array( 'disabled' => true ) ) );
	} else {
		$rows       = array();
		$total_rows = count( $values[ $table_name ] );

		foreach ( $values[ $table_name ] as $key => $values ) {
			$rows[ $key ] = buildCustomScreenSetRow( $screen_set_list, $values, ( $total_rows <= 1 ) ? array( 'disabled' => true ) : array() );

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
		'markup' => '<small><span>RaaS requires initial configuration in SAP Customer Data Cloud\'s Admin Console. Screen sets can be defined in the <a class="link-https" target="_blank" rel="external nofollow noopener noreferrer" href="https://console.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://console.gigya.com/site/partners/Settings.aspx#/screen-sets-app/dashboard">UI Builder</a>. The page will display a list of predefined default screen-sets, each with an ID. Click on the "Visual Editor" link next to the screen-set that you want to use, this will open the <a class="external" target="_blank" title="UI Builder" rel="internal" href="https://developers.gigya.com/display/GD/UI+Builder">Visual Editor</a> window. You can modify the screens, or just hit the "Save" button to activate them. Please make sure that the screen-set IDs that are defined below match the IDs of the screen-sets you have configured in the <a class="link-https" target="_blank" rel="external nofollow noopener noreferrer" href="https://console.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://console.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets">UI Builder</a> page.</span></small>',
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

	$gigya_cms      = new GigyaCMS();
	$screenset_list = $gigya_cms->getScreenSetsIDList();

	if ( $screenset_list ) {
		$valid_screen_sets = true;
		foreach ( $values['custom_screen_sets'] as $key => $value ) {
			$desktop_screen_exist = in_array( $value['desktop'], array_column( $screenset_list, 'label' ) );
			$mobile_screen_exist  = in_array( $value['mobile'], array_column( $screenset_list, 'label' ) ) || $value['mobile'] === "Use Desktop Screen-Set" || empty( $value['mobile'] );
			$valid_screen_sets    = ( $desktop_screen_exist && $mobile_screen_exist );
			if ( ! $valid_screen_sets ) {
				break;
			}
		};


		$form['screens_sets_error'] = [
			'markup' => ( $valid_screen_sets ) ? null : '<div id="setting-error-api_validate" class="error notice settings-error notice is-dismissible style" style = "border-left-color : #dc3232 "> 
							<p>
							<strong>' . __( 'One or more Screen-Set not existing in SAP CDC Database' ) . ' </strong>
							</p>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
						</div>',
		];

		$table_name                   = 'custom_screen_sets';
		$form['customScreenSetTable'] = [
			'type' => 'table',
			'name' => $table_name,
			'rows' => buildExistingCustomScreenSetArray( $table_name, $screenset_list ),
		];

	} else {
		if ( WP_DEBUG ) {
			$form['screens_sets_error'] = [
				'markup' => '<div id="setting-error-api_validate" class="error settings-error notice is-dismissible"> 
							<p>
							<strong>' . __( 'Error retrieving custom screen-set list from SAP Customer Data Cloud. It will not be possible to embed CDC screen-sets on your website. Please contact support if the problem persists.' ) . ' </strong>
							<br> ' . __( 'Check the error log for more details.' ) . '
							</p>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
						</div>',
			];

		} else {
			$form['screens_sets_error'] = [
				'markup' => '<div id="setting-error-api_validate" class="error settings-error notice is-dismissible"> 
							<p>
							<strong>' . __( 'Error retrieving custom screen-set list from SAP Customer Data Cloud. It will not be possible to embed CDC screen-sets on your website. Please contact support if the problem persists.' ) . ' </strong>
							</p>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
						</div>',
			];
		};
	}


	echo _gigya_form_render( $form, GIGYA__SETTINGS_SCREENSETS );
}
