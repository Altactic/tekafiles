<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

/* deleting options
$option_name = 'plugin_option_name';
delete_option( $option_name );
*/

//drop a custom db table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tekafile" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tekadownload" );