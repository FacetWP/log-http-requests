<script>
var LHR = {
    response: [],
    query_args: {
        'orderby': 'id',
        'order': 'DESC',
        'page': 1
    },
    nonce: '<?php echo wp_create_nonce( 'lhr_nonce' ); ?>'
};
</script>

<div class="wrap">
    <h3>Log HTTP Requests</h3>

    <button class="button lhr-clear" onclick="LHR.clear()">Clear log</button>
    <button class="button lhr-refresh" onclick="LHR.refresh()">Refresh</button>
    <div class="lhr-pager"></div>
    <table class="widefat lhr-listing">
        <thead>
            <tr>
                <td>URL</td>
                <td>Runtime (sec)</td>
                <td>Date Added</td>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <div class="lhr-pager"></div>
</div>

<!-- Modal window -->

<div class="media-modal">
    <button class="button-link media-modal-close"><span class="media-modal-icon"></span></button>
    <div class="media-modal-content">
        <div class="media-frame">
            <div class="media-frame-title">
                <h1><?php _e( 'HTTP Request', 'lhr' ); ?></h1>
            </div>
            <div class="media-frame-content">
                <div class="modal-content-wrap">
                    <div class="wrapper">
                        <div class="box">
                            <h3>Request</h3>
                            <div class="http-request-args"></div>
                        </div>
                        <div class="box">
                            <h3>Response</h3>
                            <div class="http-response"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="media-modal-backdrop"></div>
