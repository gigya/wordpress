<?php

use Gigya\CMSKit\GigyaCMS;
use Gigya\PHP\GSException;

/**
 * Form builder for 'User Management Settings' configuration page.
 */
function loginSettingsForm() {
	$values          = get_option( GIGYA__SETTINGS_LOGIN );
	$global_settings = get_option( GIGYA__SETTINGS_GLOBAL );
	$roles           = get_editable_roles();
	$form            = array();

	$form['mode'] = array(
		'type'    => 'radio',
		'options' => array(
			'wp_only' => __( 'WordPress only' ),
			'wp_sl'   => __( 'WordPress + Social Login <small class="gigya-raas-warn hidden">Warning: this site is configured on SAP CDC server to use Registration-as-a-Service. Please contact your SAP CDC account manager for migration instruction.</small>' ),
			'raas'    => __( 'Registration-as-a-Service <small>Selecting this option overrides the WordPress user management system. This requires additional administration steps. Learn more <a href="https://github.com/gigya/wordpress/wiki#user-management-settings">here</a></small>' )
		),
		'value'   => _gigParam( $values, 'mode', 'wp_only' ),
		'class'   => 'raas_disabled'
	);

	// check if raas is enabled, and add the raas_enabled class to the form mode element
	$c = new GigyaCMS();
	try {
		$is_raas = $c->isRaaS();
	}
	catch ( GSException $e ) {
		$is_raas = true;
    
		if ( ! empty( $global_settings ) ) {
			$form['raas_error'] = [
				'markup' => '<div id="setting-error-api_validate" class="error settings-error notice is-dismissible"> 
								<p>
								<strong>' . __( 'Error determining RaaS status. There could be an issue with your machine or SAP CDC account. Please contact support if the problem persists. Message from SAP CDC' ) . ': ' . $e->getMessage() . '.
								For more information please refer to <a href="https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/416d41b170b21014bbc5a10ce4041860.html" target="_blank" rel="noopener noreferrer">Response_Codes_and_Errors</a>.
								</strong>
								</p>
								<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
							</div>',
			];
		}
	}

	if ( $is_raas ) {
		$form['mode']['class'] = 'raas_enabled';
	}

	$form['gl_start'] = array(
			'markup' => '<div class="global-login-wrapper">'
	);


	$form['connectWithoutLoginBehavior'] = array(
			'type'    => 'select',
			'options' => array(
					'alwaysLogin'       => __( 'Always Login' ),
					'loginExistingUser' => __( 'Login Existing User' ),
			),
			'value'   => _gigParam( $values, 'connectWithoutLoginBehavior', 'loginExistingUser' ),
			'label'   => __( 'Connect Without Login Behavior' ),
	);

	$form['redirect'] = array(
			'type'  => 'text',
			'label' => __( 'Post Login Redirect' ),
			'value' => _gigParam( $values, 'redirect', '' ),
			'desc'  => __( 'Provide a URL to which users are redirected after they log-in via SAP Customer Data Cloud. External URLs must include the protocol prefix ( usually: http:// or https:// ).' )
	);

	$form['gl_end'] = array(
			'markup' => '</div>'
	);

	$form['sl_start'] = array(
			'markup' => '<div class="social-login-wrapper">'
	);

	$form['buttonsStyle'] = array(
			'type'    => 'select',
			'options' => array(
					'fullLogo'   => __( 'Full Logo' ),
					'standard'   => __( 'Standard' ),
					'signInWith' => __( 'Sign In With' )
			),
			'value'   => _gigParam( $values, 'buttonsStyle', 'fullLogo' ),
			'label'   => __( 'Button Style' )
	);

	$form['width'] = array(
			'type'  => 'text',
			'label' => __( 'Width' ),
			'value' => _gigParam( $values, 'width', 320 ),
			'desc'  => __( 'The width of the plugin in px' ),
			'class' => 'size'
	);

	$form['height'] = array(
			'type'  => 'text',
			'label' => __( 'Height' ),
			'value' => _gigParam( $values, 'height', 100 ),
			'desc'  => __( 'The height of the plugin in px' ),
			'class' => 'size'
	);

	$form['enabledProviders'] = array(
			'type'  => 'text',
			'label' => __( 'Login Providers' ),
			'value' => _gigParam( $values, 'enabledProviders', '' ),
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook, twitter, google, linkedin' )
	);

	$form['showTermsLink'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Terms Link' ),
			'value' => _gigParam( $values, 'showTermsLink', 0 )
	);

	$form['registerExtra'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Complete Registration Form' ),
			'value' => _gigParam( $values, 'registerExtra', 0 ),
			'desc'  => __( "Check this checkbox if you have defined required fields in you site registration form. When checked a 'Complete Registration' form will pop up during user social registration, to let the user enter the missing required fields" )
	);

	$form['advancedLoginUI'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) LoginUI" ),
			'value' => _gigParam( $values, 'advancedLoginUI', '' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/417916f470b21014bbc5a10ce4041860.html" target="_blank" rel="noopener noreferrer">' . __( 'parameters' ) . '</a>'
	);

	$form['advancedAddConnectionsUI'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
			'value' => _gigParam( $values, 'advancedAddConnectionsUI', '' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="https://help.sap.com/viewer/8b8d6fffe113457094a17701f63e3d6a/GIGYA/en-US/4178f00d70b21014bbc5a10ce4041860.html" target="_blank" rel="noopener noreferrer">' . __( 'parameters' ) . '</a>'
	);

	$form['sl_end'] = array(
		'markup' => '</div>'
	);

	$form['raas_start'] = array(
		'markup' => '<div class="raas-login-wrapper">'
	);

	$form['raasOverrideLinks'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Override WordPress Link' ),
			'desc'  => __( 'When checked, the WordPress default "Login", "Registration" and "Edit Profile" links pop-up RaaS screens instead of WordPress screens.' ),
			'value' => _gigParamDefaultOn( $values, 'raasOverrideLinks' )
	);

	$form['raas_admin_roles_title'] = array(
		'markup' => __('<h4>Admin Login Roles</h4><p>Select below which <a href="https://wordpress.org/support/article/roles-and-capabilities/">Roles</a> should be permitted to login via the default WordPress login UI in /wp-login.php <br/>For more information, please refer to <a href="https://developers.gigya.com/display/GD/Using+RaaS+with+WordPress" target="_blank" rel="noopener noreferrer">Admin Users, Roles and Permissions</a> section in SAP Customer Data Cloud documentation.</p>')
	);

	// create checkbox for each role in site (except admin & super admin)

	// Check/Uncheck all roles
	$form['raas_allowed_admin_checkall'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Check All' ),
		'value' => 0,
		'class' => 'raas_allowed_admin_checkall',
	);
	// create the roles checkboxes
	foreach ( $roles as $role ) {
		if ( $role['name'] != "Administrator" && $role['name'] != "Super Admin" && $role['name'] != "Subscriber" ) {
			$settings_role_name = "raas_allowed_admin_{$role['name']}";
			$form[ $settings_role_name ] = array(
				'type'  => 'checkbox',
				'label' => __( $role['name'] ),
				'value' => _DefaultAdminValue( $values, $role['name'], $settings_role_name ),
				'class' => 'gigya_raas_allowed_admin_roles'
			);
		}
	}

	$form['get_out_of_sync_users'] = array(
		'markup' => ' <div id="generate_report_users_get_out_of_sync">  
			 <h4>Out of Sync Users</h4>
			<input type="button" id="gigya_get_out_of_sync_users" class="button" value="Generate Report" />
			<small>SAP Customer Data Cloud is now able to sync users with WordPress based on UID instead of Email.<br>To locate and generate a report for the first ' . number_format( GIGYA__SYNC_REPORT_MAX_USERS ) . ' users (maximum) that were not previously synced between SAP Customer Data Cloud and WordPress, <br> use the Generate Report button above.</small></div>',
		'desc'   => __( 'Generate a report of users that were not synced between SAP Customer Data Cloud and WordPress.' )
	);

	$form['login_verification_mode'] = array(
		'label'   => '<h4>Login Verification Mode</h4>',
		'type'    => 'radio',
		'options' => array(
			'uid_and_email' => __( 'Using SAP CDC UID and email address (may cause multiple users) ' ),
			'uid_only'      => __( 'Using SAP Customer Data Cloud UID (recommended)' ),
		),
		'value'   => _gigParam( $values, 'login_verification_mode', 'uid_and_email' )
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

	$form['raas_end'] = array(
			'markup' => '</div>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_LOGIN );
}
