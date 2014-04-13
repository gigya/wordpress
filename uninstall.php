<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
	status_header( 404 );
	exit;
}

// Delete all Gigya options.
delete_option( GIGYA__SETTINGS_GLOBAL );
delete_option( GIGYA__SETTINGS_LOGIN );
delete_option( GIGYA__SETTINGS_SHARE );
delete_option( GIGYA__SETTINGS_COMMENTS );
delete_option( GIGYA__SETTINGS_REACTIONS );
delete_option( GIGYA__SETTINGS_GM );
delete_option( GIGYA__SETTINGS_FEED );
