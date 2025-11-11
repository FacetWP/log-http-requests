<?php

class LHR_Upgrade
{
    public $version;
    public $last_version;

    function __construct() {
        $this->version = LHR_VERSION;
        $this->last_version = get_option( 'lhr_version' );

        if ( version_compare( $this->last_version, $this->version, '<' ) ) {
            if ( version_compare( $this->last_version, '0.1.0', '<' ) ) {
                $this->clean_install();
            }
            else {
                $this->run_upgrade();
            }

            update_option( 'lhr_version', $this->version );
        }
    }


    private function clean_install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$wpdb->prefix}lhr_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            url TEXT,
            request_args MEDIUMTEXT,
            response MEDIUMTEXT,
            runtime VARCHAR(64),
            date_added DATETIME,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }


    private function run_upgrade() {
        global $wpdb;
    }
}
