<?php
function commentsConfigForm() {
	$values = get_option( GIGYA__SETTINGS_PREFIX );
	_gigya_formEl(
			array(
					'type'  => 'checkbox',
					'id'    => 'comments_plugin',
					'label' => __( 'Enable Gigya Comments' ),
					'value' => ! empty( $values['comments_plugin'] ) ? $values['comments_plugin'] : 0
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'comments_cat_id',
					'label' => __( 'Category ID' ),
					'value' => ! empty( $values['comments_cat_id'] ) ? $values['comments_cat_id'] : ''
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'text',
					'id'    => 'comments_enable_share_providers',
					'label' => __( 'Enable Share Providers' ),
					'value' => ! empty( $values['comments_enable_share_providers'] ) ? $values['comments_enable_share_providers'] : ''
			)
	);
	$comments_share_opts = array(
			"both"     => __( "both" ),
			"external" => __( "External" )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'comments_enable_share_activity',
					'options' => $comments_share_opts,
					'value'   => ! empty( $values['comments_enable_share_activity'] ) ? $values['comments_enable_share_activity'] : $comments_share_opts['external'],
					'label'   => __( 'Enable Sharing to Activity Feed' ),
					'desc'    => 'When publishing feed items, by default the feed items are published to social networks only and will not appear on the site\'s Activity Feed plugin. To change this behavior, you must change the publish scope to "Both"'
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'textarea',
					'id'    => 'comments_custom_code',
					'label' => __( "Additional Parameters (advanced)" ),
					'value' => ! empty( $values['comments_custom_code'] ) ? $values['comments_custom_code'] : '',
					'desc'  => __( 'Enter values in' ) . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __( 'format' ) . '<br>' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/030_Comments/comments.showCommentsUI" target="_blank">parameters</a>'
			)
	);
	$privacy_opts = array(
			"private" => __( "Private" ),
			"public"  => __( "Public" ),
			"friends" => __( "Friends" )
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'comments_privacy',
					'options' => $privacy_opts,
					'value'   => ! empty( $values['comments_privacy'] ) ? $values['comments_privacy'] : $privacy_opts['private'],
					'label'   => __( 'Privacy' ),
			)
	);
}