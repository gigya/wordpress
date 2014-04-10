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
			'value' => _gigParam( $values['comments_plugin'], 0 ),
	);

	$form['comments_hide'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Disabled default Comments position' ),
			'value' => _gigParam( $values['comments_hide'], 0 ),
			'desc'  => __( "Checking this will disabled the default comment position, but still let you use<br> Gigya's comments widgets to position comments location on the page layout" )
	);

	$form['comments_rating'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Include Rating plugin' ),
			'value' => _gigParam( $values['comments_rating'], 0 ),
	);

	$form['comments_cat_id'] = array(
			'type'  => 'text',
			'label' => __( 'Category ID' ),
			'value' => _gigParam( $values['comments_cat_id'], '' ),
			'desc'  => __( "The category ID on 'Comments category name' at Gigya's settings" ) . ' ' . '<a href=https://platform.gigya.com/Site/partners/Settings.aspx#cmd=Settings.CommentsSetup>' . __( 'here' ) . '</a>'
	);

	$form['comments_custom_code'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values['comments_custom_code'], '' ),
			'desc'  => __( 'Enter validate JSON format' ) . '<br>' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/030_Comments/comments.showCommentsUI" target="_blank">parameters</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_COMMENTS );
}