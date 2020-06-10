jQuery(function($) {

    var $message_list = $('#bookly-messages-list');

    /**
     * Init DataTables.
     */
    var dt = $message_list.DataTable({
        pageLength  : 25,
        pagingType  : 'numbers',
        lengthChange: false,
        ordering: false,
        info: false,
        searching: false,
        processing: true,
        responsive: true,
        ajax: {
            url: ajaxurl,
            data: { action: 'bookly_get_messages', csrf_token : BooklyL10n.csrf_token }
        },
        fnFooterCallback: function( nFoot, aData, iStart, iEnd, aiDisplay ) {
            var message_ids =[];
            for (var i = iStart; i < iEnd; i++) {
                if (aData[i].seen == 0) {
                    // Add new messages
                    message_ids.push(aData[i].message_id)
                }
            }
            if (message_ids.length > 0) {
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : {
                        action     : 'bookly_mark_read_messages',
                        message_ids: message_ids,
                        csrf_token : BooklyL10n.csrf_token
                    },
                    dataType : 'json',
                    success  : function (response) {
                        if (response.success) {

                        }
                    }
                });
            }
        },
        columns: [
            { data: 'created' },
            {
                data: 'subject',
                render: function (data, type, row, meta) {
                    if (row.seen != 1) {
                        return '<b>' + data + '</b>';
                    }
                    return data;
                }
            },
            { data: 'body' }
        ],
        dom       : "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
        language: {
            zeroRecords: BooklyL10n.datatable.zeroRecords,
            processing:  BooklyL10n.datatable.processing,
        }
    });

});