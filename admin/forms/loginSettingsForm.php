<?php
/**
 * Form builder for 'Social Login Settings' configuration page.
 */
function loginSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_LOGIN );
	$form   = array();

	$form['mode'] = array(
			'type'    => 'radio',
			'label'   => __( 'Login Mode' ),
			'options' => array(
					'wp_only' => __( 'Wordpress only' ),
					'wp_sl'   => __( 'Wordpress + Social Login' ),
					'raas'    => __( 'Registration-as-a-Service' )
			),
			'desc'    => __( 'By activate Gigya\'s Social Login, you also activate "Anyone can register" option on Wordpress General Settings page<br>' .
					'By activate Gigya\'s RaaS Login, you also deactivate "Anyone can register" option on Wordpress General Settings page' ),
			'value'   => _gigParam( $values['mode'], 'wp_only' )
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
			'value' => _gigParam( $values['width'], 200 ),
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
			'desc'  => __( 'Enter validate JSON format' )
	);

	$form['advancedAddConnectionsUI'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
			'value' => _gigParam( $values['advancedAddConnectionsUI'], '' ),
			'desc'  => __( 'Enter validate JSON format' )
	);

	$form['sl_end'] = array(
			'markup' => '</div>'
	);

	$form['raas_start'] = array(
			'markup' => '<div class="raas-login-wrapper">'
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
			'label' => __( 'Override Wordpress Links' ),
			'desc'  => __( "Checking this checkbox, WordPress's default 'Login', 'Registration' and 'Edit Profile' links will pop-up RaaS's respective screens instead of redirecting to the WordPress screens" ),
			'value' => _gigParam( $values['raasOverrideLinks'], 1 )
	);

	$form['raas_divs'] = array(
			'markup' => __( 'DIV IDs' ) . '<br>' .
					'<small>' . __( 'Specify the DIV IDs in which to embed the screen-sets. If the IDs are empty (default), then the screen-set would show as pop-up dialogs' ) . '<small>'
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