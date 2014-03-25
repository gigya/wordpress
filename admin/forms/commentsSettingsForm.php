<?php
/**
 * Form builder for 'Comment Settings' configuration page.
 */
function commentsSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_COMMENTS );
	$form   = array();

	$form['comments_plugin'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gigya Comments' ),
			'value' => ! empty( $values['comments_plugin'] ) ? $values['comments_plugin'] : 0
	);

	$form['comments_cat_id'] = array(
			'type'  => 'text',
			'label' => __( 'Category ID' ),
			'value' => ! empty( $values['comments_cat_id'] ) ? $values['comments_cat_id'] : ''
	);

	$form['comments_enable_share_providers'] = array(
			'type'  => 'text',
			'label' => __( 'Enable Share Providers' ),
			'value' => ! empty( $values['comments_enable_share_providers'] ) ? $values['comments_enable_share_providers'] : ''
	);

	$comments_share_opts = array(
			"both"     => __( "both" ),
			"external" => __( "External" )
	);

	$form['comments_enable_share_activity'] = array(
			'type'    => 'select',
			'options' => $comments_share_opts,
			'value'   => ! empty( $values['comments_enable_share_activity'] ) ? $values['comments_enable_share_activity'] : $comments_share_opts['external'],
			'label'   => __( 'Enable Sharing to Activity Feed' ),
			'desc'    => 'When publishing feed items, by default the feed items are published to social networks only and will not appear on the site\'s Activity Feed plugin. To change this behavior, you must change the publish scope to "Both"'
	);

	$form['comments_custom_code'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => ! empty( $values['comments_custom_code'] ) ? $values['comments_custom_code'] : '',
			'desc'  => __( 'Enter values in' ) . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __( 'format' ) . '<br>' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/030_Comments/comments.showCommentsUI" target="_blank">parameters</a>'
	);

	$privacy_opts = array(
			"private" => __( "Private" ),
			"public"  => __( "Public" ),
			"friends" => __( "Friends" )
	);

	$form['comments_privacy'] = array(
			'type'    => 'select',
			'options' => $privacy_opts,
			'value'   => ! empty( $values['comments_privacy'] ) ? $values['comments_privacy'] : $privacy_opts['private'],
			'label'   => __( 'Privacy' ),
	);

	echo GigyaSettings::_gigya_form_render( $form, GIGYA__SETTINGS_COMMENTS );
}