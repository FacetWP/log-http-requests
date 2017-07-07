(function($) {
    $(function() {

        // Refresh
        LHR.refresh = function() {
            $('.lhr-refresh').text('Refreshing...').attr('disabled', 'disabled');

            $.post(ajaxurl, {
                'action': 'lhr_query',
                '_wpnonce': LHR.nonce,
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
                $('.lhr-refresh').text('Refresh').removeAttr('disabled');
            }, 'json');
        }

        // Clear
        LHR.clear = function() {
            $('.lhr-clear').text('Clearing...').attr('disabled', 'disabled');

            $.post(ajaxurl, {
                'action': 'lhr_clear',
                '_wpnonce': LHR.nonce
            }, function(data) {
                $('.lhr-listing tbody').html('');
                $('.lhr-clear').text('Clear log').removeAttr('disabled');
            }, 'json');
        }

        // Page change
        $(document).on('click', '.lhr-page:not(.active)', function() {
            LHR.query_args.page = parseInt($(this).attr('data-page'));
            LHR.refresh();
        });

        // Open detail modal
        $(document).on('click', '.field-url a', function() {
            var id = parseInt($(this).attr('data-id'));
            var data = LHR.response.rows[id];
            $('.http-request-args').text(JSON.stringify(JSON.parse(data.request_args), null, 2));
            $('.http-response').text(JSON.stringify(JSON.parse(data.response), null, 2));
            $('.media-modal').show();
            $('.media-modal-backdrop').show();
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