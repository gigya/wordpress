<?php
function buildCustomScreenSetRow( $screenSetList, $values = array(), $more_options = array(), $more_field_options = array() ) {
	$more_field_options = array_values( $more_field_options );
	$desktop_list       = $screenSetList;
	$mobile_list        = $screenSetList;
	$desktop_error      = array();
	$mobile_error       = array();
	array_unshift( $mobile_list, array(
		'label' => 'Use Desktop Screen-Set',
		'attrs' => array( 'value' => 'desktop' )
	), array(
		'label' => str_repeat( '_', 3 + max( array_map( 'strlen', array_column( $mobile_list, 'label' ) ) ) ),
		'attrs' => array( 'disabled' => 'disabled' )
	) );
	$specific_desktop_list     = $desktop_list;
	$specific_mobile_list      = $mobile_list;
	$screen_set_exists_desktop = in_array( $values['desktop'], array_column( $desktop_list, 'label' ) ) || empty( $values['desktop'] );
	$screen_set_exists_mobile  = in_array( $values['mobile'], array_column( $mobile_list, 'label' ) ) || empty( $values['mobile'] ) || $values['mobile'] == 'desktop';

	$screen_set_error = array(
		'error_message' => 'Screen-Set does not exist',
		'attrs'         => array( 'class' => 'gigya-error-message-notice-div' )
	);


	if ( ! $screen_set_exists_desktop ) {
		$desktop_error = $screen_set_error;
		array_unshift( $specific_desktop_list, array(
			'label' => $values['desktop'],
			'attrs' => array( 'class' => 'invalid-gigya-Screen-Set-option', 'data-exists' => 'false' )
		) );
	}

	if ( ! $screen_set_exists_mobile ) {
		$mobile_error = $screen_set_error;
		array_unshift( $specific_mobile_list, array(
			'label' => $values['mobile'],
			'attrs' => array( 'class' => "invalid-gigya-Screen-Set-option", 'data-exists' => 'false' )
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
				'options'  => $specific_desktop_list,
				'required' => 'empty-selection',
				'error'    => ( ( empty( $desktop_error ) ) ? '' : _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $desktop_error ) ),
				'attrs'    => array( 'class' => 'custom-screen-set-select-width ' . ( ( $screen_set_exists_desktop ) ? null : ' gigya-wp-field-error' ) ),
			],
			[ /* Mobile screen-set */
				'type'    => 'select',
				'name'    => 'mobile',
				'label'   => __( 'Mobile Screen-Set' ),
				'value'   => ( ( ! empty( $values['mobile'] ) ) ? $values['mobile'] : 'desktop' ),
				'options' => $specific_mobile_list,
				'error'   => ( ( empty( $mobile_error ) ) ? '' : _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $mobile_error ) ),
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
	$screenset_list = $gigya_cms->getScreenSetsIdList();

	if ( $screenset_list ) {
		foreach ( $values['custom_screen_sets'] as $key => $value ) {
			$desktop_screen_exist = in_array( $value['desktop'], array_column( $screenset_list, 'label' ) );
			$mobile_screen_exist  = in_array( $value['mobile'], array_column( $screenset_list, 'label' ) ) || $value['mobile'] === 'desktop' || empty( $value['mobile'] );
			if ( ( ! $desktop_screen_exist ) || ( ! $mobile_screen_exist ) ) {
				$compare_error = array(
					'error_message' => 'One or more Screen-Set not existing in SAP CDC Database',
					'attrs'         => array( 'class' => 'notice notice-error is-dismissible' )
				);
				echo _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $compare_error );

				break;
			}
		};


		$table_name                   = 'custom_screen_sets';
		$form['customScreenSetTable'] = [
			'type' => 'table',
			'name' => $table_name,
			'rows' => buildExistingCustomScreenSetArray( $table_name, $screenset_list ),
		];

	} else {
		$connection_error = array(
			'error_message' => array(),
			'attrs'         => array( 'class' => 'notice notice-error is-dismissible' )
		);

		if ( get_option( GIGYA__SETTINGS_GLOBAL )['debug'] ) {

			$first_line                        = 'Error retrieving custom screen-set list from SAP Customer Data Cloud. It will not be possible to embed CDC screen-sets on your website. Please contact support if the problem persists';
			$second_line                       = 'Check the error log for more details';
			$connection_error['error_message'] = array( $first_line, $second_line );

		} else {
			$message                           = 'Error retrieving custom screen-set list from SAP Customer Data Cloud. It will not be possible to embed CDC screen-sets on your website. Please contact support if the problem persists.';
			$connection_error['error_message'] = array( $message );
		};
		echo _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $connection_error );
	}


	echo _gigya_form_render( $form, GIGYA__SETTINGS_SCREENSETS );
}
