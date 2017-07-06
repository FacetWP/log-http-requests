<?php
/*
Plugin Name: Log HTTP Requests
Description: Log all those pesky WP HTTP requests
Version: 1.0.0
Author: FacetWP, LLC
Author URI: https://facetwp.com/

Copyright 2017 FacetWP, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) or exit;

class Log_HTTP_Requests
{
    public $start_time;
    public static $instance;


    function __construct() {

        // setup variables
        define( 'LHR_VERSION', '1.0.0' );
        define( 'LHR_DIR', dirname( __FILE__ ) );
        define( 'LHR_URL', plugins_url( '', __FILE__ ) );
        define( 'LHR_BASENAME', plugin_basename( __FILE__ ) );

        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_filter( 'http_request_args', array( $this, 'start_timer' ) );
        add_action( 'http_api_debug', array( $this, 'capture_request' ), 10, 5 );
        add_action( 'lhr_cleanup_cron', array( $this, 'cleanup' ) );
        // apply_filters( 'http_response', $response, $r, $url );
    }


    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    function init() {
        include( LHR_DIR . '/includes/class-upgrade.php' );

        new LHR_Upgrade();

        $this->run_cron();
    }


    function run_cron() {
        if ( ! wp_next_scheduled( 'lhr_cleanup' ) ) {
            wp_schedule_single_event( time() + 300, 'lhr_cleanup' );
        }
    }


    function admin_menu() {
        add_options_page( 'Log HTTP Requests', 'Log HTTP Requests', 'manage_options', 'log-http-requests', array( $this, 'settings_page' ) );
    }


    function settings_page() {
        include( LHR_DIR . '/templates/page-settings.php' );
    }


    function admin_scripts( $hook ) {
        if ( 'settings_page_log-http-requests' == $hook ) {
            wp_enqueue_style( 'media-views' );
        }
    }


    function start_timer( $args ) {
        $this->start_time = microtime( true );
        return $args;
    }


    function capture_request( $response, $context, $transport, $args, $url ) {
        global $wpdb;

        if ( false !== strpos( $url, 'doing_wp_cron' ) ) {
            return;
        }

        $wpdb->insert( $wpdb->prefix . 'lhr_log', array(
            'url' => $url,
            'request_args' => serialize( $args ),
            'response' => serialize( $response ),
            'runtime' => ( microtime( true ) - $this->start_time ),
            'date_added' => current_time( 'mysql' )
        ) );
    }


    function time_since( $time ) {
        $time = current_time( 'timestamp' ) - strtotime( $time );
        $time = ( $time < 1 ) ? 1 : $time;
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ( $tokens as $unit => $text ) {
            if ( $time < $unit ) continue;
            $numberOfUnits = floor( $time / $unit );
            return $numberOfUnits . ' ' . $text . ( ( $numberOfUnits > 1 ) ? 's' : '' );
        }
    }
}


function LHR() {
    return Log_HTTP_Requests::instance();
}


LHR();
