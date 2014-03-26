<?php
/**
 * Form builder for 'RAAS Settings' configuration page.
 */
function raasSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_RAAS );
	$form   = array();

	$form['raas_plugin'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gigya RAAS' ),
			'value' => ! empty( $values['raas_plugin'] ) ? $values['raas_plugin'] : 0
	);
	$form['raas_web_screen_id'] = array(
			'type' => 'text',
			'label' => __('Web Screen Set ID'),
			'value' => ! empty( $values['raas_web_screen_id'] ) ? $values['raas_web_screen_id'] : 'Login-web'
	);
	$form['raas_mobile_screen_id'] = array(
			'type' => 'text',
			'label' => __('Mobile Screen Set ID'),
			'value' => ! empty( $values['raas_mobile_screen_id'] ) ? $values['raas_mobile_screen_id'] : 'Mobile-login'
	);
	$form['raas_login_screen_id'] = array(
			'type' => 'text',
			'label' => __('Login Screen ID'),
			'value' => ! empty( $values['raas_login_screen_id'] ) ? $values['raas_login_screen_id'] : 'gigya-login-screen'
	);
	$form['raas_register_screen_id'] = array(
			'type' => 'text',
			'label' => __('Register Screen ID'),
			'value' => ! empty( $values['raas_register_screen_id'] ) ? $values['raas_register_screen_id'] : 'gigya-register-screen'
	);
	$form['raas_profile_web_screen_id'] = array(
			'type' => 'text',
			'label' => __('Web Screen Set ID'),
			'value' => ! empty( $values['raas_profile_web_screen_id'] ) ? $values['raas_profile_web_screen_id'] : 'Profile-web'
	);
	$form['raas_profile_mobile_screen_id'] = array(
			'type' => 'text',
			'label' => __('mobile Screen Set ID'),
			'value' => ! empty( $values['raas_profile_mobile_screen_id'] ) ? $values['raas_profile_mobile_screen_id'] : 'Profile-mobile'
	);
	$form['raas_login_label'] = array(
			'type' => 'text',
			'label' => __('Login'),
			'value' => ! empty( $values['raas_login_label'] ) ? $values['raas_login_label'] : 'Login'
	);
	$form['raas_register_label'] = array(
			'type' => 'text',
			'label' => __('Register'),
			'value' => ! empty( $values['raas_register_label'] ) ? $values['raas_register_label'] : 'Register'
	);
	$form['raas_profile_label'] = array(
			'type' => 'text',
			'label' => __('Profile'),
			'value' => ! empty( $values['raas_profile_label'] ) ? $values['raas_profile_label'] : 'Profile'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_RAAS );
}