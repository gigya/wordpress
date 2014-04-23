<?php
/**
 * Form builder for 'Comment Settings' configuration page.
 */
function commentsSettingsForm() {
	$values = get_option( GIGYA__SETTINGS_COMMENTS );
	$form   = array();

	$form['on'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Enable Gigya Comments' ),
			'value' => $values['on'] === '0' ? '0' :'1'
	);

	$form['hide'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Disabled default Comments position' ),
			'value' => _gigParam( $values['hide'], 0 ),
			'desc'  => __( "Checking this will disabled the default comment position, but still let you use<br> Gigya's comments widgets to position comments location on the page layout" )
	);

	$form['rating'] = array(
			'type'  => 'checkbox',
			'label' => __( 'Include Rating plugin' ),
			'value' => _gigParam( $values['rating'], 0 ),
	);

	$form['categoryID'] = array(
			'type'  => 'text',
			'label' => __( 'Category ID' ),
			'value' => _gigParam( $values['categoryID'], '' ),
			'desc'  => __( "The category ID on 'Comments category name' at Gigya's settings" ) . ' ' . '<a href=https://platform.gigya.com/Site/partners/Settings.aspx#cmd=Settings.CommentsSetup>' . __( 'here' ) . '</a>'
	);

	$form['advanced'] = array(
			'type'  => 'textarea',
			'label' => __( "Additional Parameters (advanced)" ),
			'value' => _gigParam( $values['advanced'], '' ),
			'desc'  => __( 'Enter validate JSON format' ) . '<br>' . __( 'See list of available:' ) . '<a href="http://developers.gigya.com/020_Client_API/030_Comments/comments.showCommentsUI" target="_blank">parameters</a>'
	);

	echo _gigya_form_render( $form, GIGYA__SETTINGS_COMMENTS );
}