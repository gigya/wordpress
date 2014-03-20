<?php
function commentsConfigForm() {
	_gigya_formEl(
			array(
					'type'    => 'checkbox',
					'id'      => 'comments_plugin',
					'label'   => __( 'Enable Gigya Comments' ),
			)
	);
	_gigya_formEl(
			array(
					'type'    => 'text',
					'id'      => 'comments_cat_id',
					'label'   => __( 'Category ID' )
			)
	);
	_gigya_formEl(
			array(
					'type'    => 'text',
					'id'      => 'comments_enable_share_providers',
					'label'   => __( 'Enable Share Providers' ),
			)
	);
	$comments_share_opts = array(
			"both" => __("both"),
			"external" => __("External")
	);
	_gigya_formEl(
			array(
					'type'    => 'select',
					'id'      => 'comments_enable_share_activity',
					'options' => $comments_share_opts,
					'default' => $comments_share_opts['external'],
					'label'   => __( 'Enable Sharing to Activity Feed' ),
				'desc' => 'When publishing feed items, by default the feed items are published to social networks only and will not appear on the site\'s Activity Feed plugin. To change this behavior, you must change the publish scope to "Both"'
			)
	);
	_gigya_formEl(
			array(
					'type'  => 'textarea',
					'id'    => 'commets_custom_code',
					'label' => __( "Additional Parameters (advanced)" ),
				'desc' => __('Enter values in') . '<strong>key1=value1|key2=value2...keyX=valueX</strong>' . __('format') . '<br>' .  __('See list of available:') . '<a href="http://developers.gigya.com/020_Client_API/030_Comments/comments.showCommentsUI" target="_blank">parameters</a>'
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
					'default' => $privacy_opts['private'],
					'label'   => __( 'Privacy' ),
			)
	);
}