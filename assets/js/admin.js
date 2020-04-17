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
                    var css_class_prot = (row.protocol != 'http://' && row.protocol != 'https://') ? ' warn' : '';
                    css_class_prot = (row.protocol == 'http://') ? ' error' : css_class_prot;
                    html += `
                    <tr>
                        <td class="field-protocol` + css_class_prot + `">
                            <div><a href="javascript:;" data-id="` + idx + `">` + row.protocol + `</a></div>
                        </td>
                        <td class="field-domain">
                            <div><a href="javascript:;" data-id="` + idx + `">` + row.domain + `</a></div>
                        </td>
                        <td class="field-path">
                            <div><a href="javascript:;" data-id="` + idx + `">` + row.path + `</a></div>
                        </td>
                        <td class="field-query">
                            <div><a href="javascript:;" data-id="` + idx + `" title="` + row.query + `">` + row.short_query + `</a></div>
                        </td>
                        <td class="field-runtime` + css_class + `">` + row.runtime + `</td>
                        <td class="field-date">` + row.date_added + `</td>
                        <td class="field-since">` + row.time_since + `</td>
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
        function show_detail(id) {
        	$('.media-modal').attr('data-id',id); 
            var data = LHR.response.rows[id];
            $('.url-string').text(data.short_url);
            if (data.parameters != '[]') {
            	$('.url-parameters').text(JSON.stringify(JSON.parse(data.parameters), null, 2));
            	$('.wrapper').attr('style','grid-template-columns:30% 34% 34%');
            	$('.box-url-parameters').attr('style','display:block');
			} else {
            	$('.url-parameters').text('');
            	$('.wrapper').attr('style','grid-template-columns:49% 49%');
            	$('.box-url-parameters').attr('style','display:none');
			}
            $('.http-request-args').text(JSON.stringify(JSON.parse(data.request_args), null, 2));
            $('.http-response').text(JSON.stringify(JSON.parse(data.response), null, 2));
            $('.media-modal').show();
            $('.media-modal-backdrop').show();
        }
        
        $(document).on('click', '.field-query a, .field-protocol a, .field-domain a, .field-path a', function() {
            var id = parseInt($(this).attr('data-id'));
            show_detail(id);
         });

        // Previous or next request
        function prev_detail() {
            var id = parseInt($('.media-modal').attr('data-id'));
            if (id > 0) { 
            	id = id - 1; 
                show_detail(id);
            }	
        }

        function next_detail() {
            var id = parseInt($('.media-modal').attr('data-id'));
            if (id < 49) { 
            	id = id + 1; 
                show_detail(id);
            }	
        }
        
        $(document).on('click', '.media-modal-prev', function() { prev_detail(); } );

        $(document).on('click', '.media-modal-next', function() { next_detail(); } );

        // Close modal window
        function close_detail () {
            $('.media-modal').hide();
            $('.media-modal-backdrop').hide();        	
        }
        
        $(document).on('click', '.media-modal-close', function() { close_detail(); } );

		// Keyboard events
		$(document).keydown(function(event) {
			if($(".media-modal").is(":visible")) {
				if (event.keyCode == 27) {  //ESC
					close_detail();
				} else if (event.keyCode == 38) { //UP
					prev_detail();
				} else if (event.keyCode == 40) { //DOWN
		            next_detail();
				}
	            return false;
			}
		});

        // Ajax
        LHR.refresh();
    });
})(jQuery);
