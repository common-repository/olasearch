jQuery(document).ready(function ($) {
    var reIndex = false;

    function progress(percent, $element) {
        var progressBarWidth = percent * $element.width() / 100;
        $element.find('div').animate({width: progressBarWidth}, 0).html(percent + "%&nbsp;");
    }

    function notice(text, error) {
        var note = '<div class="is-dismissible notice notice-' + ( error ? 'error' : 'success' ) + '"><p>' + text + '</p><button id="ajax-note-close" type="button" class="notice-dismiss"></button></div>';
        $("#ajax-note").html(note);
        $("button.notice-dismiss").click(clearNote);
        tb_remove();
    }

    function clearNote() {
        $("#ajax-note").text("");
    }

    function process(initial, reIndex, status) {
        data = {action: 'do_index'};
        if (reIndex) {
            data.re_index = true;
        }
        if (initial) {
            data.initial = true;
            $('#progress-info').text('batch ');
            progress(0, $('#progress-bar'));
            status = {
                batch: 0,
                indexed: 0,
                failed: 0
            }
        }

        // check and set last batch true variable
        if (status && status.batch !== 0 && status.batch === status.no_of_batches - 1) {
            data.last_batch = true
        }

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: data,
            success: function (response) {
                status.batch += 1;
                if (initial) {
                    status.total_posts = response.data.total_posts;
                    status.no_of_batches = response.data.no_of_batches;
                }
                $('#progress-info').text('batch ' + status.batch + ' of ' + status.no_of_batches);
                progress(Math.round(status.batch / status.no_of_batches * 100), $('#progress-bar'));

                if (!response.data.error) {
                    status.indexed += response.data.current_batch_total;
                } else {
                    status.failed += status.total_posts - status.indexed;
                }

                if (!response.data.error && status.no_of_batches > status.batch) {
                    process(false, false, status);
                } else {
                    if (response.data.error) {
                        notice('Failed to index ' + status.failed + ' documents.' + (status.indexed ? ' Indexed only ' + status.indexed + ' documents.' : '') + '<br>Error: ' + response.data.error + (response.data.code ? '(' + response.data.code + ' error)' : ''), true);
                    } else {
                        notice('Successfully indexed ' + status.indexed + ' documents.', false);
                        if (!reIndex) {
                            var $indexBtn = $('#index-data');
                            var $reIndexBtn = $('#re-index-data');
                            if ($reIndexBtn.length) {
                                $indexBtn.next('p').remove();
                                $indexBtn.next('hr').remove();
                                $indexBtn.remove();
                            } else {
                                $indexBtn.attr('id', 're-index-data').attr('name', 're_index_data').val('Send all content for re-indexing');
                            }
                        }
                    }
                }
            }
        });
    }

    $("form#index").submit(function (event) {
        clearNote();
        var btn = $(this).find("input[type=submit]:focus");
        switch (btn[0].id) {
            case "re-index-data":
                event.preventDefault();
                if (confirm("Are you sure you want to send all content for re-indexing?")) {
                    tb_show('Reindexing in progress', '#TB_inline?width=400&height=100&inlineId=progress-window');
                    reIndex = true;
                    process(true, reIndex);
                }
                break;
            case "index-data":
                event.preventDefault();
                if (confirm("Are you sure you want to send all content for indexing?")) {
                    tb_show('Indexing in progress', '#TB_inline?width=400&height=100&inlineId=progress-window');
                    reIndex = false;
                    process(true, reIndex);
                }
                break;
            case "wipe-data":
                if (!confirm("Are you sure you want to clear all indexed data?")) {
                    event.preventDefault();
                }
                break;
        }
    });
});