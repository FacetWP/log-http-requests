<?php

global $wpdb;

$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}lhr_log ORDER BY id DESC LIMIT 50" );

?>

<style>
.widefat td {
    font-size: 12px;
}
.widefat .field-url div {
    width: 480px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.widefat .field-runtime,
.widefat .field-date {
    width: 100px;
}
.widefat .field-args,
.widefat .field-response {
    display: none;
}
.http-request-args,
.http-response {
    max-height: 360px;
    font-family: monospace;
    white-space: pre;
    overflow: auto;
}
/* grid */
.wrapper {
  display: grid;
  grid-template-columns: 50% 50%;
  grid-gap: 10px;
}

/* modal */
.media-modal,
.media-modal-backdrop {
    display: none;
}

.media-frame-title,
.media-frame-content {
    left: 0;
}

.media-frame-router {
    left: 10px;
}

.media-frame-content {
    bottom: 0;
    overflow: auto;
}

.media-modal-close {
    cursor: pointer;
}

.modal-content-wrap {
    padding: 16px;
}
</style>

<script>
(function($) {
    $(function() {
        $(document).on('click', '.field-url a', function() {
            var $parent = $(this).closest('.field-url');
            $('.http-request-args').text($parent.find('.field-args').text());
            $('.http-response').text($parent.find('.field-response').text());
            $('.media-modal').show();
            $('.media-modal-backdrop').show();
        });

        // Close modal window
        $(document).on('click', '.media-modal-close', function() {
            $('.media-modal').hide();
            $('.media-modal-backdrop').hide();
        });
    });
})(jQuery);
</script>

<div class="wrap">
    <h3>Log HTTP Requests</h3>

    <button class="button">Clear log</button>
    <table class="widefat">
        <thead>
            <tr>
                <td>URL</td>
                <td>Runtime (sec)</td>
                <td>Date Added</td>
            </tr>
        </thead>
<?php foreach ( $rows as $row ) : ?>
<?php
$args = unserialize( $row->request_args );
$response = unserialize( $row->response );
?>
        <tr>
            <td class="field-url">
                <div><a href="javascript:;"><?php echo $row->url; ?></a></div>
                <div class="field-args"><?php var_dump( $args ); ?></div>
                <div class="field-response"><?php var_dump( $response ); ?></div>
            </td>
            <td class="field-runtime"><?php echo round( $row->runtime, 4 ); ?></td>
            <td class="field-date"><?php echo LHR()->time_since( $row->date_added ); ?></td>
        </tr>
<?php endforeach; ?>
    </table>
</div>

<!-- Modal window -->

<div class="media-modal">
    <button class="button-link media-modal-close"><span class="media-modal-icon"></span></button>
    <div class="media-modal-content">
        <div class="media-frame">
            <div class="media-frame-title">
                <h1><?php _e( 'HTTP Request Viewer', 'lhr' ); ?></h1>
            </div>
            <div class="media-frame-router">
                <div class="media-router">
                    <?php _e( 'Analyze the contents of each HTTP request.', 'fwp' ); ?>
                </div>
            </div>
            <div class="media-frame-content">
                <div class="modal-content-wrap">
                    <div class="wrapper">
                        <div class="box">
                            <h3>Request Args</h3>
                            <div class="http-request-args"></div>
                        </div>
                        <div class="box">
                            <h3>HTTP Response</h3>
                            <div class="http-response"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="media-modal-backdrop"></div>
