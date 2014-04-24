<?php
/**
 * Form builder for 'Social Login Settings' configuration page.
 */
function loginSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_LOGIN );
	$form   = array();

	$form['mode'] = array(
			'type'    => 'radio',
			'options' => array(
					'wp_only' => __( 'Wordpress only' ),
					'wp_sl'   => __( 'Wordpress + Social Login <small class="gigya-raas-warn hidden">Warning: this site is configured on Gigya server to use Registration-as-a-Service. Please contact your Gigya account manager for migration instruction.</small>' ),
					'raas'    => __( 'Registration-as-a-Service <small>Selecting this option overrides Drupal\'s user management system. This requires additional administration steps. Learn more here</small>' )
			),
			'value'   => _gigParam( $values['mode'], 'wp_only' ),
			'class'   => 'raas_disabled'
	);

	$c       = new GigyaCMS();
	$is_raas = $c->isRaaS();
	if ( ! empty( $is_raas ) ) {
		$form['mode']['class'] = 'raas_enabled';
	}

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
			'value'   => _gigParam( $values['buttonsStyle'], 'fullLogo' ),
			'label'   => __( 'Button Style' )
	);

	$form['connectWithoutLoginBehavior'] = array(
			'type'    => 'select',
			'options' => array(
					'alwaysLogin'       => __( 'Always Login' ),
					'loginExistingUser' => __( 'Login Existing User' ),
			),
			'value'   => _gigParam( $values['connectWithoutLoginBehavior'], 'loginExistingUser' ),
			'label'   => __( 'Connect Without Login Behavior' ),
	);

	$form['width'] = array(
			'type'  => 'text',
			'label' => __( 'Width' ),
			'value' => _gigParam( $values['width'], 320 ),
			'desc'  => __( 'The width of the plugin in px' ),
			'class' => 'size'
	);

	$form['height'] = array(
			'type'  => 'text',
			'label' => __( 'Height' ),
			'value' => _gigParam( $values['height'], 100 ),
			'desc'  => __( 'The height of the plugin in px' ),
			'class' => 'size'
	);

	$form['redirect'] = array(
			'type'  => 'text',
			'label' => __( 'Post Login Redirect' ),
			'value' => _gigParam( $values['redirect'], '' ),
			'desc'  => __( 'Provide a URL to redirect users after they logged-in via Gigya social login' )
	);

	$form['enabledProviders'] = array(
			'type'  => 'text',
			'label' => __( 'Login Providers' ),
			'value' => _gigParam( $values['enabledProviders'], '' ),
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook, twitter, google, linkedin' )
	);

	$form['showTermsLink'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Terms Link' ),
			'value' => _gigParam( $values['showTermsLink'], 0 )
	);

	$form['registerExtra'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Complete Registration Form' ),
			'value' => _gigParam( $values['registerExtra'], 0 ),
			'desc'  => __( "Check this checkbox if you have defined required fields in you site registration form. When checked a 'Complete Registration' form will pop up during user social registration, to let the user enter the missing required fields" )
	);

	$form['advancedLoginUI'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) LoginUI" ),
			'value' => _gigParam( $values['advancedLoginUI'], '' ),
			'desc'  => __( 'Enter valid JSON format. See list of available' ) . ' <a htef="http://developers.gigya.com/030_Gigya_Socialize_API_2.0/030_API_reference/010_Client_API_%28JavaScript%29/Social_service/Socialize.showLoginUI">' . __( 'parameters' ) . '</a>'
	);

	$form['advancedAddConnectionsUI'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
			'value' => _gigParam( $values['advancedAddConnectionsUI'], '' ),
			'desc'  => __( 'Enter valid JSON format. See list of available' ) . ' <a htef="http://developers.gigya.com/020_Client_API/020_Methods/socialize.showAddConnectionsUI">' . __( 'parameters' ) . '</a>'
	);

	$form['sl_end'] = array(
			'markup' => '</div>'
	);

	$form['raas_start'] = array(
			'markup' => '<div class="raas-login-wrapper">'
	);

	$form['raas_txt'] = array(
			'markup' => '<h4>Registration-as-a-Service Settings</h4><small><span>Please make sure to initially configure RaaS in Gigya\'s site. Go to the <a class="link-https" target="_blank" rel="external nofollow" href="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets">UI Builder</a> page after logging in to Gigya\'s site. The page presents the list of predefined default screen-sets, each has an ID. Click on the "Visual Editor" link next to a screen-set that you wish to use. This will open the <a class="external" target="_blank" title="010_Developer_Guide/10_UM360/040_Raas/020_UI_Builder#Visual_Editor" rel="internal" href="http://dev-wiki.gigya.com/010_Developer_Guide/10_UM360/040_Raas/020_UI_Builder#Visual_Editor">Visual Editor</a> window. You can modify the screens, or just hit the "Save" button to activate it. Please make sure that the screen-sets IDs that are defined below match the IDs of the screen-sets you have configured in the <a class="link-https" target="_blank" rel="external nofollow" href="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets" title="https://platform.gigya.com/site/partners/Settings.aspx#cmd%3DUserManagement360.ScreenSets">UI Builder</a> page.</span></small>'
	);

	$form['raas_screens'] = array(
			'markup' => '<h4>Login/Registration Screen Sets</h4>'
	);

	$form['raasWebScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Web Screen Set ID' ),
			'value' => _gigParam( $values['raasWebScreen'], 'Login-web' )
	);

	$form['raasMobileScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Mobile Screen Set ID' ),
			'value' => _gigParam( $values['raasMobileScreen'], 'Mobile-login' )
	);

	$form['raasLoginScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Login Screen ID' ),
			'value' => _gigParam( $values['raasLoginScreen'], 'gigya-login-screen' )
	);

	$form['raasRegisterScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Register Screen ID' ),
			'value' => _gigParam( $values['raasRegisterScreen'], 'gigya-register-screen' )
	);

	$form['raas_profile_screens'] = array(
			'markup' => '<h4>Profile Screen Sets</h4>'
	);

	$form['raasProfileWebScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Web Screen Set ID' ),
			'value' => _gigParam( $values['raasProfileWebScreen'], 'Profile-web' )
	);

	$form['raasProfileMobileScreen'] = array(
			'type'  => 'text',
			'label' => __( 'Mobile Screen Set ID' ),
			'value' => _gigParam( $values['raasProfileMobileScreen'], 'Profile-mobile' )
	);

	$form['raasOverrideLinks'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Override Wordpress Link' ),
			'desc'  => __( 'Checking this checkbox, WordPress\'s default "Login", "Registration" and "Edit Profile" links will pop-up RaaS\'s respective screens instead of redirecting to the WordPress screens.' ),
			'value' => $values['raasOverrideLinks'] === '0' ? '0' : '1'
	);

	$form['raas_divs'] = array(
			'markup' => '<h4>DIV IDs</h4><small>' . __( 'Specify the DIV IDs in which to embed the screen-sets. If the IDs are empty (default), then the screen-set would show as pop-up dialogs' ) . '<small>'
	);

	$form['raasLoginDiv'] = array(
			'type'  => 'text',
			'label' => __( 'Login' ),
			'value' => _gigParam( $values['raasLoginDiv'], 'loginform' )
	);

	$form['raasRegisterDiv'] = array(
			'type'  => 'text',
			'label' => __( 'Register' ),
			'value' => _gigParam( $values['raasRegisterDiv'], 'registerform' )
	);

	$form['raasProfileDiv'] = array(
			'type'  => 'text',
			'label' => __( 'Profile' ),
			'value' => _gigParam( $values['raasProfileDiv'], 'profile-page' )
	);

	$form['raas_end'] = array(
			'markup' => '</div>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_LOGIN );
}