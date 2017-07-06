<?php

class LHR_Upgrade
{
    function __construct() {
        $this->version = LHR_VERSION;
        $this->last_version = get_option( 'lhr_version' );

        if ( version_compare( $this->last_version, $this->version, '<' ) ) {
            if ( version_compare( $this->last_version, '0.1.0', '<' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
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

        $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lhr_log (
            id BIGINT unsigned not null auto_increment,
            url TEXT,
            request_args MEDIUMTEXT,
            response MEDIUMTEXT,
            runtime VARCHAR(64),
            date_added DATETIME,
            PRIMARY KEY (id)
        ) DEFAULT CHARSET=utf8";
        dbDelta( $sql );
    }


    private function run_upgrade() {
        global $wpdb;
    }
}
