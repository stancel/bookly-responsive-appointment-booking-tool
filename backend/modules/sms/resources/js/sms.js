jQuery(function($) {
    'use strict';

    let $form_confirm  = $('.bookly-js-confirm-form'),
        $form_forgot   = $('.bookly-js-forgot-form'),
        $form_invoice  = $('.bookly-js-invoice'),
        $form_register = $('.bookly-js-register-form'),
        $form_login    = $('.bookly-js-login-form')
    ;

    booklyAlert(BooklyL10n.smsAlert);

    $('.bookly-js-show-register-form').on('click', function (e) {
        e.preventDefault();
        $form_confirm.hide();
        $form_login.hide();
        $form_register.show();
        $form_forgot.hide();
    });

    $('.bookly-js-show-login-form').on('click', function (e) {
        e.preventDefault();
        $form_confirm.hide();
        $form_login.show();
        $form_register.hide();
        $form_forgot.hide();
    });

    $('.bookly-js-show-forgot-form').on('click', function (e) {
        e.preventDefault();
        $form_confirm.hide();
        $form_forgot.show();
        $form_login.hide();
        $form_register.hide();
    });

    $('.bookly-js-form-forgot-next').on('click', function (e) {
        e.preventDefault();
        var $btn  = $(this),
            $form = $(this).parents('form'),
            $code = $form.find('input[name="code"]'),
            $pwd  = $form.find('input[name="password"]'),
            $username   = $form.find('input[name="username"]'),
            $pwd_repeat = $form.find('input[name="password_repeat"]'),
            data  = { action: 'bookly_forgot_password', step: $btn.data('step'), username: $username.val(), csrf_token : BooklyL10n.csrfToken };
        switch ($(this).data('step')) {
            case 0:
                forgot_helper( data, function() {
                    $username.parent().addClass('hidden');
                    $code.parent().removeClass('hidden');
                    $btn.data('step', 1);
                });
                break;
            case 1:
                data.code = $code.val();
                forgot_helper(data, function() {
                    $code.parent().addClass('hidden');
                    $pwd.parent().removeClass('hidden');
                    $pwd_repeat.parent().removeClass('hidden');
                    $btn.data('step', 2);
                });
                break;
            case 2:
                data.code = $code.val();
                data.password = $pwd.val();
                data.password_repeat = $pwd_repeat.val();
                if (data.password == data.password_repeat && data.password != '') {
                    forgot_helper(data, function() {
                        $('.bookly-js-show-login-form').trigger('click');
                        $btn.data('step', 0);
                        $username.parent().removeClass('hidden');
                        $pwd.parent().addClass('hidden');
                        $pwd_repeat.parent().addClass('hidden');
                        $form.trigger('reset');
                    });
                } else {
                    booklyAlert({error: [BooklyL10n.passwords_no_same]});
                }
                break;
        }
    });

    $('.bookly-js-resend-confirmation').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url         : ajaxurl,
            data        : {
                action: 'bookly_resend_confirmation',
                csrf_token : BooklyL10n.csrfToken,
            },
            dataType    : 'json',
            xhrFields   : { withCredentials: true },
            crossDomain : 'withCredentials' in new XMLHttpRequest(),
            success     : function (response) {
                if (response.success) {
                    booklyAlert({success: [response.data.message]});
                } else {
                    booklyAlert({error: [response.data.message]});
                }
            }
        });
    });

    $('#bookly-js-confirm-sms-account').on('click', function () {
        let ladda   = Ladda.create(this);
        ladda.start();
        $.ajax({
            method     : 'POST',
            url        : ajaxurl,
            data       : {
                action: 'bookly_complete_sms_registration',
                code:   $('.bookly-js-confirmation-code').val(),
                csrf_token : BooklyL10n.csrfToken,
            },
            dataType   : 'json',
            xhrFields  : {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success    : function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    if (response.data && response.data.message) {
                        booklyAlert({error: [response.data.message]});
                    }
                }
                ladda.stop();
            }
        });
    });

    function forgot_helper(data, callback) {
        $.ajax({
            method     : 'POST',
            url        : ajaxurl,
            data       : data,
            dataType   : 'json',
            xhrFields  : {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success    : function (response) {
                if (response.success) {
                    callback();
                } else {
                    if (response.data && response.data.message) {
                        if (response.data.hasOwnProperty('code')) {
                            if (response.data.code == 'ERROR_EMAIL_CONFIRM_REQUIRED') {
                                $form_confirm.show();
                                $form_forgot.hide();
                                $form_login.hide();
                                $form_register.hide();
                            }
                        }
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }
        });
    }

    $form_invoice
        .on('click', 'button', function () {
            var ladda   = Ladda.create(this),
                invalid = false,
                data    = $form_invoice.serializeArray();
            $('input[required]', $form_invoice).each(function () {
                if ($(this).val() == '') {
                    $(this).addClass('is-invalid');
                    invalid = true;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (invalid) {
                return false;
            } else {
                ladda.start();
                data.push({name: 'action', value: 'bookly_save_invoice_data'});
                data.push({name: 'csrf_token', value: BooklyL10n.csrfToken});
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : data,
                    dataType : 'json',
                    success  : function(response) {
                        if (response.success) {
                            booklyAlert({success: [response.data.message]});
                        } else {
                            booklyAlert({error: [response.data.message]});
                        }
                        ladda.stop();
                    }
                });
            }
        });


    $('.bookly-js-checkboxes :checkbox').on('change', function () {
        let $checkbox = $(this),
            $label = $checkbox.next('label'),
            $img = $checkbox.parent().prev('img');
        $label.removeClass('custom-control-label');
        $label.css({marginBottom: 0, verticalAlign: 'top'});
        $img.show();
        $.get(ajaxurl, {action: 'bookly_admin_notify', csrf_token : BooklyL10n.csrfToken, option_name: $checkbox.attr('name'), value: $checkbox.is(':checked') ? 1 : 0 }, function () {}, 'json').always(function () {
            $label.addClass('custom-control-label');
            $img.hide();
        });
    });

    $('#ajax-send-change-password').on('click', function (e) {
        e.preventDefault();
        var $form = $('#form-change-password');
        var new_password = $form.find('#new_password').val();
        if ($form.find('#old_password').val() != '') {
            if (new_password == $form.find('#new_password_repeat').val() && new_password != '') {
                $.ajax({
                    type        : 'POST',
                    url         : ajaxurl,
                    data        : $form.serialize(),
                    dataType    : 'json',
                    xhrFields   : { withCredentials: true },
                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                    success     : function (response) {
                        if (response.success) {
                            $('#modal_change_password').booklyModal('hide');
                            $form.trigger('reset');
                        } else {
                            if (response.data && response.data.message) {
                                booklyAlert({error: [response.data.message]});
                            }
                        }
                    }
                });
            } else {
                booklyAlert({error: [BooklyL10n.passwords_no_same]});
            }
        } else {
            booklyAlert({error: [BooklyL10n.input_old_password]});
        }
    });

    var $phone_input = $('#admin_phone');
    if (BooklyL10n.intlTelInput.enabled) {
        $phone_input.intlTelInput({
            preferredCountries: [BooklyL10n.intlTelInput.country],
            initialCountry: BooklyL10n.intlTelInput.country,
            geoIpLookup: function (callback) {
                $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: BooklyL10n.intlTelInput.utils
        });
    }
    $('#bookly-js-submit-notifications').on('click', function (e) {
        e.preventDefault();
        var ladda = Ladda.create(this);
        ladda.start();
        var $form = $(this).parents('form');
        $form.bookly_sms_administrator_phone = getPhoneNumber();
        $form.submit();
    });
    $('#send_test_sms').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url         : ajaxurl,
            data        : {
                action: 'bookly_send_test_sms',
                csrf_token : BooklyL10n.csrfToken,
                phone_number: getPhoneNumber() },
            dataType    : 'json',
            xhrFields   : { withCredentials: true },
            crossDomain : 'withCredentials' in new XMLHttpRequest(),
            success     : function (response) {
                if (response.success) {
                    booklyAlert({success: [response.message]});
                } else {
                    booklyAlert({error: [response.message]});
                }
            }
        });
    });

    /**
     * Auto-Recharge Tab.
     */
    var $recharge_init    = $('#bookly-auto-recharge-init'),
        $recharge_decline = $('#bookly-auto-recharge-decline'),
        $recharge_amount  = $('#bookly-recharge-amount')
        ;
    $recharge_init.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();
        $.get(ajaxurl, {action: 'bookly_init_auto_recharge', csrf_token : BooklyL10n.csrfToken, amount: $recharge_amount.val()}, function () {
        }, 'json').always(function (response) {
            if (response.success) {
                window.location.replace(response.data.paypal_preapproval);
            } else {
                ladda.stop();
                booklyAlert({error: [response.data.message]});
            }
        });
    });
    $recharge_decline.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();
        $.get(ajaxurl, {action: 'bookly_decline_auto_recharge', csrf_token : BooklyL10n.csrfToken}, function () {}, 'json')
            .always(function (response) {
                ladda.stop();
                if (response.success) {
                    $recharge_amount.prop('disabled', false);
                    $recharge_init.prop('disabled', false);
                    $recharge_decline.prop('disabled', true);
                    booklyAlert({success: [response.data.message]});
                } else {
                    booklyAlert({error: [response.data.message]});
                }
            });
    });

    /**
     * Date range pickers options.
     */
    var picker_ranges = {};
    picker_ranges[BooklyL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[BooklyL10n.dateRange.today]     = [moment(), moment()];
    picker_ranges[BooklyL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
    picker_ranges[BooklyL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
    picker_ranges[BooklyL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[BooklyL10n.dateRange.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
    var locale = $.extend({},BooklyL10n.dateRange, BooklyL10n.datePicker);

    /**
     * Purchases Tab.
     */
    $('[href="#purchases"]').one('click', function() {
        var $date_range = $('#purchases_date_range');
        $date_range.daterangepicker(
            {
                parentEl : $date_range.parent(),
                startDate: moment().subtract(30, 'days'), // by default select "Last 30 days"
                ranges   : picker_ranges,
                locale   : locale,
                showDropdowns: true,
                linkedCalendars: false,
            },
            function (start, end) {
                var format = 'YYYY-MM-DD';
                $date_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.dateRange.format) + ' - ' + end.format(BooklyL10n.dateRange.format));
            }
        );

        /**
         * Init Columns.
         */
        let columns = [];

        $.each(BooklyL10n.datatables.sms_purchases.settings.columns, function (column, show) {
            if (show) {
                columns.push({data: column});
            }
        });
        columns.push({
            className: "text-right",
            render   : function (data, type, row, meta) {
                return '<button type="button" class="btn btn-default" data-action="download-invoice"><i class="far fa-fw fa-file-pdf mr-1"></i> ' + BooklyL10n.invoice.button + '</a>';
            }
        });

        var dt = $('#bookly-purchases').DataTable({
            ordering: false,
            paging: false,
            info: false,
            searching: false,
            processing: true,
            responsive: true,
            ajax: {
                url : ajaxurl,
                data: function (d) {
                    return {
                        action: 'bookly_get_purchases_list',
                        csrf_token: BooklyL10n.csrfToken,
                        range:  $date_range.data('date')
                    };
                },
                dataSrc: 'list'
            },
            columns: columns,
            language: {
                zeroRecords: BooklyL10n.zeroRecords,
                processing:  BooklyL10n.processing
            }
        });

        $date_range.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    });

    $('#bookly-purchases')
        .on('click', '[data-action=download-invoice]', function () {
            if ($('#bookly_sms_invoice_company_name').val() == '') {
                booklyAlert({error: [BooklyL10n.invoice.alert]});
                $('#collapse_invoice').collapse('show');
                $('#bookly_sms_invoice_company_name').focus();
            } else {
                var data = $('#bookly-purchases').DataTable().row($(this).closest('td')).data();
                window.location = BooklyL10n.invoice.link + '/' + data.order;
            }
        });

    /**
     * SMS Details Tab.
     */
    $('[href="#sms_details"]').one('click', function() {
        var $date_range = $('#sms_date_range');
        $date_range.daterangepicker(
            {
                parentEl : $date_range.parent(),
                startDate: moment().subtract(30, 'days'), // by default select "Last 30 days"
                ranges   : picker_ranges,
                locale   : locale,
                showDropdowns: true,
                linkedCalendars: false,
            },
            function (start, end) {
                var format = 'YYYY-MM-DD';
                $date_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.dateRange.format) + ' - ' + end.format(BooklyL10n.dateRange.format));
            }
        );

        /**
         * Init Columns.
         */
        let columns = [];

        $.each(BooklyL10n.datatables.sms_details.settings.columns, function (column, show) {
            if (show) {
                columns.push({data: column});
            }
        });
        if (columns.length) {
            var dt = $('#bookly-sms').DataTable({
                ordering  : false,
                paging    : false,
                info      : false,
                searching : false,
                processing: true,
                responsive: true,
                ajax      : {
                    url    : ajaxurl,
                    data   : function (d) {
                        return {
                            action    : 'bookly_get_sms_list',
                            csrf_token: BooklyL10n.csrfToken,
                            range     : $date_range.data('date')
                        };
                    },
                    dataSrc: 'list'
                },
                columns   : columns,
                language  : {
                    zeroRecords: BooklyL10n.zeroRecords,
                    processing : BooklyL10n.processing
                }
            });
        }

        $date_range.on('apply.daterangepicker', function () { dt.ajax.reload(); });
        $(this).on('click', function () { dt.ajax.reload(); });
    });

    /**
     * Prices Tab.
     */
    $("[href='#price_list']").one('click', function() {
        fillPriceTable();
    });
    if ($('form.bookly-js-login-form').length){
        fillPriceTable();
    }
    $('[data-action=save-administrator-phone]')
        .on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'bookly_save_administrator_phone',
                    bookly_sms_administrator_phone: getPhoneNumber(),
                    csrf_token: BooklyL10n.csrfToken
                },
                success: function (response) {
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.settingsSaved]});
                    }
                }
            });
        });

    function fillPriceTable() {
        /**
         * Init Columns.
         */
        let columns = [];

        $.each(BooklyL10n.datatables.sms_prices.settings.columns, function (column, show) {
            if (show) {
                switch (column) {
                    case 'country_iso_code':
                        columns.push({
                            data: column, render: function ( data, type, row, meta ) {
                                return '<div class="iti-flag ' + data + '"></div>';
                            }
                        });
                        break;
                    case 'price':
                        columns.push({
                            data: column,
                            className: "text-right",
                            render: function ( data, type, row, meta ) {
                                return '$' + data.replace(/0+$/, '');
                            }
                        });
                        break;
                    case 'price_alt':
                        columns.push({
                            data: column,
                            className: "text-right",
                            render: function ( data, type, row, meta ) {
                                if (row.price_alt == '') {
                                    return '-';
                                } else {
                                    return '$' + data.replace(/0+$/, '');
                                }
                            }
                        });
                        break;
                    default:
                        columns.push({data: column});
                        break;
                }
            }
        });
        if (columns.length) {
            var dt = $('#bookly-prices').DataTable({
                ordering  : false,
                paging    : false,
                info      : false,
                searching : false,
                processing: true,
                responsive: true,
                ajax      : {
                    url    : ajaxurl,
                    data   : {action: 'bookly_get_price_list', csrf_token: BooklyL10n.csrfToken},
                    dataSrc: 'list'
                },
                columns   : columns,
                language  : {
                    zeroRecords: BooklyL10n.zeroRecords,
                    processing : BooklyL10n.processing
                }
            });
        }
    }

    function getPhoneNumber() {
        var phone_number;
        try {
            phone_number = BooklyL10n.intlTelInput.enabled ? $phone_input.intlTelInput('getNumber') : $phone_input.val();
            if (phone_number == '') {
                phone_number = $phone_input.val();
            }
        } catch (error) {  // In case when intlTelInput can't return phone number.
            phone_number = $phone_input.val();
        }

        return phone_number;
    }

    /**
     * Sender ID Tab.
     */
    $("[href='#sender_id']").one('click', function() {
        var $request_sender_id = $('#bookly-request-sender_id'),
            $reset_sender_id   = $('#bookly-reset-sender_id'),
            $cancel_sender_id  = $('#bookly-cancel-sender_id'),
            $sender_id         = $('#bookly-sender-id-input');

        /**
         * Init Columns.
         */
        let columns = [];

        $.each(BooklyL10n.datatables.sms_sender.settings.columns, function (column, show) {
            if (show) {
                columns.push({data: column});
            }
        });
        if (columns.length) {
            var dt = $('#bookly-sender-ids').DataTable({
                ordering  : false,
                paging    : false,
                info      : false,
                searching : false,
                processing: true,
                responsive: true,
                ajax      : {
                    url    : ajaxurl,
                    data   : {action: 'bookly_get_sender_ids_list', csrf_token: BooklyL10n.csrfToken},
                    dataSrc: function (json) {
                        if (json.pending) {
                            $sender_id.val(json.pending);
                            $request_sender_id.hide();
                            $sender_id.prop('disabled', true);
                            $cancel_sender_id.show();
                        }

                        return json.list;
                    }
                },
                columns   : columns,
                language  : {
                    zeroRecords: BooklyL10n.zeroRecords2,
                    processing : BooklyL10n.processing
                }
            });
        }

        $request_sender_id.on('click', function () {
            var ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                data : {action: 'bookly_request_sender_id', csrf_token : BooklyL10n.csrfToken, 'sender_id': $sender_id.val()},
                dataType : 'json',
                xhrFields: {withCredentials: true},
                success: function (response) {
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.sender_id.sent]});
                        $request_sender_id.hide();
                        $sender_id.prop('disabled',true);
                        $cancel_sender_id.show();
                        dt.ajax.reload();
                    } else {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }).always(function () {
                ladda.stop();
            });
        });

        $reset_sender_id.on('click', function (e) {
            e.preventDefault();
            if (confirm(BooklyL10n.areYouSure)) {
                $.ajax({
                    url: ajaxurl,
                    data: {action: 'bookly_reset_sender_id', csrf_token : BooklyL10n.csrfToken},
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    success: function (response) {
                        if (response.success) {
                            booklyAlert({success: [BooklyL10n.sender_id.set_default]});
                            $('.bookly-js-sender-id').html('Bookly');
                            $('.bookly-js-approval-date').remove();
                            $sender_id.prop('disabled', false).val('');
                            $request_sender_id.show();
                            $cancel_sender_id.hide();
                            dt.ajax.reload();
                        } else {
                            booklyAlert({error: [response.data.message]});
                        }
                    }
                });
            }
        });

        $cancel_sender_id.on('click',function () {
            if (confirm(BooklyL10n.areYouSure)) {
                var ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    method     : 'POST',
                    url        : ajaxurl,
                    data:      {action: 'bookly_cancel_sender_id', csrf_token : BooklyL10n.csrfToken},
                    dataType   : 'json',
                    success    : function (response) {
                        if (response.success) {
                            $sender_id.prop('disabled', false).val('');
                            $request_sender_id.show();
                            $cancel_sender_id.hide();
                            dt.ajax.reload();
                        } else {
                            if (response.data && response.data.message) {
                                booklyAlert({error: [response.data.message]});
                            }
                        }
                    }
                }).always(function () {
                    ladda.stop();
                });
            }
        });
        $(this).on('click', function () { dt.ajax.reload(); });
    });

    $('#bookly-open-tab-sender-id').on('click', function (e) {
        e.preventDefault();
        $('#sms_tabs li a[href="#sender_id"]').trigger('click');
    });

    $('[href="#' + BooklyL10n.current_tab + '"]').click();
});