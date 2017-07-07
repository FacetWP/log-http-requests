<?php

$nonce = wp_create_nonce( 'lhr_nonce' );

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
.widefat .field-runtime.warn {
    background-color: rgba(255, 235, 59, 0.2);
}
.widefat .field-runtime.error {
    background-color: rgba(244, 67, 54, 0.2);
}
.http-request-args,
.http-response {
    max-height: 360px;
    font-family: monospace;
    white-space: pre;
    overflow: auto;
}

/* pager */

.lhr-pager {
    padding: 10px 0;
    text-align: right;
}

.lhr-page {
    display: inline-block;
    padding: 0px 4px;
    margin-right: 6px;
    cursor: pointer;
}

.lhr-page.active {
    font-weight: bold;
    cursor: default;
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
var LHR = {
    response: [],
    query_args: {
        'orderby': 'id',
        'order': 'DESC',
        'page': 1
    }
};

(function($) {
    $(function() {

        LHR.refresh = function() {
            $.post(ajaxurl, {
                'action': 'lhr_query',
                '_wpnonce': '<?php echo $nonce; ?>',
                'data': LHR.query_args
            }, function(data) {
                LHR.response = data;
                LHR.query_args.page = 1;

                var html = '';
                $.each(data.rows, function(idx, row) {
                    var runtime = parseFloat(row.runtime);
                    var css_class = (runtime > 1) ? ' warn' : '';
                    css_class = (runtime > 2) ? ' error' : css_class;
                    html += `
                    <tr>
                        <td class="field-url">
                            <div><a href="javascript:;" data-id="` + idx + `">` + row.url + `</a></div>
                        </td>
                        <td class="field-runtime` + css_class + `">` + row.runtime + `</td>
                        <td class="field-date">` + row.date_added + `</td>
                    </tr>
                    `;
                });
                $('.lhr-listing tbody').html(html);
                $('.lhr-pager').html(data.pager);
            }, 'json');
        }

        LHR.clear = function() {
            $.post(ajaxurl, {
                'action': 'lhr_clear',
                '_wpnonce': '<?php echo $nonce; ?>'
            }, function(data) {
                $('.lhr-listing tbody').html('');
            }, 'json');
        }

        $(document).on('click', '.field-url a', function() {
            var id = parseInt($(this).attr('data-id'));
            var data = LHR.response[id];
            $('.http-request-args').text(JSON.stringify(JSON.parse(data.request_args), null, 2));
            $('.http-response').text(JSON.stringify(JSON.parse(data.response), null, 2));
            $('.media-modal').show();
            $('.media-modal-backdrop').show();
        });

        // Page change
        $(document).on('click', '.lhr-page:not(.active)', function() {
            LHR.query_args.page = parseInt($(this).attr('data-page'));
            LHR.refresh();
        });

        // Close modal window
        $(document).on('click', '.media-modal-close', function() {
            $('.media-modal').hide();
            $('.media-modal-backdrop').hide();
        });

        // Ajax
        LHR.refresh();
    });
})(jQuery);
</script>

<div class="wrap">
    <h3>Log HTTP Requests</h3>

    <button class="button" onclick="LHR.clear()">Clear log</button>
    <button class="button" onclick="LHR.refresh()">Refresh</button>
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
