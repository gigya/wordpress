<?php
function loginConfigForm() {
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'login_plugin',
					'label' => __( 'Enable Gigya Social Login' )
			)
	);
	$button_opts = array(
			'fullLogo'   => __('Full Logo'),
			'standard'   => __('Standard'),
			'signInWith' => __( 'Sign In With' )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'login_button_style',
					'options' => $button_opts,
					'default' => $button_opts['fullLogo'],
					'label'   => __( 'Button Style' )
			)
	);
	$connect_without_opts = array(
			'tempUser'          => __( 'Temp User' ),
			'alwaysLogin'       => __( 'Always Login' ),
			'loginExistingUser' => __( 'Login Existing User' ),
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'connect_without',
					'options' => $connect_without_opts,
					'default' => $connect_without_opts['loginExistingUser'],
					'label'   => __( 'Connect Without Login Behavior' ),
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'login_width',
					'label' => __( 'Width' ),
					'desc'  => __( 'The width of the plugin in px' ),
					'class' => 'size'
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'login_height',
					'label' => __( 'Height' ),
					'desc'  => __( 'The height of the plugin in px' ),
					'class' => 'size'
			)
	);

	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'post_login_redirect',
					'label' => __( 'Post Login Redirect' ),
					'desc'  => __( 'Provide a URL to redirect users after they logged-in via Gigya social login' )
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'login_providers',
					'label' => __( 'Login Providers' ),
					'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook, twitter, google, linkedin' )
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'login_term_link',
					'label' => __( 'Show Terms Link' ),
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'show_reg',
					'label' => __( 'Show Complete Registration Form' ),
					'desc'  => __( "Check this checkbox if you have defined required fields in you site registration form. When checked a 'Complete Registration' form will pop up during user social registration, to let the user enter the missing required fields" )
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'gigya_debug',
					'label' => __( "Enable Gigya debug log" ),
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'textarea',
					'id'    => 'login_custom_code',
					'label' => __( "Additional Parameters (advanced) LoginUI" ),
					'desc'  => __( 'Enter values in' ) . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __( 'format' )
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'textarea',
					'id'    => 'login_add_connection_custom',
					'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
					'desc'  => __( 'Enter values in' ) . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __( 'format' )
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'textarea',
					'id'    => 'login_ui',
					'label' => __( "Custom Code (deprecated)" ),
					'desc'  => __( 'To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see' ) . '<a target="_blank" href="http://developers.gigya.com/050_CMS_Modules/030_Wordpress_Plugin">here</a>' . __( 'for help on what to put in the text area' ),
					'class' => 'closed'
			)
	);
}