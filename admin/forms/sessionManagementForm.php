<?php
/**
 * Form builder for 'Global Settings' configuration page.
 *
 * Note: gigya_admin.js has some client-side controls for this form. Any change in the form might affect those, so check them!
 */
function sessionManagementForm() {

	$values = _getGigyaSettingsValues( GIGYA__SETTINGS_SESSION );
	$form = array();

	$form['session_title'] = [
		'markup' => '<h4>Regular Session</h4>',
	];

	$form['session_type'] = array(
		'type' => 'select',
		'label' => __( 'Type' ),
		'options' => array(
			'sliding' => __( 'Sliding' ),
			'fixed' => __( 'Fixed' ),
			'forever' => __( 'Valid forever' ),
			'browser_close' => __( 'Until browser closes' ),
		),
		'value' => _gigParam( $values, 'session_type', 'sliding' ),
	);

	$form['session_type_numeric'] = array(
		'type' => 'hidden',
		'label' => __( 'Type Numeric' ),
		'value' => _gigParam( $values, 'session_type_numeric', GIGYA__SESSION_SLIDING ),
	);

	$form['session_duration'] = array(
		'type' => 'text',
		'label' => __( 'Duration' ),
		'value' => _gigParam( $values, 'session_duration', GIGYA__DEFAULT_COOKIE_EXPIRATION ),
		'markup' => 'seconds',
		'size' => 10,
		'class' => 'hidden',
	);

	$form['remember_session_title'] = [
		'markup' => '<h4>Remember Me Session</h4>',
	];

	$form['remember_session_type'] = array(
		'type' => 'select',
		'label' => __( 'Type' ),
		'options' => array(
			'sliding' => __( 'Sliding' ),
			'fixed' => __( 'Fixed' ),
			'forever' => __( 'Valid forever' ),
			'browser_close' => __( 'Until browser closes' ),
		),
		'value' => _gigParam( $values, 'remember_session_type', 'fixed' ),
	);

	$form['remember_session_type_numeric'] = array(
		'type' => 'hidden',
		'label' => __( 'Type Numeric' ),
		'value' => _gigParam( $values, 'remember_session_type_numeric', GIGYA__SESSION_FIXED ),
	);

	$form['remember_session_duration'] = array(
		'type' => 'text',
		'label' => __( 'Duration' ),
		'value' => _gigParam( $values, 'remember_session_duration', GIGYA__DEFAULT_REMEMBER_COOKIE_EXPIRATION ),
		'markup' => 'seconds',
		'size' => 10,
		'class' => 'hidden',
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

	if ( get_option( 'gigya_settings_fields' ) )
	{
		$form['clean_db'] = array(
			'markup' => '<a href="javascript:void(0)" class="clean-db">Database cleaner after upgrade</a><br><small>Press this button to remove all unnecessary elements of the previous version from your database.Please make sure to backup your database before performing the clean. Learn more about upgrading from the previous version <a href="https://github.com/gigya/wordpress/wiki#installing-the-gigya-plugin-for-wordpress">here.</a></small>',
		);
	}

	echo _gigya_form_render( $form, GIGYA__SETTINGS_SESSION );
}