jQuery(function ($) {
    let $helpBtn            = $('#bookly-help-btn'),
        $businessHours      = $('#business-hours'),
        $companyLogo        = $('#bookly-js-company-logo'),
        $finalStepUrl       = $('input[name=bookly_url_final_step_url]'),
        $finalStepUrlMode   = $('#bookly_settings_final_step_url_mode'),
        $participants       = $('#bookly_appointment_participants'),
        $defaultCountry     = $('#bookly_cst_phone_default_country'),
        $defaultCountryCode = $('#bookly_cst_default_country_code'),
        $gcSyncMode         = $('#bookly_gc_sync_mode'),
        $gcLimitEvents      = $('#bookly_gc_limit_events'),
        $gcFullSyncOffset   = $('#bookly_gc_full_sync_offset_days'),
        $gcFullSyncTitles   = $('#bookly_gc_full_sync_titles'),
        $ocSyncMode         = $('#bookly_oc_sync_mode'),
        $ocLimitEvents      = $('#bookly_oc_limit_events'),
        $ocFullSyncOffset   = $('#bookly_oc_full_sync_offset_days'),
        $ocFullSyncTitles   = $('#bookly_oc_full_sync_titles'),
        $currency           = $('#bookly_pmt_currency'),
        $formats            = $('#bookly_pmt_price_format')
    ;

    booklyAlert(BooklyL10n.alert);

    Ladda.bind('button[type=submit]');

    // Customers tab.
    $.each($.fn.intlTelInput.getCountryData(), function (index, value) {
        $defaultCountry.append('<option value="' + value.iso2 + '" data-code="' + value.dialCode + '">' + value.name + ' +' + value.dialCode + '</option>');
    });
    $defaultCountry.val(BooklyL10n.default_country);
    $defaultCountry.on('change', function () {
        $defaultCountryCode.val($defaultCountry.find('option:selected').data('code'));
    });
    let $sortableAddress = $('#bookly_cst_address_show_fields');
    if ($sortableAddress.length) {
        Sortable.create($sortableAddress[0], {
            handle: '.bookly-js-draghandle'
        });
    }
    $('#bookly-customer-reset').on('click', function (event) {
        $defaultCountry.val($defaultCountry.data('country'));
    });

    // Google Calendar tab.
    $gcSyncMode.on('change', function () {
        $gcLimitEvents.closest('.form-group').toggle(this.value == '1.5-way');
        $gcFullSyncOffset.closest('.form-group').toggle(this.value == '2-way');
        $gcFullSyncTitles.closest('.form-group').toggle(this.value == '2-way');
    }).trigger('change');

    // Outlook Calendar tab.
    $ocSyncMode.on('change', function () {
        $ocLimitEvents.closest('.form-group').toggle(this.value == '1.5-way');
        $ocFullSyncOffset.closest('.form-group').toggle(this.value == '2-way');
        $ocFullSyncTitles.closest('.form-group').toggle(this.value == '2-way');
    }).trigger('change');

    // Calendar tab.
    $participants.on('change', function () {
        $('#bookly_cal_one_participant').hide();
        $('#bookly_cal_many_participants').hide();
        $('#' + this.value).show();
    }).trigger('change');
    $("#bookly_settings_calendar button[type=reset]").on( 'click', function () {
        setTimeout(function () {
            $participants.trigger('change');
        }, 50 );
    });

    // Company tab.
    $companyLogo.find('.bookly-js-delete').on('click', function () {
        let $thumb = $companyLogo.find('.bookly-js-image');
        $thumb.attr('style', '');
        $companyLogo.find('[name=bookly_co_logo_attachment_id]').val('');
        $(this).hide();
    });
    $companyLogo.find('.bookly-js-edit').on('click', function() {
        let frame = wp.media({
            library: {type: 'image'},
            multiple: false
        });
        frame.on('select', function () {
            let selection = frame.state().get('selection').toJSON(),
                img_src
            ;
            if (selection.length) {
                if (selection[0].sizes['thumbnail'] !== undefined) {
                    img_src = selection[0].sizes['thumbnail'].url;
                } else {
                    img_src = selection[0].url;
                }
                $companyLogo.find('[name=bookly_co_logo_attachment_id]').val(selection[0].id);
                $companyLogo.find('.bookly-js-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'cover'});
                $companyLogo.find('.bookly-js-delete').show();
                $(this).hide();
            }
        });

        frame.open();
    });
    $('#bookly-company-reset').on('click', function () {
        var $div = $('#bookly-js-company-logo .bookly-js-image'),
            $input = $('[name=bookly_co_logo_attachment_id]');
        $div.attr('style', $div.data('style'));
        $input.val($input.data('default'));
    });

    // Cart tab.
    let $sortableCart = $('#bookly_cart_show_columns');
    if ($sortableCart.length) {
        Sortable.create($sortableCart[0], {
            handle: '.bookly-js-draghandle'
        });
    }
    // Payments tab.
    Sortable.create($('#bookly-payment-systems')[0], {
        handle: '.bookly-js-draghandle',
        onChange: function () {
            let order = [];
            $('#bookly_settings_payments .card[data-slug]').each(function () {
                order.push($(this).data('slug'));
            });
            $('#bookly_settings_payments [name="bookly_pmt_order"]').val(order.join(','));
        },
    });
    $currency.on('change', function () {
        $formats.find('option').each(function () {
            var decimals = this.value.match(/{price\|(\d)}/)[1],
                price    = BooklyL10n.sample_price
            ;
            if (decimals < 3) {
                price = price.slice(0, -(decimals == 0 ? 4 : 3 - decimals));
            }
            var html = this.value
                .replace('{sign}', '')
                .replace('{symbol}', $currency.find('option:selected').data('symbol'))
                .replace(/{price\|\d}/, price)
            ;
            html += ' (' + this.value
                .replace('{sign}', '-')
                .replace('{symbol}', $currency.find('option:selected').data('symbol'))
                .replace(/{price\|\d}/, price) + ')'
            ;
            this.innerHTML = html;
        });
    }).trigger('change');

    $('#bookly_paypal_enabled').change(function () {
        $('.bookly-paypal-ec').toggle(this.value == 'ec');
        $('.bookly-paypal-ps').toggle(this.value == 'ps');
        $('.bookly-paypal-checkout').toggle(this.value == 'checkout');
        $('.bookly-paypal').toggle(this.value != '0');
        $('#bookly_paypal_timeout').closest('.form-group').toggle(this.value != 'ec');
    }).change();

    $('#bookly_authorize_net_enabled').change(function () {
        $('.bookly-authorize-net').toggle(this.value != '0');
    }).change();

    $('#bookly_stripe_enabled').change(function () {
        $('.bookly-stripe').toggle(this.value == 1);
    }).change();

    $('#bookly_2checkout_enabled').change(function () {
        $('.bookly-2checkout').toggle(this.value != '0');
    }).change();

    $('#bookly_payu_biz_enabled').change(function () {
        $('.bookly-payu_biz').toggle(this.value != '0');
    }).change();

    $('#bookly_payu_latam_enabled').change(function () {
        $('.bookly-payu_latam').toggle(this.value != '0');
    }).change();

    $('#bookly_payson_enabled').change(function () {
        $('.bookly-payson').toggle(this.value != '0');
    }).change();

    $('#bookly_mollie_enabled').change(function () {
        $('.bookly-mollie').toggle(this.value != '0');
    }).change();

    $('#bookly_payu_biz_sandbox').change(function () {
        var live = this.value != 1;
        $('.bookly-payu_biz > .form-group:eq(1)').toggle(live);
        $('.bookly-payu_biz > .form-group:eq(2)').toggle(live);
    }).change();

    $('#bookly_payu_latam_sandbox').change(function () {
        var live = this.value != 1;
        $('.bookly-payu_latam > .form-group:eq(1)').toggle(live);
        $('.bookly-payu_latam > .form-group:eq(2)').toggle(live);
        $('.bookly-payu_latam > .form-group:eq(3)').toggle(live);
    }).change();

    $('#bookly-payments-reset').on('click', function (event) {
        setTimeout(function () {
            $('#bookly_pmt_currency,#bookly_paypal_enabled,#bookly_authorize_net_enabled,#bookly_stripe_enabled,#bookly_2checkout_enabled,#bookly_payu_biz_enabled,#bookly_payu_latam_enabled,#bookly_payson_enabled,#bookly_mollie_enabled,#bookly_payu_biz_sandbox,#bookly_payu_latam_sandbox').change();
        }, 0);
    });

    // URL tab.
    if ($finalStepUrl.val()) { $finalStepUrlMode.val(1); }
    $finalStepUrlMode.change(function () {
        if (this.value == 0) {
            $finalStepUrl.hide().val('');
        } else {
            $finalStepUrl.show();
        }
    });

    // Holidays Tab.
    var d = new Date();
    $('.bookly-js-annual-calendar').jCal({
        day: new Date(d.getFullYear(), 0, 1),
        days:       1,
        showMonths: 12,
        scrollSpeed: 350,
        events:     BooklyL10n.holidays,
        action:     'bookly_settings_holiday',
        csrf_token: BooklyL10n.csrf_token,
        dayOffset:  parseInt(BooklyL10n.firstDay),
        loadingImg: BooklyL10n.loading_img,
        dow:        BooklyL10n.days,
        ml:         BooklyL10n.months,
        we_are_not_working: BooklyL10n.we_are_not_working,
        repeat:     BooklyL10n.repeat,
        close:      BooklyL10n.close
    });
    $('.bookly-js-jCalBtn').on('click', function (e) {
        e.preventDefault();
        var trigger = $(this).data('trigger');
        $('.bookly-js-annual-calendar').find($(trigger)).trigger('click');
    });

    // Business Hours tab.
    $('.bookly-js-parent-range-start', $businessHours)
        .on('change', function () {
            var $parentRangeStart = $(this),
                $rangeRow = $parentRangeStart.parents('.bookly-js-range-row');
            if ($parentRangeStart.val() == '') {
                $('.bookly-js-invisible-on-off', $rangeRow).addClass('invisible');
            } else {
                $('.bookly-js-invisible-on-off', $rangeRow).removeClass('invisible');;
                rangeTools.hideInaccessibleEndTime($parentRangeStart, $('.bookly-js-parent-range-end', $rangeRow));
            }
        }).trigger('change');
    // Reset.
    $('#bookly-hours-reset', $businessHours).on('click', function () {
        $('.bookly-js-parent-range-start', $businessHours).each(function () {
            $(this).val($(this).data('default_value')).trigger('change');
        });
    });

    // Change link to Help page according to activated tab.
    let help_link = $helpBtn.attr('href');
    $('#bookly-sidebar a[data-toggle="bookly-pill"]').on('shown.bs.tab', function(e) {
        $helpBtn.attr('href', help_link + e.target.getAttribute('href').substring(1).replace(/_/g, '-'));
    });

    // Activate tab.
    $('a[href="#bookly_settings_' + BooklyL10n.current_tab + '"]').booklyTab('show');
});