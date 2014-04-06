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
			'value' => _gigParam( $values['comments_plugin'], 0 )
	);

	$form['comments_container_id'] = array(
			'type'  => 'text',
			'label' => __( 'Container ID' ),
			'value' => _gigParam( $values['comments_container_id'], 'comments' )
	);

	$form['comments_cat_id'] = array(
			'type'  => 'text',
			'label' => __( 'Category ID' ),
			'value' => _gigParam( $values['comments_cat_id'], '' )
	);

	$comments_share_opts = array(
			"both"     => __( "both" ),
			"external" => __( "External" )
	);

	$form['comments_enable_share_activity'] = array(
			'type'    => 'select',
			'options' => $comments_share_opts,
			'value'   => _gigParam( $values['comments_enable_share_activity'], 'external' ),
			'label'   => __( 'Enable Sharing to Activity Feed' ),
			'desc'    => 'When publishing feed items, by default the feed items are published to social networks only and will not appear on the site\'s Activity Feed plugin. To change this behavior, you must change the publish scope to "Both"'
	);

	$form['comments_custom_code'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values['comments_custom_code'], '' ),
			'desc'  => __( 'Enter validate JSON format' ) . '<br>' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/030_Comments/comments.showCommentsUI" target="_blank">parameters</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_COMMENTS );
}