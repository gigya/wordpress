<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
	status_header( 404 );
	exit;
}

/* Delete SAP CDC admin settings options */
delete_option( GIGYA__SETTINGS_GLOBAL );
delete_option( GIGYA__SETTINGS_LOGIN );
delete_option( GIGYA__SETTINGS_SCREENSETS );
delete_option( GIGYA__SETTINGS_SESSION );

/* Delete old/deprecated SAP CDC admin settings options, if present */
delete_option( 'gigya_share_settings' );
delete_option( 'gigya_comments_settings' );
delete_option( 'gigya_reactions_settings' );
delete_option( 'gigya_gm_settings' );

/* Delete old/deprecated SAP CDC widgets options */
delete_option( 'widget_gigya_share' );
delete_option( 'widget_gigya_comments' );
delete_option( 'widget_gigya_reactions' );
delete_option( 'widget_gigya_gamification' );
delete_option( 'widget_gigya_feed' );
delete_option( 'widget_gigya_follow' );

/* Remove custom SAP CDC capabilities */
$role = get_role('administrator');
$role->remove_cap('edit_gigya');
$role->remove_cap('edit_gigya_secret');

/* Remove old widgets if still there */
global $wpdb;
$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wp_options WHERE option_name LIKE '%widget_gigya%'" ) );
