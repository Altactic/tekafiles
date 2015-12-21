<?php

global $tekafiles_db_version;
$tekafiles_db_version = "1.0";

function tekafiles_activate () {
  tekafiles_install_db();
  $role = get_role('administrator');
  $role->add_cap('manage_tekafiles');
  $uploads = wp_upload_dir();
  $tekafiles_dir = $uploads['basedir'] . '/tekafiles';
  if (!is_dir($tekafiles_dir)) {
    wp_mkdir_p($tekafiles_dir);
  }
}

function tekafiles_install_db () {
    global $wpdb;
    global $tekafiles_db_version;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tekafile (
        ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT DEFAULT '',
        file VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL default '',
        enabled TINYINT NOT NULL DEFAULT '1',
        public TINYINT NOT NULL DEFAULT '1',
        date DATE DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  id (ID)
    );";
    dbDelta( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tekafile_user (
      ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      tekafile BIGINT(20) UNSIGNED NOT NULL,
      user BIGINT(20) NOT NULL,
      locked TINYINT NOT NULL DEFAULT '0',
      PRIMARY KEY  id (ID)
    );";
    dbDelta( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tekadownload (
      ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      tekafile BIGINT(20) UNSIGNED NOT NULL,
      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      user BIGINT(20) NOT NULL,
      ip VARCHAR(20) NULL DEFAULT '',
      PRIMARY KEY  id (id),
      KEY tekafile (tekafile),
      KEY user (user)
    );";
    dbDelta( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tekafile_log (
      ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      user BIGINT(20) NOT NULL,
      date datetime NOT NULL,
      ip varchar(20) NULL DEFAULT '',
      PRIMARY KEY (ID)
      );";
    dbDelta( $sql );

    add_option( "tekafiles_db_version", $tekafiles_db_version );
}

function tekafiles_update_db_check() {
  global $tekafiles_db_version;
  if (get_site_option( 'tekafiles_db_version' ) != $tekafiles_db_version) {
    tekafiles_install_db();
  }
}

function tekafiles_deactivate () {
  //global $wpdb;
  //$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tekafile" );
  //$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tekafile_user" );
  //$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tekadownload" );
  $role = get_role('administrator');
  $role->remove_cap('manage_tekafiles');
}