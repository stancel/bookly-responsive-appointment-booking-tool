jQuery(function($) {
    'use strict';
    let
        $customersList        = $('#bookly-customers-list'),
        $mergeListContainer   = $('#bookly-merge-list'),
        $mergeList            = $customersList.clone().prop('id', '').find('th:last').remove().end().appendTo($mergeListContainer),
        $filter               = $('#bookly-filter'),
        $checkAllButton       = $('#bookly-check-all'),
        $customerDialog       = $('#bookly-customer-dialog'),
        $selectForMergeButton = $('#bookly-select-for-merge'),
        $mergeWithButton      = $('[data-target=#bookly-merge-dialog]'),
        $mergeDialog          = $('#bookly-merge-dialog'),
        $mergeButton          = $('#bookly-merge',$mergeDialog),
        columns               = [],
        order                 = [],
        row
    ;

    /**
     * Init table columns.
     */
    $.each(BooklyL10n.datatables.customers.settings.columns, function (column, show) {
        if (show) {
            switch (column) {
                case 'id':
                case 'last_appointment':
                case 'total_appointments':
                case 'payments':
                case 'wp_user':
                    columns.push({data: column});
                    break;
                case 'address':
                    columns.push({data: column, render: $.fn.dataTable.render.text(), orderable: false});
                    break;
                case 'facebook':
                    columns.push({
                        data: 'facebook_id',
                        render: function (data, type, row, meta) {
                            return data ? '<a href="https://www.facebook.com/app_scoped_user_id/' + data + '/" target="_blank"><span class="dashicons dashicons-facebook"></span></a>' : '';
                        }
                    });
                    break;
                default:
                    if (column.startsWith('info_fields_')) {
                        const id = parseInt(column.split('_').pop());
                        const field =  BooklyL10n.infoFields.find( function(i) { return i.id === id; });
                        columns.push({
                            data: 'info_fields.' + id + '.value' + (field.type === 'checkboxes' ? '[, ]' : ''),
                            render: $.fn.dataTable.render.text(),
                            orderable: false
                        });
                    } else {
                        columns.push({data: column, render: $.fn.dataTable.render.text()});
                    }
                    break;
            }
        }
    });

    $.each(BooklyL10n.datatables.customers.settings.order, function (_, value) {
        const index = columns.findIndex(function (c) { return c.data === value.column; });
        if (index !== -1) {
            order.push([index, value.order]);
        }
    });

    /**
     * Init DataTables.
     */
    var dt = $customersList.DataTable({
        order       : order,
        info        : false,
        searching   : false,
        lengthChange: false,
        pageLength  : 25,
        pagingType  : 'numbers',
        processing  : true,
        responsive  : true,
        serverSide  : true,
        ajax        : {
            url : ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, {
                    action    : 'bookly_get_customers',
                    csrf_token: BooklyL10n.csrfToken,
                    filter    : $filter.val()
                });
            }
        },
        columns: columns.concat([
            {
                responsivePriority: 1,
                orderable  : false,
                searchable : false,
                width      : 120,
                render     : function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default" data-action="edit"><i class="far fa-fw fa-edit mr-1"></i>' + BooklyL10n.edit + '…</button>';
                }
            },
            {
                responsivePriority: 1,
                orderable  : false,
                searchable : false,
                render     : function (data, type, row, meta) {
                    return '<div class="custom-control custom-checkbox">' +
                        '<input value="' + row.id + '" id="bookly-dt-' + row.id + '" type="checkbox" class="custom-control-input">' +
                        '<label for="bookly-dt-' + row.id + '" class="custom-control-label"></label>' +
                        '</div>';
                }
            }
        ]),
        dom : "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing:  BooklyL10n.processing
        }
    });

    /**
     * Select all customers.
     */
    $checkAllButton.on('change', function () {
        $customersList.find('tbody input:checkbox').prop('checked', this.checked);
    });

    $customersList
        // On customer select.
        .on('change', 'tbody input:checkbox', function () {
            $checkAllButton.prop('checked', $customersList.find('tbody input:not(:checked)').length == 0);
            $mergeWithButton.prop('disabled', $customersList.find('tbody input:checked').length != 1);
        })
        // Edit customer.
        .on('click', '[data-action=edit]', function () {
            row = dt.row($(this).closest('td'));
            $customerDialog.booklyModal('show');
        });

    /**
     * On show modal.
     */
    $customerDialog
        .on('show.bs.modal', function () {
            var $title = $customerDialog.find('.modal-title');
            var $button = $customerDialog.find('.modal-footer button:first');
            var customer;
            if (row) {
                customer = $.extend(true, {}, row.data());
                $title.text(BooklyL10n.edit_customer);
                $button.text(BooklyL10n.save);
            } else {
                customer = {
                    id: '',
                    wp_user_id: '',
                    group_id: '',
                    full_name: '',
                    first_name: '',
                    last_name: '',
                    phone: '',
                    email: '',
                    country: '',
                    state: '',
                    postcode: '',
                    city: '',
                    street: '',
                    address: '',
                    info_fields: {},
                    notes: '',
                    birthday: ''
                };
                BooklyL10n.infoFields.forEach(function (field) {
                    customer.info_fields[field.id] = {id: field.id, value: field.type === 'checkboxes' ? [] : ''};
                });
                $title.text(BooklyL10n.new_customer);
                $button.text(BooklyL10n.create_customer);
            }
            customer.birthday = customer.birthday === null ? customer.birthday : moment(customer.birthday);

            var $scope = booklyAngular.element(this).scope();
            $scope.$apply(function ($scope) {
                $scope.customer = customer;
                setTimeout(function () {
                    if (BooklyL10nCustDialog.intlTelInput.enabled) {
                        $('#phone', $customerDialog).intlTelInput('setNumber', customer.phone);
                    } else {
                        $('#phone', $customerDialog).val(customer.phone);
                    }
                    $('#wp_user', $customerDialog).trigger('change.select2');
                }, 0);
            });
        })
        .on('hidden.bs.modal', function () { row = null; });

    /**
     * On filters change.
     */
    $filter.on('keyup', function () { dt.ajax.reload(); });

    /**
     * Merge list.
     */
    var mdt = $mergeList.DataTable({
        order      : [[0, 'asc']],
        info       : false,
        searching  : false,
        paging     : false,
        responsive : true,
        columns: columns.concat([
            {
                responsivePriority: 1,
                orderable         : false,
                searchable        : false,
                render            : function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default"><i class="fas fa-fw fa-times"></i></button>';
                }
            }
        ]),
        language: {
            zeroRecords: BooklyL10n.zeroRecords
        }
    });

    /**
     * Select for merge.
     */
    $selectForMergeButton.on('click', function () {
        var $checkboxes = $customersList.find('tbody input:checked');

        if ($checkboxes.length) {
            $checkboxes.each(function () {
                var data = dt.row($(this).closest('td')).data();
                if (mdt.rows().data().indexOf(data) < 0) {
                    mdt.row.add(data).draw();
                }
                this.checked = false;
            }).trigger('change');
            $mergeWithButton.show();
            $mergeListContainer.show();
            mdt.responsive.recalc();
        }
    });

    /**
     * Merge customers.
     */
    $mergeButton.on('click', function (e) {
        e.preventDefault();
        let ladda = Ladda.create(this),
            ids = [];
        ladda.start();
        mdt.rows().every(function () {
            ids.push(this.data().id);
        });
        $.ajax({
            url  : ajaxurl,
            type : 'POST',
            data : {
                action     : 'bookly_merge_customers',
                csrf_token : BooklyL10n.csrfToken,
                target_id  : $customersList.find('tbody input:checked').val(),
                ids        : ids
            },
            dataType : 'json',
            success  : function(response) {
                ladda.stop();
                $mergeDialog.booklyModal('hide');
                if (response.success) {
                    dt.ajax.reload(null, false);
                    mdt.clear();
                    $mergeListContainer.hide();
                    $mergeWithButton.hide();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    /**
     * Remove customer from merge list.
     */
    $mergeList.on('click', 'button', function () {
        mdt.row($(this).closest('td')).remove().draw();
        var any = mdt.rows().any();
        $mergeWithButton.toggle(any);
        $mergeListContainer.toggle(any);
    });

    /**
     * Import & export customers.
     */
    Ladda.bind('#bookly-import-customers-dialog button[type=submit]');
    Ladda.bind('#bookly-export-customers-dialog button[type=submit]', {timeout: 2000});

});

(function() {
    var module = booklyAngular.module('customer', ['customerDialog']);
    module.controller('customerCtrl', function($scope) {
        $scope.customer = {
            id          : '',
            wp_user_id  : '',
            group_id    : '',
            full_name   : '',
            first_name  : '',
            last_name   : '',
            phone       : '',
            email       : '',
            country     : '',
            state       : '',
            postcode    : '',
            city        : '',
            street      : '',
            address     : '',
            info_fields : [],
            notes       : '',
            birthday    : ''
        };
        $scope.saveCustomer = function(customer) {
            jQuery('#bookly-customers-list').DataTable().ajax.reload(function () {
                jQuery('#bookly-customers-list').DataTable().responsive.recalc();
            }, false);
        };
    });
})();