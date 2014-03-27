<?php
/**
 * Form builder for 'Social Login Settings' configuration page.
 */
function loginSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_LOGIN );
	$form   = array();

	$form['sl_start'] = array(
			'markup' => '<div class="social-login-wrapper">'
	);

	$mode_opts = array(
			'wp_only' => __( 'Wordpress only' ),
			'wp_sl'   => __( 'Wordpress + Social Login' ),
			'raas'    => __( 'Registration-as-a-Service' )
	);

	$form['login_mode'] = array(
			'type'    => 'radio',
			'label'   => __( 'Login Mode' ),
			'options' => $mode_opts,
			'desc'    => __( 'By activate Gigya\'s Social Login, you also activate "Membership: Anyone can register" option on Wordpress General Settings page' ),
			'value'   => ! empty( $values['login_mode'] ) ? $values['login_mode'] : 'wp_only'
	);

	$button_opts = array(
			'fullLogo'   => __( 'Full Logo' ),
			'standard'   => __( 'Standard' ),
			'signInWith' => __( 'Sign In With' )
	);

	$form['login_button_style'] = array(
			'type'    => 'select',
			'options' => $button_opts,
			'value'   => ! empty( $values['login_button_style'] ) ? $values['login_button_style'] : $button_opts['fullLogo'],
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
			'value'   => ! empty( $values['login_connect_without'] ) ? $values['login_connect_without'] : $connect_without_opts['loginExistingUser'],
			'label'   => __( 'Connect Without Login Behavior' ),
	);

	$form['login_width'] = array(
			'type'  => 'text',
			'label' => __( 'Width' ),
			'value' => ! empty( $values['login_width'] ) ? $values['login_width'] : '',
			'desc'  => __( 'The width of the plugin in px' ),
			'class' => 'size'
	);

	$form['login_height'] = array(
			'type'  => 'text',
			'label' => __( 'Height' ),
			'value' => ! empty( $values['login_height'] ) ? $values['login_height'] : '',
			'desc'  => __( 'The height of the plugin in px' ),
			'class' => 'size'
	);

	$form['login_redirect'] = array(
			'type'  => 'text',
			'label' => __( 'Post Login Redirect' ),
			'value' => ! empty( $values['login_redirect'] ) ? $values['login_redirect'] : '',
			'desc'  => __( 'Provide a URL to redirect users after they logged-in via Gigya social login' )
	);

	$form['login_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Login Providers' ),
			'value' => ! empty( $values['login_providers'] ) ? $values['login_providers'] : '',
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook, twitter, google, linkedin' )
	);

	$form['login_term_link'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Terms Link' ),
			'value' => ! empty( $values['login_term_link'] ) ? $values['login_term_link'] : 0
	);

	$form['login_show_reg'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Show Complete Registration Form' ),
			'value' => ! empty( $values['login_show_reg'] ) ? $values['login_show_reg'] : 0,
			'desc'  => __( "Check this checkbox if you have defined required fields in you site registration form. When checked a 'Complete Registration' form will pop up during user social registration, to let the user enter the missing required fields" )
	);

	$form['login_gigya_debug'] = array(
			'type'  => 'checkbox',
			'label' => __( "Enable Gigya debug log" ),
			'value' => ! empty( $values['login_gigya_debug'] ) ? $values['login_gigya_debug'] : 0
	);

	$form['login_ui'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) LoginUI" ),
			'value' => ! empty( $values['login_custom_code'] ) ? $values['login_custom_code'] : '',
			'desc'  => __( 'Enter values in' ) . ' <strong>key1=value1|key2=value2...keyX=valueX</strong> ' . __( 'format' )
	);

	$form['login_add_connection_custom'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
			'value' => ! empty( $values['login_add_connection_custom'] ) ? $values['login_add_connection_custom'] : '',
			'desc'  => __( 'Enter values in' ) . ' <strong>key1=value1|key2=value2...keyX=valueX</strong> ' . __( 'format' )
	);

	$form['login_custom_code'] = array(
			'type'  => 'textarea',
			'label' => __( "Custom Code (deprecated)" ),
			'value' => ! empty( $values['login_ui'] ) ? $values['login_ui'] : '',
			'desc'  => __( 'To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see' ) . '<a target="_blank" href="http://developers.gigya.com/050_CMS_Modules/030_Wordpress_Plugin">here</a>' . __( 'for help on what to put in the text area' ),
			'class' => 'closed'
	);

	$form['sl_end'] = array(
			'markup' => '</div>'
	);
	$form['raas_start'] = array(
			'markup' => '<div class="raas-login-wrapper">'
	);

	$form['login_raas_web_screen_id'] = array(
			'type' => 'text',
			'label' => __('Web Screen Set ID'),
			'value' => ! empty( $values['login_raas_web_screen_id'] ) ? $values['login_raas_web_screen_id'] : 'Login-web'
	);
	$form['login_raas_mobile_screen_id'] = array(
			'type' => 'text',
			'label' => __('Mobile Screen Set ID'),
			'value' => ! empty( $values['login_raas_mobile_screen_id'] ) ? $values['login_raas_mobile_screen_id'] : 'Mobile-login'
	);
	$form['login_raas_login_screen_id'] = array(
			'type' => 'text',
			'label' => __('Login Screen ID'),
			'value' => ! empty( $values['login_raas_login_screen_id'] ) ? $values['login_raas_login_screen_id'] : 'gigya-login-screen'
	);
	$form['login_raas_register_screen_id'] = array(
			'type' => 'text',
			'label' => __('Register Screen ID'),
			'value' => ! empty( $values['login_raas_register_screen_id'] ) ? $values['login_raas_register_screen_id'] : 'gigya-register-screen'
	);
	$form['login_raas_profile_web_screen_id'] = array(
			'type' => 'text',
			'label' => __('Web Screen Set ID'),
			'value' => ! empty( $values['login_raas_profile_web_screen_id'] ) ? $values['login_raas_profile_web_screen_id'] : 'Profile-web'
	);
	$form['login_raas_profile_mobile_screen_id'] = array(
			'type' => 'text',
			'label' => __('mobile Screen Set ID'),
			'value' => ! empty( $values['login_raas_profile_mobile_screen_id'] ) ? $values['login_raas_profile_mobile_screen_id'] : 'Profile-mobile'
	);
	$form['login_raas_login_label'] = array(
			'type' => 'text',
			'label' => __('Login'),
			'value' => ! empty( $values['login_raas_login_label'] ) ? $values['login_raas_login_label'] : 'Login'
	);
	$form['login_raas_register_label'] = array(
			'type' => 'text',
			'label' => __('Register'),
			'value' => ! empty( $values['login_raas_register_label'] ) ? $values['login_raas_register_label'] : 'Register'
	);
	$form['login_raas_profile_label'] = array(
			'type' => 'text',
			'label' => __('Profile'),
			'value' => ! empty( $values['login_raas_profile_label'] ) ? $values['login_raas_profile_label'] : 'Profile'
	);

	$form['raas_end'] = array(
			'markup' => '</div>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_LOGIN );
}