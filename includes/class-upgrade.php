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

        $sql =
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lhr_log (
                id BIGINT unsigned not null auto_increment,
                url TEXT,
                request_args MEDIUMTEXT,
                response MEDIUMTEXT,
                runtime VARCHAR(64),
                referrer TEXT null,
                referrer_args MEDIUMTEXT null,
                date_added DATETIME,
                PRIMARY KEY  (id),
                KEY date_added (date_added)
            ) COLLATE {$wpdb->collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    private function run_upgrade() {
        global $wpdb;

        $sql =
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lhr_log (
                id BIGINT unsigned not null auto_increment,
                url TEXT,
                request_args MEDIUMTEXT,
                response MEDIUMTEXT,
                runtime VARCHAR(64),
                referrer TEXT null,
                referrer_args MEDIUMTEXT null,
                date_added DATETIME,
                PRIMARY KEY  (id),
                KEY date_added (date_added)
            ) COLLATE {$wpdb->collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}
