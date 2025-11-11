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
                    
                    // Format date on client-side based on local timezone
                    var dateStr = LHR.formatTimeSince(row.date_timestamp);
                    var dateTitle = new Date(row.date_timestamp * 1000).toLocaleString();
                    
                    var tr = $('<tr>');
                    var urlTd = $('<td class="field-url">').append(
                        $('<div>').append(
                            $('<a href="javascript:;">').attr('data-id', idx).text(row.url)
                        )
                    );
                    tr.append(urlTd);
                    tr.append($('<td class="field-status-code">').text(row.status_code));
                    tr.append($('<td class="field-runtime">').addClass(css_class).text(row.runtime));
                    tr.append($('<td class="field-date">').attr('title', dateTitle).text(dateStr));
                    
                    html += tr.prop('outerHTML');
                });
                $('.lhr-listing tbody').html(html);
                $('.lhr-pager').html(data.pager);
                $('.lhr-refresh').text('Refresh').removeAttr('disabled');
            }, 'json');
        }

        // Format timestamp to "X time ago" format
        LHR.formatTimeSince = function(timestamp) {
            var now = Math.floor(Date.now() / 1000);
            var diff = now - timestamp;
            diff = (diff < 1) ? 1 : diff;
            
            var tokens = {
                31536000: 'year',
                2592000: 'month',
                604800: 'week',
                86400: 'day',
                3600: 'hour',
                60: 'minute',
                1: 'second'
            };
            
            for (var unit in tokens) {
                if (diff < unit) continue;
                var numberOfUnits = Math.floor(diff / unit);
                return numberOfUnits + ' ' + tokens[unit] + (numberOfUnits > 1 ? 's' : '');
            }
            
            return '1 second';
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

        LHR.show_details = function(action) {
            var id = LHR.active_id;

            if ('next' == action && id < LHR.response.rows.length - 1) {
                id = id + 1;
            }
            else if ('prev' == action && id > 0) {
                id = id - 1;
            }

            LHR.active_id = id;

            var data = LHR.response.rows[id];
            $('.http-url').text(data.url);
            $('.http-request-id').text(id);
            $('.http-request-args').text(JSON.stringify(JSON.parse(data.request_args), null, 2));
            $('.http-response').text(JSON.stringify(JSON.parse(data.response), null, 2));
            $('.media-modal').addClass('open');
            $('.media-modal-backdrop').addClass('open');  
        }

        // Page change
        $(document).on('click', '.lhr-page:not(.active)', function() {
            LHR.query_args.page = parseInt($(this).attr('data-page'));
            LHR.refresh();
        });

        // Open detail modal
        $(document).on('click', '.field-url a', function() {
            LHR.active_id = parseInt($(this).attr('data-id'));
            LHR.show_details('curr');
        });

        // Close modal window
        $(document).on('click', '.media-modal-close', function() {
            var $this = $(this);

            if ($this.hasClass('prev') || $this.hasClass('next')) {
                var action = $this.hasClass('prev') ? 'prev' : 'next';
                LHR.show_details(action);
                return;
            }

            $('.media-modal').removeClass('open');
            $('.media-modal-backdrop').removeClass('open');
            $(document).off('keydown.lhr-modal-close');
        });

        $(document).keydown(function(e) {

            if (! $('.media-modal').hasClass('open')) {
                return;
            }

            if (-1 < $.inArray(e.keyCode, [27, 38, 40])) {
                e.preventDefault();

                if (27 == e.keyCode) { // esc
                    $('.media-modal-close').click();
                }
                else if (38 == e.keyCode) { // up
                    $('.media-modal-close.prev').click();
                }
                else if (40 == e.keyCode) { // down
                    $('.media-modal-close.next').click();
                }
            }
        });

        // Ajax
        LHR.refresh();
    });
})(jQuery);
