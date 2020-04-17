<?php
/*
 * Plugin Name: Log HTTP Requests
 * Description: Log all those pesky WP HTTP requests
 * Version: 1.1
 * Author: FacetWP, LLC
 * Author URI: https://facetwp.com/
 *
 * Copyright 2019 FacetWP, LLC
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */
defined('ABSPATH') or exit();

class Log_HTTP_Requests
{

    public $query;

    public $start_time;

    public static $instance;

    function __construct()
    {

        // setup variables
        define('LHR_VERSION', '1.1');
        define('LHR_DIR', dirname(__FILE__));
        define('LHR_URL', plugins_url('', __FILE__));
        define('LHR_BASENAME', plugin_basename(__FILE__));

        add_action('init', array(
            $this,
            'init'
        ));
        add_action('admin_menu', array(
            $this,
            'admin_menu'
        ));
        add_action('admin_enqueue_scripts', array(
            $this,
            'admin_scripts'
        ));
        add_filter('http_request_args', array(
            $this,
            'start_timer'
        ));
        add_action('http_api_debug', array(
            $this,
            'capture_request'
        ), 10, 5);
        add_action('lhr_cleanup_cron', array(
            $this,
            'cleanup'
        ));
        add_action('wp_ajax_lhr_query', array(
            $this,
            'lhr_query'
        ));
        add_action('wp_ajax_lhr_clear', array(
            $this,
            'lhr_clear'
        ));
    }

    public static function instance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function init()
    {
        include (LHR_DIR . '/includes/class-upgrade.php');
        include (LHR_DIR . '/includes/class-query.php');

        new LHR_Upgrade();
        $this->query = new LHR_Query();

        if (! wp_next_scheduled('lhr_cleanup_cron')) {
            wp_schedule_single_event(time() + 86400, 'lhr_cleanup_cron');
        }
    }

    function cleanup()
    {
        global $wpdb;

        $now = current_time('timestamp');
        $expires = apply_filters('lhr_expiration_days', 1);
        $expires = date('Y-m-d H:i:s', strtotime('-' . $expires . ' days', $now));
        $wpdb->query("DELETE FROM {$wpdb->prefix}lhr_log WHERE date_added < '$expires'");
    }

    function admin_menu()
    {
        add_options_page('Log HTTP Requests', 'Log HTTP Requests', 'manage_options', 'log-http-requests', array(
            $this,
            'settings_page'
        ));
    }

    function settings_page()
    {
        include (LHR_DIR . '/templates/page-settings.php');
    }

    function admin_scripts($hook)
    {
        if ('settings_page_log-http-requests' == $hook) {
            wp_enqueue_script('lhr', LHR_URL . '/assets/js/admin.js', array(
                'jquery'
            ));
            wp_enqueue_style('lhr', LHR_URL . '/assets/css/admin.css');
            wp_enqueue_style('media-views');
        }
    }

    function lhr_query()
    {
        check_ajax_referer('lhr_nonce');

        $args = $_POST['data'];

        $output = array(
            'rows' => LHR()->query->get_results($args),
            'pager' => LHR()->query->paginate()
        );

        wp_send_json($output);
    }

    function lhr_clear()
    {
        check_ajax_referer('lhr_nonce');

        LHR()->query->truncate_table();
    }

    function start_timer($args)
    {
        $this->start_time = microtime(true);
        return $args;
    }

    function capture_request($response, $context, $transport, $args, $url)
    {
        global $wpdb;

        if (false !== strpos($url, 'doing_wp_cron')) {
            return;
        }

        // False to ignore current row
        $log_data = apply_filters('lhr_log_data', [
            'url' => $url,
            'request_args' => json_encode($args),
            'response' => json_encode($response),
            'runtime' => (microtime(true) - $this->start_time),
            'date_added' => current_time('mysql')
        ]);

        if (false !== $log_data) {
            $wpdb->insert($wpdb->prefix . 'lhr_log', $log_data);
        }
    }

    function time_since($time)
    {
        $time = current_time('timestamp') - strtotime($time);
        $time = ($time < 1) ? 1 : $time;
        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit)
                continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }
    }

    function split_url($uri)
    {
        if (0 === stripos($uri, 'http://')) {
            $protocol = 'http://';
            $uri = substr($uri, 7);
        } elseif (0 === stripos($uri, 'https://')) {
            $protocol = 'https://';
            $uri = substr($uri, 8);
        } elseif (0 === stripos($uri, ':')) {
            list ($protocol, $uri) = explode(':', $uri, 2);
            $protocol .= ':';
        } else {
            $protocol = '';
        }

        if (strpos($uri, '/') !== false) {
            list ($domain, $uri) = explode('/', $uri, 2);
            $uri = '/' . $uri;
        } else {
            $domain = $uri;
            $uri = '';
        }

        if (strpos($uri, '?') !== false) {
            list ($path, $query) = explode('?', $uri, 2);
            $path .= '?';
        } elseif ($protocol || strpos($uri, '=') === false) {
            $path = $uri . '?';
            $query = '';
        } else {
            $path = '';
            $query = $uri;
        }
        wp_parse_str($query, $parameters);

        return array(
            $protocol,
            $domain,
            $path,
            $query,
            $parameters
        );
    }

    function get_query_parameters($query)
    {
        if (strpos($query, '&') !== false) {
            list ($param1, $query) = explode('&', $query, 2);
            $ret = $param1;
            if (strpos($query, '&') !== false) {
                list ($param2, $query) = explode('&', $query, 2);
                $ret .= '&' . $param2;
                if (strpos($query, '&') !== false) {
                    $ret .= '&...';
                }
            }
            if (strlen($ret) > 100) {
                $ret = substr($ret, 0, 100) . '...';
            }
            return $ret;
        } else {
            return $query;
        }
    }

    function remove_url_parameters($url)
    {
        if (strpos($url, '?') !== false) {
            list ($base, $query) = explode('?', $url, 2);
            $base .= '?...';

            return $base;
        } else {
            return $url;
        }
    }
}

function LHR()
{
    return Log_HTTP_Requests::instance();
}

LHR();
