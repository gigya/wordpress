<?php
/**
 * Form builder for 'Social Login Settings' configuration page.
 */
function loginSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_LOGIN );
	$form   = array();

	$mode_opts = array(
			'wp_only' => __( 'Wordpress only' ),
			'wp_sl'   => __( 'Wordpress + Social Login' ),
			'raas'    => __( 'Registration-as-a-Service' )
	);

	$form['login_mode'] = array(
			'type'    => 'radio',
			'label'   => __( 'Login Mode' ),
			'options' => $mode_opts,
			'desc'    => __( 'By activate Gigya\'s Social Login, you also activate "Anyone can register" option on Wordpress General Settings page<br>' .
											 'By activate Gigya\'s RaaS Login, you also deactivate "Anyone can register" option on Wordpress General Settings page' ),
			'value'   => getParam( $values['login_mode'], 'wp_only' )
	);

	$form['sl_start'] = array(
			'markup' => '<div class="social-login-wrapper">'
	);

	$button_opts = array(
			'fullLogo'   => __( 'Full Logo' ),
			'standard'   => __( 'Standard' ),
			'signInWith' => __( 'Sign In With' )
	);

	$form['login_button_style'] = array(
			'type'    => 'select',
			'options' => $button_opts,
			'value'   => getParam( $values['login_button_style'], 'fullLogo' ),
			'label'   => __( 'Button Style' )
	);

	$connect_without_opts = array(
			'tempUser'          => __( 'Temp User' ),
			'alwaysLogin'       => __( 'Always Login' ),
			'loginExistingUser' => __( 'Login Existing User' ),
	);

	$form['login_connect_without'] = array(
			'type'    => 'select',
			'options' => $connect_without_opts,
			'value'   => getParam( $values['login_connect_without'], 'loginExistingUser' ),
			'label'   => __( 'Connect Without Login Behavior' ),
	);

	$form['login_width'] = array(
			'type'  => 'text',
			'label' => __( 'Width' ),
			'value' => getParam( $values['login_width'], '' ),
			'desc'  => __( 'The width of the plugin in px' ),
			'class' => 'size'
	);

	$form['login_height'] = array(
			'type'  => 'text',
			'label' => __( 'Height' ),
			'value' => getParam( $values['login_height'], '' ),
			'desc'  => __( 'The height of the plugin in px' ),
			'class' => 'size'
	);

	$form['login_redirect'] = array(
			'type'  => 'text',
			'label' => __( 'Post Login Redirect' ),
			'value' => getParam( $values['login_redirect'], '' ),
			'desc'  => __( 'Provide a URL to redirect users after they logged-in via Gigya social login' )
	);

	$form['login_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Login Providers' ),
			'value' => getParam( $values['login_providers'], '' ),
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook, twitter, google, linkedin' )
	);

	$form['login_term_link'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Terms Link' ),
			'value' => getParam( $values['login_term_link'], 0 )
	);

	$form['login_extra'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Complete Registration Form' ),
			'value' => getParam( $values['login_extra'], 0 ),
			'desc'  => __( "Check this checkbox if you have defined required fields in you site registration form. When checked a 'Complete Registration' form will pop up during user social registration, to let the user enter the missing required fields" )
	);

	$form['login_ui'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) LoginUI" ),
			'value' => getParam( $values['login_custom_code'], '' ),
			'desc'  => __( 'Enter one value per line, in the format' ) . ' <strong>key|value</strong> ' . __( 'format' )
	);

	$form['login_add_connection_custom'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
			'value' => getParam( $values['login_add_connection_custom'], '' ),
			'desc'  => __( 'Enter one value per line, in the format' ) . ' <strong>key|value</strong>'
	);

//	$form['login_custom_code'] = array(
//			'type'  => 'textarea',
//			'label' => __( "Custom Code (deprecated)" ),
//			'value' => ! empty( $values['login_ui'] ) ? $values['login_ui'] : '',
//			'desc'  => __( 'To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see' ) . '<a target="_blank" href="http://developers.gigya.com/050_CMS_Modules/030_Wordpress_Plugin">here</a>' . __( 'for help on what to put in the text area' ),
//			'class' => 'closed'
//	);

	$form['sl_end'] = array(
			'markup' => '</div>'
	);

	$form['raas_start'] = array(
			'markup' => '<div class="raas-login-wrapper">'
	);

	$form['raas_web_screen'] = array(
			'type'  => 'text',
			'label' => __( 'Web Screen Set ID' ),
			'value' => getParam( $values['raas_web_screen'], 'Login-web' )
	);

	$form['raas_mobile_screen'] = array(
			'type'  => 'text',
			'label' => __( 'Mobile Screen Set ID' ),
			'value' => getParam( $values['raas_mobile_screen'], 'Mobile-login' )
	);

	$form['raas_login_screen'] = array(
			'type'  => 'text',
			'label' => __( 'Login Screen ID' ),
			'value' => getParam( $values['raas_login_screen'], 'gigya-login-screen' )
	);

	$form['raas_register_screen'] = array(
			'type'  => 'text',
			'label' => __( 'Register Screen ID' ),
			'value' => getParam( $values['raas_register_screen'], 'gigya-register-screen' )
	);

	$form['raas_profile_web_screen'] = array(
			'type'  => 'text',
			'label' => __( 'Web Screen Set ID' ),
			'value' => getParam( $values['raas_profile_web_screen'], 'Profile-web' )
	);

	$form['raas_profile_mobile_screen'] = array(
			'type'  => 'text',
			'label' => __( 'Mobile Screen Set ID' ),
			'value' => getParam( $values['login_raas_profile_mobile_screen'], 'Profile-mobile' )
	);

	$form['raas_override_links'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Override Wordpress Links' ),
			'desc'  => __( "Checking this checkbox, WordPress's default 'Login', 'Registration' and 'Edit Profile' links will pop-up RaaS's respective screens instead of redirecting to the WordPress screens" ),
			'value' => getParam( $values['login_raas_profile_mobile'], 1 )
	);

	$form['raas_divs'] = array(
			'markup' => __( 'DIV IDs' ) . '<br>' .
					'<small>' . __( 'Specify the DIV IDs in which to embed the screen-sets. If the IDs are empty (default), then the screen-set would show as pop-up dialogs' ) . '<small>'
	);

	$form['raas_login_div'] = array(
			'type'  => 'text',
			'label' => __( 'Login' ),
			'value' => getParam( $values['raas_login_div'], 'loginform' )
	);

	$form['raas_register_div'] = array(
			'type'  => 'text',
			'label' => __( 'Register' ),
			'value' => getParam( $values['raas_register_div'], 'registerform' )
	);

	$form['raas_profile_div'] = array(
			'type'  => 'text',
			'label' => __( 'Profile' ),
			'value' => getParam( $values['raas_profile_div'], 'profile-page' )
	);

	$form['raas_end'] = array(
			'markup' => '</div>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_LOGIN );
}