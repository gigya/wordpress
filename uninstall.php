<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
	status_header( 404 );
	exit;
}

// Delete Gigya admin settings options.
delete_option( GIGYA__SETTINGS_GLOBAL );
delete_option( GIGYA__SETTINGS_LOGIN );
delete_option( GIGYA__SETTINGS_SHARE );
delete_option( GIGYA__SETTINGS_COMMENTS );
delete_option( GIGYA__SETTINGS_REACTIONS );
delete_option( GIGYA__SETTINGS_GM );
delete_option( GIGYA__SETTINGS_FEED );

// Delete Gigya widgets options.
delete_option( 'widget_gigya_share' );
delete_option( 'widget_gigya_comments' );
delete_option( 'widget_gigya_reactions' );
delete_option( 'widget_gigya_gamification' );
delete_option( 'widget_gigya_feed' );
delete_option( 'widget_gigya_follow' );

// Remove old widgets if still there.
global $wpdb;
$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wp_options WHERE option_name LIKE '%widget_gigya%'" ) );
