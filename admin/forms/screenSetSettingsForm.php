<?php

use Gigya\CMSKit\GigyaCMS;
use Gigya\WordPress\GigyaLogger;


function buildCustomScreenSetRow( $screenSetList, $values = array(), $more_options = array(), $more_field_options = array() ) {
	$more_field_options   = array_values( $more_field_options );
	$desktop_list         = $screenSetList;
	$mobile_list          = $screenSetList;
	$desktop_value_exists = ! empty( $values['desktop'] );
	$mobile_value_exists  = ! empty( $values['mobile'] );

	array_unshift( $mobile_list, array(
		'label' => __( 'Use Desktop Screen-Set' ),
		'attrs' => array( 'value' => 'desktop' )
	), array(
		'label' => str_repeat( '_', 3 + max( array_map( 'strlen', array_column( $mobile_list, 'label' ) ) ) ),
		'attrs' => array( 'disabled' => 'disabled' )
	) );

	if ( ! $desktop_value_exists and ! $mobile_value_exists ) {
		$screen_set_exists_desktop = true;
		$screen_set_exists_mobile  = true;
		array_unshift( $desktop_list, array(
			'label' => '',
			'attrs' => array( 'hidden' => '' )
		) );
		array_unshift( $mobile_list, array(
			'label' => '',
			'attrs' => array( 'hidden' => '' )
		) );

	} else {
		$screen_set_exists_desktop = in_array( $values['desktop'], array_column( $desktop_list, 'label' ) ) || empty( $values['desktop'] );
		$screen_set_exists_mobile  = in_array( $values['mobile'], array_column( $mobile_list, 'label' ) ) || empty( $values['mobile'] ) || $values['mobile'] == 'desktop';

		if ( ! $screen_set_exists_desktop ) {
			array_unshift( $desktop_list, array(
				'label' => $values['desktop'],
				'attrs' => array( 'class' => 'invalid-gigya-screen-set-option' )
			) );
		}
		if ( ! $screen_set_exists_mobile ) {
			array_unshift( $mobile_list, array(
				'label' => $values['mobile'],
				'attrs' => array( 'class' => "invalid-gigya-screen-set-option" )
			) );
		}
	};

	$row = [
		'type'   => 'dynamic_field_line',
		'fields' => [
			[ /* Desktop screen-set */
				'type'     => 'select',
				'name'     => 'desktop',
				'label'    => __( 'Desktop Screen-Set' ),
				'value'    => ( ( $desktop_value_exists ) ? $values['desktop'] : '' ),
				'options'  => $desktop_list,
				'required' => 'empty-selection',
				'attrs'    => array(
					'class'       => 'custom-screen-set-select-width ',
					'data-exists' => ( ( ! $screen_set_exists_desktop ) ? 'false' : 'true' )
				)
			],
			[ /* Mobile screen-set */
				'type'    => 'select',
				'name'    => 'mobile',
				'label'   => __( 'Mobile Screen-Set' ),
				'value'   => ( ( $mobile_value_exists ) ? $values['mobile'] : ( ( $desktop_value_exists ) ? 'desktop' : '' ) ),
				'options' => $mobile_list,
				'attrs'   => array(
					'class'       => 'custom-screen-set-select-width ',
					'data-exists' => ( ( ! $screen_set_exists_mobile ) ? 'false' : 'true' )
				)
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
	$logger = new GigyaLogger();
	$values = get_option( GIGYA__SETTINGS_SCREENSETS );
	$form   = [];

	$form['raas_txt'] = [
		'markup' => '<small><span>RaaS requires initial configuration in SAP Customer Data Cloud\'s Admin Console. Screen sets can be defined in the
<a class="link-https" target="_blank" rel="external nofollow noopener noreferrer" href="https://console.gigya.com/site/partners/Settings.aspx#/screen-sets-app/web/dashboard" title="UI Builder">UI Builder</a>.
The page will display a list of predefined default screen-sets, each with an ID.
Click on the "Visual Editor" link next to the screen-set that you want to use, this will open the
<a class="external" target="_blank" title="UI Builder" rel="internal" href="https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/417d11df70b21014bbc5a10ce4041860.html">Visual Editor</a>
window.
You can modify the screens, or just hit the "Save" button to activate them.
Please make sure that the screen-set IDs that are defined below match the IDs of the screen-sets you have configured in the
<a class="link-https" target="_blank" rel="external nofollow noopener noreferrer" href="https://console.gigya.com/site/partners/Settings.aspx#/screen-sets-app/web/dashboard" title="UI Builder">UI Builder</a>
page.</span></small>',
	];

	$form['raas_screens'] = [
		'markup' => '<h4>' . __( 'Login/Registration Screen-Sets' ) . '</h4>',
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

	$gigya_cms = new GigyaCMS();

	$screenset_list = $gigya_cms->getScreenSetsIdList();

	if ( $screenset_list ) {
		if ( ! empty( $values['custom_screen_sets'] ) ) {
			foreach ( $values['custom_screen_sets'] as $key => $value ) {
				$desktop_screen_exist = in_array( $value['desktop'], array_column( $screenset_list, 'label' ) );
				$mobile_screen_exist  = in_array( $value['mobile'], array_column( $screenset_list, 'label' ) ) || $value['mobile'] === 'desktop' || empty( $value['mobile'] );
				if ( ( ( ! $desktop_screen_exist ) || ( ! $mobile_screen_exist ) ) && ! empty( ( $value['desktop'] ) && ! empty( $value['mobile'] ) ) ) {

					$compare_error = array(
						'error_message' => __( 'One or more of the screen-Sets below does not exist at SAP Customer Data Cloud.' ),
						'attrs'         => array( 'class' => 'notice notice-error is-dismissible' )
					);
					echo _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $compare_error );

					break;
				}
			};
		}

		$table_name                   = 'custom_screen_sets';
		$form['customScreenSetTable'] = [
			'type' => 'table',
			'name' => $table_name,
			'rows' => buildExistingCustomScreenSetArray( $table_name, $screenset_list ),
		];
	} else {
		$first_line       = __( 'Error retrieving custom screen-set list from SAP Customer Data Cloud. It will not be possible to embed CDC screen-sets on your website. Please make sure that the <a href="?page=gigya_global_settings">Global Configuration</a> has been set correctly. If the problem persists, please contact SAP CDC support.' );
		$second_line      = __( 'Check the error log for more details.' );
		$connection_error = array(
			'error_message' => array(),
			'attrs'         => array( 'class' => 'notice notice-error is-dismissible' )
		);

		if ( $screenset_list !== false ) {
			$logger->error( 'The current site has no screen-sets defined. Either the screen-sets are at the parent site level, or they have not been initialized. Check Screen-Set settings in the SAP CDC console.' );
		}

		$connection_error['error_message'] = array( $first_line, $second_line );

		echo _gigya_render_tpl( 'admin/tpl/error-message.tpl.php', $connection_error );
	}

	echo _gigya_form_render( $form, GIGYA__SETTINGS_SCREENSETS );
}
