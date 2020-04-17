=== Log HTTP Requests ===
Contributors: mgibbs189
Tags: log, wp_http, requests, update checks, api
Requires at least: 4.9
Tested up to: 5.4
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

= 1.2 =
* Split url output into protocol, domain, path, and parameters (full url is shown on mouse-over)
* Add full date column to output 
* Show protocol column in red if unsecure http was used (instead of https)
* Added display of url and parameters to detailed view
* Use ESC key to exit detailed view

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
