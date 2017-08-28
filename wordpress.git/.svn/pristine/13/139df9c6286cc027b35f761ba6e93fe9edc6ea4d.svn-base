<?php
/**
 * Form builder for 'Gamification Settings' configuration page.
 */
function gmSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_GM );
	$form   = array();

	$form['on'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gamification Plugins' ),
			'value' => _gigParamDefaultOn( $values, 'on' )
	);

	$form['notification'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Notifications' ),
			'value' => _gigParam( $values, 'notification', 0 )
	);

	$form['period'] = array(
			'type'    => 'select',
			'options' => array(
					"7days" => __( "7 Days" ),
					"all"   => __( "All" )
			),
			'value'   => _gigParam( $values, 'period', '7days' ),
			'label'   => __( 'Leaderboard time period' ),
	);

	$form['totalCount'] = array(
			'type'  => 'text',
			'value' => _gigParam( $values, 'totalCount', '12' ),
			'label' => __( 'Leaderboard user count' ),
			'desc'  => __( 'Valid values are between 1 to 23' )
	);

	$form['advanced_achievements'] = array(
		'type'  => 'textarea',
		'value' => _gigParam( $values, 'advanced_achievements', '' ),
		'label' => __( 'Additional Parameters (advanced) for showAchievementsUI' ),
		'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/040_GM/gm.showAchievementsUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	$form['advanced_challenge'] = array(
		'type'  => 'textarea',
		'value' => _gigParam( $values, 'advanced_challenge', '' ),
		'label' => __( 'Additional Parameters (advanced) showChallengeStatusUI' ),
		'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/040_GM/gm.showChallengeStatusUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	$form['advanced_leaderboard'] = array(
		'type'  => 'textarea',
		'value' => _gigParam( $values, 'advanced_leaderboard', '' ),
		'label' => __( 'Additional Parameters (advanced) showLeaderboardUI' ),
		'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/040_GM/gm.showLeaderboardUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	$form['advanced_user_status'] = array(
		'type'  => 'textarea',
		'value' => _gigParam( $values, 'advanced_user_status', '' ),
		'label' => __( 'Additional Parameters (advanced) showUserStatusUI' ),
		'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/040_GM/gm.showUserStatusUI" target="_blank">' . __( 'parameters' ) . '</a>'
	);

	$form['advanced_notification'] = array(
		'type'  => 'textarea',
		'value' => _gigParam( $values, 'advanced_notification', '' ),
		'label' => __( 'Additional Parameters (advanced) showNotifications' ),
		'desc'  => sprintf( __( 'Enter valid %s. See list of available:' ), '<a class="gigya-json-example" href="javascript:void(0)">' . __( 'JSON format' ) . '</a>' ) . ' <a href="http://developers.gigya.com/020_Client_API/040_GM/gm.showNotifications" target="_blank">' . __( 'parameters' ) . '</a>'
	);
	echo _gigya_form_render( $form, GIGYA__SETTINGS_GM );
}