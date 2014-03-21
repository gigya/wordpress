<?php
/**
 * Form builder for 'Social Login Settings' configuration page.
 */
function loginSettingsForm() {
	$values = get_option( $_GET['page'] . '-settings' );
	$form   = array();

	$form['login_plugin'] = array(
			'type'  => 'checkbox',
			'id'    => 'login_plugin',
			'label' => __( 'Enable Gigya Social Login' ),
			'value' => ! empty( $values['login_plugin'] ) ? $values['login_plugin'] : 0
	);

	$button_opts = array(
			'fullLogo'   => __( 'Full Logo' ),
			'standard'   => __( 'Standard' ),
			'signInWith' => __( 'Sign In With' )
	);

	$form['login_button_style'] = array(
			'type'    => 'select',
			'id'      => 'login_button_style',
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
			'id'      => 'login_connect_without',
			'options' => $connect_without_opts,
			'value'   => ! empty( $values['login_connect_without'] ) ? $values['login_connect_without'] : $connect_without_opts['loginExistingUser'],
			'label'   => __( 'Connect Without Login Behavior' ),
	);

	$form['login_width'] = array(
			'type'  => 'text',
			'id'    => 'login_width',
			'label' => __( 'Width' ),
			'value' => ! empty( $values['login_width'] ) ? $values['login_width'] : '',
			'desc'  => __( 'The width of the plugin in px' ),
			'class' => 'size'

	);

	$form['login_height'] = array(
			'type'  => 'text',
			'id'    => 'login_height',
			'label' => __( 'Height' ),
			'value' => ! empty( $values['login_height'] ) ? $values['login_height'] : '',
			'desc'  => __( 'The height of the plugin in px' ),
			'class' => 'size'
	);

	$form['login_redirect'] = array(
			'type'  => 'text',
			'id'    => 'login_redirect',
			'label' => __( 'Post Login Redirect' ),
			'value' => ! empty( $values['login_redirect'] ) ? $values['login_redirect'] : '',
			'desc'  => __( 'Provide a URL to redirect users after they logged-in via Gigya social login' )
	);

	$form['login_providers'] = array(
			'type'  => 'text',
			'id'    => 'login_providers',
			'label' => __( 'Login Providers' ),
			'value' => ! empty( $values['login_providers'] ) ? $values['login_providers'] : '',
			'desc'  => __( 'Leave empty or type * for all providers or define specific providers, for example: facebook, twitter, google, linkedin' )
	);

	$form['login_term_link'] = array(
			'type'  => 'checkbox',
			'id'    => 'login_term_link',
			'label' => __( 'Show Terms Link' ),
			'value' => ! empty( $values['login_term_link'] ) ? $values['login_term_link'] : 0
	);

	$form['login_show_reg'] = array(
			'type'  => 'checkbox',
			'id'    => 'login_show_reg',
			'label' => __( 'Show Complete Registration Form' ),
			'value' => ! empty( $values['login_show_reg'] ) ? $values['login_show_reg'] : 0,
			'desc'  => __( "Check this checkbox if you have defined required fields in you site registration form. When checked a 'Complete Registration' form will pop up during user social registration, to let the user enter the missing required fields" )
	);

	$form['login_show_reg'] = array(
			'type'  => 'checkbox',
			'id'    => 'login_show_reg',
			'label' => __( "Enable Gigya debug log" ),
			'value' => ! empty( $values['login_gigya_debug'] ) ? $values['login_gigya_debug'] : 0
	);

	$form['login_custom_code'] = array(
			'type'  => 'textarea',
			'id'    => 'login_custom_code',
			'label' => __( "Additional Parameters (advanced) LoginUI" ),
			'value' => ! empty( $values['login_custom_code'] ) ? $values['login_custom_code'] : '',
			'desc'  => __( 'Enter values in' ) . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __( 'format' )
	);

	$form['login_add_connection_custom'] = array(
			'type'  => 'textarea',
			'id'    => 'login_add_connection_custom',
			'label' => __( "Additional Parameters (advanced) AddConnectionsUI" ),
			'value' => ! empty( $values['login_add_connection_custom'] ) ? $values['login_add_connection_custom'] : '',
			'desc'  => __( 'Enter values in' ) . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __( 'format' )
	);

	$form['login_ui'] = array(
			'type'  => 'textarea',
			'id'    => 'login_ui',
			'label' => __( "Custom Code (deprecated)" ),
			'value' => ! empty( $values['login_ui'] ) ? $values['login_ui'] : '',
			'desc'  => __( 'To customize the look of the sign in component provided by the Gigya Socialize for WordPress plugin, you can provide generated interface code here.  If nothing is provided the default will be used. Please see' ) . '<a target="_blank" href="http://developers.gigya.com/050_CMS_Modules/030_Wordpress_Plugin">here</a>' . __( 'for help on what to put in the text area' ),
			'class' => 'closed'
	);

	GigyaSettings::_gigya_form_render($form);
}