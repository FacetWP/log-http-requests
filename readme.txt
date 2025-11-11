=== Log HTTP Requests ===
Contributors: mgibbs189
Tags: log, wp_http, requests, update checks, api
Requires at least: 5.0
Tested up to: 6.8
Stable tag: trunk
License: GPLv2

Log and view all WP HTTP requests

== Description ==

= Log and view all WP HTTP requests =

How long do [core / plugin / theme] update checks take to run? What data about my site is being sent out? What about all those ajax requests? The answers to these questions are just a few clicks away.

This plugin logs all WP_HTTP requests and displays them in a table listing for easy viewing. It also stores the runtime of each HTTP request.

= Available Hooks =
Customize the length (in days) before older log items are removed:

<pre>
add_filter( 'lhr_expiration_days', function( $days ) {
    return 7; // default = 1
});
</pre>

Don't log items from a specific hostname:

<pre>
add_filter( 'lhr_log_data', function( $data ) {
    if ( false !== strpos( $data['url'], 'wordpress.org' ) ) {
        return false;
    }
    return $data;
});
</pre>

In the above example, the `$data` array keys correspond to columns within the `lhr_log` database table.

= Important Links =
* [Github →](https://github.com/FacetWP/log-http-requests)

== Installation ==

1. Download and activate the plugin.
2. Browse to `Tools > Log HTTP Requests` to view log entries.

== Changelog ==

= 1.5.0 =
* Security: Fixed SQL injection vulnerabilities in cleanup() and capture_request() methods
* Security: Added prepared statements for all database queries
* Security: Added input sanitization for POST/GET data with proper type checking
* Security: Added proper output escaping throughout templates
* Security: Fixed potential XSS vulnerability in JavaScript table rendering
* Security: Improved data validation with absint() and floatval() for numeric values
* Security: Replaced json_encode() with wp_json_encode() for better security
* Security: Added capability check to settings page for better access control
* Improvement: Updated WordPress compatibility to 6.8
* Improvement: Added text domain for internationalization support
* Improvement: Modernized code to follow WordPress coding standards
* Improvement: Replaced deprecated current_time('timestamp') with modern WordPress functions
* Improvement: Improved database table creation using dbDelta() and get_charset_collate()

= 1.4.1
* Fixed PHP8 deprecation notices

= 1.4 =
* Added extra ajax role validation (props pluginvulnerabilities.com)

= 1.3.2 =
* Escaped URL field to prevent possible XSS (props Bishop Fox)

= 1.3.1 =
* Ensured compatibility with WP 5.8

= 1.3 =
* Minor PHP cleanup
* Ensured compatibility with WP 5.7

= 1.2 =
* Moved "Log HTTP Requests" to the `Tools` menu (props @aaemnnosttv)
* Added "Status" column to show HTTP response code (props @danielbachhuber)
* Added prev/next browsing to the detail modal (props @marcissimus)
* Added keyboard support (up, down, esc) to the detail modal (props @marcissimus)
* Added raw timestamp to "Date Added" column on hover
* Added hook docs to the readme

= 1.1 =
* Added `lhr_log_data` hook to customize logged data (return FALSE to skip logging)
* Added `lhr_expiration_days` hook

= 1.0.4 =
* Minor styling tweak

= 1.0.3 =
* Better visibility for long URLs

= 1.0.2 =
* Minor design tweaks
* Replaced `json_encode` with `wp_send_json`

= 1.0.1 =
* Tested compatibility against WP 4.9.4

= 1.0.0 =
* Initial release
