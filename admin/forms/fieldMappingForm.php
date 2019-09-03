<?php
	/**
	 * Form builder for 'User Management Settings' configuration page.
	 */
	function fieldMappingForm() {
		$login_options = get_option( GIGYA__SETTINGS_LOGIN );
		$values        = get_option( GIGYA__SETTINGS_FIELD_MAPPING );
		$form          = [];

		if ( $login_options['mode'] == 'raas' ) {
			$form['map_fieldmapping_desc'] = [
				'markup' => __( '<p>Define which fields to map from Gigya to WordPress. The WordPress mapped target fields will be populated with data copied from the corresponding source fields. Learn more <a href="https://developers.gigya.com/display/GD/WordPress+Plugin#WordPressPlugin-UserManagementSettings" target="_blank" rel="noopener noreferrer" />here</a></p>' ),
			];

			$gigya_full_map            = _gigParam( $values, 'map_raas_full_map', '' );
			$form['map_raas_full_map'] = [
				'type'  => 'textarea',
				'label' => __( 'Full field mapping' ),
				'value' => ( $gigya_full_map ) ? $gigya_full_map :
					_gigParamsBuildLegacyJson( [ 'first_name', 'last_name', 'nickname', 'profile_image', 'description' ] ),
			];

			$form['map_offline_sync_title'] = [
				'markup' => __( '<h4>Offline Sync</h4><p>Define whether to perform field mapping operations periodically even without a specific user action</p>' ),
			];

			$form['map_offline_sync_enable'] = [
				'type'  => 'checkbox',
				'label' => __( 'Enable' ),
				'value' => _gigParam( $values, 'map_offline_sync_enable', 1 ),
			];

			$form['map_offline_sync_frequency'] = [
				'type'   => 'text',
				'size'   => 10,
				'label'  => __( 'Job frequency' ),
				'value'  => _gigParam( $values, 'map_offline_sync_frequency', 10 ),
				'markup' => __( 'minutes' ),
				'desc'   => 'This setting relies on the WordPress cron mechanism. Minimum value: ' . GIGYA__OFFLINE_SYNC_MIN_FREQ,
			];

			$form['map_offline_sync_email_on_success'] = [
				'type'  => 'text',
				'label' => __( 'Email on Success' ),
				'value' => _gigParam( $values, 'map_offline_sync_email_on_success', '' ),
			];

			$form['map_offline_sync_email_on_failure'] = [
				'type'  => 'text',
				'label' => __( 'Email on Failure' ),
				'value' => _gigParam( $values, 'map_offline_sync_email_on_failure', '' ),
			];
		} elseif ( $login_options['mode'] == 'wp_sl' ) {
			$form['map_social_title'] = [
				'markup' => __( '<h4>Mapping Gigya User Fields to WordPress Fields</h4><p>Define which fields to map from Gigya to WordPress. The WordPress mapped target fields will be populated with data copied from the corresponding source fields. Learn more <a href="https://developers.gigya.com/display/GD/WordPress+Plugin#WordPressPlugin-UserManagementSettings" target="_blank" rel="noopener noreferrer" />here</a></p>' ),
			];

			$form['map_social_first_name']    = [
				'type'  => 'checkbox',
				'label' => __( 'First Name' ),
				'value' => _gigParam( $values, 'map_social_first_name', 1 ),
			];
			$form['map_social_last_name']     = [
				'type'  => 'checkbox',
				'label' => __( 'Last Name' ),
				'value' => _gigParam( $values, 'map_social_last_name', 1 ),
			];
			$form['map_social_display_name']  = [
				'type'  => 'checkbox',
				'label' => __( 'Display Name' ),
				'value' => _gigParam( $values, 'map_social_display_name', 1 ),
			];
			$form['map_social_nickname']      = [
				'type'  => 'checkbox',
				'label' => __( 'Nickname' ),
				'value' => _gigParam( $values, 'map_social_nickname', 1 ),
			];
			$form['map_social_profile_image'] = [
				'type'  => 'checkbox',
				'label' => __( 'Profile Image (avatar)' ),
				'value' => _gigParam( $values, 'map_social_profile_image', 1 ),
			];
			$form['map_social_description']   = [
				'type'  => 'checkbox',
				'label' => __( 'Biographical Info' ),
				'value' => _gigParam( $values, 'map_social_description', 1 ),
			];
		}

		echo _gigya_form_render( $form, GIGYA__SETTINGS_FIELD_MAPPING );
	}