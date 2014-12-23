<?php
/**
 * Form builder for 'Social Login Settings' configuration page.
 */
function loginSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_LOGIN );
	$roles = get_editable_roles();
	$form   = array();

	$form['mode'] = array(
			'type'    => 'radio',
			'options' => array(
					'wp_only' => __( 'WordPress only' ),
					'wp_sl'   => __( 'WordPress + Social Login <small class="gigya-raas-warn hidden">Warning: this site is configured on Gigya server to use Registration-as-a-Service. Please contact your Gigya account manager for migration instruction.</small>' ),
					'raas'    => __( 'Registration-as-a-Service <small>Selecting this option overrides the WordPress user management system. This requires additional administration steps. Learn more <a href="http://developers.gigya.com//015_Partners/030_CMS_and_Ecommerce_Platforms/030_Wordpress_Plugin/020_RaaS">here</a></small>' )
			),
			'value'   => _gigParam( $values, 'mode', 'wp_only' ),
			'class'   => 'raas_disabled'
	);

	// check if raas is enabled, and add the raas_enabled class to the form mode element
	$c       = new GigyaCMS();
//	$is_raas = $c->isRaaS();
	$is_raas = $c->isRaaNotIds($values);

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
			'desc'  => __( 'Provide a URL to which users are redirected after they log-in via Gigya. External URLs must include the protocol prefix ( usually: http:// or https:// ).' )
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
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/030_Gigya_Socialize_API_2.0/030_API_reference/010_Client_API_%28JavaScript%29/Social_service/Socialize.showLoginUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	$form['advancedAddConnectionsUI'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
			'value' => _gigParam( $values, 'advancedAddConnectionsUI', '' ),
			'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/020_Methods/socialize.showAddConnectionsUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	$form['map_social_title'] = array(
		'markup' => __('<h4>Mapping Gigya User Fields to WordPress Fields</h4><p>Define which fields to map from Gigya to WordPress. The WordPress mapped target fields will be populated with data copied from the corresponding source fields. Learn more <a href="http://developers.gigya.com/015_Partners/030_CMS_and_Ecommerce_Platforms/030_Wordpress_Plugin#User_Management_Settings" target="_blank"/>here</a></p>')
	);

	$form['map_social_first_name'] = array(
		'type'  => 'checkbox',
		'label' => __( 'First Name' ),
		'value' => _gigParam( $values, 'map_social_first_name', 1 )
	);
	$form['map_social_last_name'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Last Name' ),
		'value' => _gigParam( $values, 'map_social_last_name', 1 )
	);
	$form['map_social_display_name'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Display Name' ),
		'value' => _gigParam( $values, 'map_social_display_name', 1 )
	);
	$form['map_social_nickname'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Nickname' ),
		'value' => _gigParam( $values, 'map_social_nickname', 1 )
	);
	$form['map_social_profile_image'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Profile Image (avatar)' ),
		'value' => _gigParam( $values, 'map_social_profile_image', 1 )
	);
	$form['map_social_description'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Biographical Info' ),
		'value' => _gigParam( $values, 'map_social_description', 1 )
	);

	$form['sl_end'] = array(
			'markup' => '</div>'
	);

	$form['raas_start'] = array(
			'markup' => '<div class="raas-login-wrapper">'
	);

	$form['raas_txt'] = array(
			'markup' => '<h4>Registration-as-a-Service Settings</h4><small><span>RaaS requires initial configuration in Gigya\'s Admin Console. Screen sets can be defined in the <a class="link-https" target="_blank" rel="external nofollow" href="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets">UI Builder</a>. The page will display a list of predefined default screen-sets, each with an ID. Click on the "Visual Editor" link next to the screen-set that you want to use, this will open the <a class="external" target="_blank" title="010_Developer_Guide/10_UM360/040_Raas/020_UI_Builder#Visual_Editor" rel="internal" href="http://dev-wiki.gigya.com/010_Developer_Guide/10_UM360/040_Raas/020_UI_Builder#Visual_Editor">Visual Editor</a> window. You can modify the screens, or just hit the "Save" button to activate them. Please make sure that the screen-set IDs that are defined below match the IDs of the screen-sets you have configured in the <a class="link-https" target="_blank" rel="external nofollow" href="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets">UI Builder</a> page.</span></small>'
	);

	$form['raas_screens'] = array(
			'markup' => '<h4>Login/Registration Screen Sets</h4>'
	);

	$form['raasWebScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Web Screen Set ID' ),
			'value' => _gigParam( $values, 'raasWebScreen', 'Default-RegistrationLogin' )
	);

	$form['raasMobileScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Mobile Screen Set ID' ),
			'value' => _gigParam( $values, 'raasMobileScreen', 'DefaultMobile-RegistrationLogin' )
	);

	$form['raasLoginScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Login Screen ID' ),
			'value' => _gigParam( $values, 'raasLoginScreen', 'gigya-login-screen' )
	);

	$form['raasRegisterScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Register Screen ID' ),
			'value' => _gigParam( $values, 'raasRegisterScreen', 'gigya-register-screen' )
	);

	$form['raas_profile_screens'] = array(
			'markup' => '<h4>Profile Screen Sets</h4>'
	);

	$form['raasProfileWebScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Web Screen Set ID' ),
			'value' => _gigParam( $values, 'raasProfileWebScreen', 'Default-ProfileUpdate' )
	);

	$form['raasProfileMobileScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Mobile Screen Set ID' ),
			'value' => _gigParam( $values, 'raasProfileMobileScreen', 'DefaultMobile-ProfileUpdate' )
	);

	$form['raasOverrideLinks'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Override WordPress Link' ),
			'desc'  => __( 'When checked, the WordPress default "Login", "Registration" and "Edit Profile" links pop-up RaaS screens instead of WordPress screens.' ),
			'value' => _gigParamDefaultOn( $values, 'raasOverrideLinks' )
	);

	$form['raas_divs'] = array(
			'markup' => '<h4>DIV IDs</h4><small>' . __( 'Specify the DIV IDs in which to embed the screen-sets.' ) . '</small>'
	);

	$form['raasLoginDiv'] = array(
			'type'  => 'text',
			'label' => __( 'Login' ),
			'value' => _gigParam( $values, 'raasLoginDiv', 'loginform' )
	);

	$form['raasRegisterDiv'] = array(
			'type'  => 'text',
			'label' => __( 'Register' ),
			'value' => _gigParam( $values, 'raasRegisterDiv', 'registerform' )
	);

	$form['raasProfileDiv'] = array(
			'type'  => 'text',
			'label' => __( 'Profile' ),
			'value' => _gigParam( $values, 'raasProfileDiv', 'profile-page' )
	);

	$form['map_rass_title'] = array(
		'markup' => __('<h4>Mapping Gigya User Fields to WordPress Fields</h4><p>Define which fields to map from Gigya to WordPress. The WordPress mapped target fields will be populated with data copied from the corresponding source fields. Learn more <a href="http://developers.gigya.com/015_Partners/030_CMS_and_Ecommerce_Platforms/030_Wordpress_Plugin#User_Management_Settings" target="_blank"/>here</a></p>')
	);

	$form['map_raas_first_name'] = array(
		'type'  => 'checkbox',
		'label' => __( 'First Name' ),
		'value' => _gigParam( $values, 'map_raas_first_name', 1 )
	);

	$form['map_raas_last_name'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Last Name' ),
		'value' => _gigParam( $values, 'map_raas_last_name', 1 )
	);

	$form['map_raas_nickname'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Nickname' ),
		'value' => _gigParam( $values, 'map_raas_nickname', 1 )
	);

	$form['map_raas_profile_image'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Profile image (avatar)' ),
		'value' => _gigParam( $values, 'map_raas_profile_image', 1 )
	);

	$form['map_raas_description'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Biographical Info' ),
		'value' => _gigParam( $values, 'map_raas_description', 1 )
	);

	$form['raas_admin_roles_title'] = array(
		'markup' => __('<h4>Admin Login Roles</h4><p>Select below which <a target="_blank" href=http://codex.wordpress.org/Roles_and_Capabilities#Roles>Roles</a> should be permitted to login via the default WordPress login UI in /wp-login.php <br/>For more information, please refer to <a href="http://developers.gigya.com/015_Partners/030_CMS_and_Ecommerce_Platforms/030_Wordpress_Plugin/020_RaaS#Admin_Users.2C_Roles_.26_Permissions" target="_blank">Users, Roles & Permissions</a> section in Gigya documentation.</p>')
	);

	// create checkbox for each role in site (except admin & super admin)

	// Check/Uncheck all roles
	$form['raas_allowed_admin_checkall'] = array(
		'type'  => 'checkbox',
		'label' => __( 'Check All' ),
		'value' => 0,
		'class' => 'raas_allowed_admin_checkall'
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

	$form['raas_end'] = array(
			'markup' => '</div>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_LOGIN );
}
