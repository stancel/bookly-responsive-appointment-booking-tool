(function ($) {
    var Schedule = function ($container, options) {
        let obj = this;
            jQuery.extend(obj.options, options);

        // Loads schedule list
        if (!$container.children().length) {
            $container.html('<div class="bookly-loading"></div>');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: obj.options.get_staff_schedule,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    // fill in the container
                    $container.html('');
                    $container.append(response.data.html);
                    $container.removeData('init');
                    obj.options.onLoad();
                    initContainer($container, obj);
                }
            });
        } else {
            initContainer($container, obj);
        }

        function initContainer($panel, obj) {
            if ($panel.data('init') != true) {
                initBooklyPopover($container);

                $container.off()
                    // Save Schedule
                    .on('click', '#bookly-schedule-save', function (e) {
                        e.preventDefault();
                        let ladda = Ladda.create(this),
                            data = {};
                        ladda.start();
                        $('select.bookly-js-parent-range-start, select.bookly-js-parent-range-end, input:hidden', $container).each(function () {
                            data[this.name] = this.value;
                        });
                        data['location_id'] = $('#staff_location_id', $container).val();
                        data['custom_location_settings'] = $('#custom_location_settings', $container).val();
                        data['staff_id'] = options.get_staff_schedule.staff_id;
                        data['action'] = 'bookly_staff_schedule_update';
                        $.post(ajaxurl, $.param(data), function () {
                            ladda.stop();
                            obj.options.saving({success: [obj.options.l10n.saved]});
                        });
                    })
                    // Resets initial schedule values
                    .on('click', '#bookly-schedule-reset', function (e) {
                        e.preventDefault();
                        var ladda = Ladda.create(this);
                        ladda.start();

                        $('.bookly-js-parent-range-start', $container).each(function () {
                            $(this).val($(this).data('default_value'));
                            $(this).trigger('change');
                        });

                        $('.bookly-js-parent-range-end', $container).each(function () {
                            $(this).val($(this).data('default_value'));
                        });

                        // reset breaks
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {action: 'bookly_staff_cabinet_reset_breaks', breaks: $(this).data('default-breaks'), staff_cabinet: $(this).data('staff-cabinet') || 0, csrf_token: obj.options.l10n.csrfToken},
                            dataType: 'json',
                            success: function (response) {
                                $('[data-index]', $container).each(function () {
                                    let $row  = $(this),
                                        index = $row.data('index'),
                                        $list = $('.bookly-js-breaks-list', $row);
                                    $list.html('');
                                    if (response.data.breaks.hasOwnProperty(index)) {
                                        response.data.breaks[index].forEach(function(elem) {
                                            var $html = $.parseHTML(elem);
                                            initBooklyPopover($html);
                                            $list.append($html)
                                        });
                                    }
                                });
                            },
                            complete: function () {
                                ladda.stop();
                            }
                        });
                    })
                    .on('click', '.popover-body .bookly-js-save-break', function (e) {
                        e.preventDefault();
                        // Listener for saving break.
                        let $button = $(this),
                            $body = $button.closest('.popover-body'),
                            ladda = rangeTools.ladda(this),
                            data = $.extend({
                                action    : 'bookly_staff_schedule_handle_break',
                                csrf_token: obj.options.l10n.csrfToken,
                                start_time: $('.bookly-js-popover-range-start', $body).val(),
                                end_time  : $('.bookly-js-popover-range-end', $body).val(),
                            }, $button.data('submit'));
                        let $parentRange = $('.bookly-js-range-row[data-key=' + data.ss_id + ']');

                        data.working_end   = $('.bookly-js-parent-range-end > option:selected', $parentRange).val();
                        data.working_start = $('.bookly-js-parent-range-start > option:selected', $parentRange).val();
                        $.ajax({
                            method: 'POST',
                            url: ajaxurl,
                            data: data,
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    if (data.hasOwnProperty('id')) {
                                        // Change text on button with new range value.
                                        var $interval_button = $('button.bookly-js-break-interval', $('[data-entity-id=' + data.id + ']'));
                                        $interval_button.html(response.data.interval);
                                    } else {
                                        var $html = $.parseHTML(response.data.html);
                                        initBooklyPopover($html);
                                        $('.bookly-js-range-row[data-key=' + data.ss_id + '] .bookly-js-breaks-list', $container)
                                            .append($html);
                                    }
                                    $button.parents('.bookly-popover').booklyPopover('hide');
                                } else {
                                    if (response.data && response.data.message) {
                                        obj.options.booklyAlert({error: [response.data.message]});
                                    }
                                }
                            }
                        }).always(function () {
                            ladda.stop()
                        });
                    })
                    .on('click', '.bookly-js-delete-break', function () {
                        deleteBreak.call(this);
                    })

                    .on('change', '.bookly-js-popover-range-start', function () {
                        let $start = $(this),
                            $body = $start.closest('.popover-body'),
                            $end = $('.bookly-js-popover-range-end', $body),
                            ss_id = $('.bookly-js-save-break', $body).data('submit').ss_id,
                            $parent = $('.bookly-js-range-row[data-key=' + ss_id + ']');
                        rangeTools.hideInaccessibleBreaks($start, $end, $parent);
                    })

                    .on('change', '.bookly-js-parent-range-start', function () {
                        var $parentRangeStart = $(this),
                            $rangeRow = $parentRangeStart.parents('.bookly-js-range-row');
                        if ($parentRangeStart.val() == '') {
                            $rangeRow
                                .find('.bookly-js-hide-on-off').hide().end()
                                .find('.bookly-js-invisible-on-off').addClass('invisible');
                        } else {
                            $rangeRow
                                .find('.bookly-js-hide-on-off').show().end()
                                .find('.bookly-js-invisible-on-off').removeClass('invisible');
                            rangeTools.hideInaccessibleEndTime($parentRangeStart, $('.bookly-js-parent-range-end', $rangeRow));
                        }
                    })
                    // Change location
                    .on('change', '#staff_location_id', function () {
                        var get_staff_schedule = {
                                action: options.get_staff_schedule.action,
                                staff_id: options.get_staff_schedule.staff_id,
                                csrf_token: options.get_staff_schedule.csrf_token,
                            },
                            staff_location_id = $('#staff_location_id', $container).val();
                        if (staff_location_id != '') {
                            get_staff_schedule['location_id'] = staff_location_id;
                        }
                        $container.html('');
                        new BooklyStaffSchedule($container, {
                            get_staff_schedule: get_staff_schedule,
                            l10n: options.l10n
                        });
                    })
                    // Change default/custom settings for location
                    .on('change', '#custom_location_settings', function () {
                        if ($(this).val() == 1) {
                            $('.bookly-js-range-row', $container).show();
                        } else {
                            $('.bookly-js-range-row', $container).hide();
                        }
                    })
                ;

                $('#custom_location_settings', $container).trigger('change');
                $('.bookly-js-parent-range-start', $container).trigger('change');
                $container.data('init', true);
            }
        }

        function initBooklyPopover($panel) {
            $('.bookly-js-toggle-popover', $panel)
                .booklyPopover({
                    html     : true,
                    placement: 'bottom',
                    container: $container,
                    template : '<div class="bookly-popover mw-100" role="tooltip"><div class="arrow"></div><div class="popover-body"></div></div>',
                    trigger  : 'manual',
                    content  : function () {
                        let $button       = $(this),
                            $popover      = $('.bookly-js-edit-break-body > div', $container).clone(),
                            $popoverStart = $('.bookly-js-popover-range-start', $popover),
                            $popoverEnd   = $('.bookly-js-popover-range-end', $popover),
                            $saveButton   = $('.bookly-js-save-break', $popover),
                            force_keep_values = false;
                        if ($button.hasClass('bookly-js-break-interval')) {
                            let interval = $button.html().trim().split(' - ');
                            rangeTools.setVal($popoverStart, interval[0]);
                            rangeTools.setVal($popoverEnd, interval[1]);
                            force_keep_values = true;
                            $saveButton.data('submit', {
                                id: $button.closest('[data-entity-id]').data('entity-id'),
                                ss_id: $button.closest('.bookly-js-range-row').data('key'),
                            });
                        } else {
                            rangeTools.setPopoverRangeDefault($popoverStart, $popoverEnd, $button.closest('.bookly-js-range-row'));
                            $saveButton.data('submit', {
                                ss_id: $button.closest('.bookly-js-range-row').data('key'),
                            });
                        }
                        rangeTools.hideInaccessibleBreaks($popoverStart, $popoverEnd, $button.closest('.bookly-js-range-row'), force_keep_values);
                        $('.bookly-js-close', $popover).on('click', function () {
                            $button.booklyPopover('hide');
                        });

                        return $popover;
                    }
                })
                .on('click', function () {
                    $('.bookly-js-toggle-popover').booklyPopover('hide');
                    $(this).booklyPopover('toggle');
                });
        }

        function deleteBreak() {
            var $btn_group = $(this).closest('[data-entity-id]');
            if (confirm(obj.options.l10n.areYouSure)) {
                var ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {action: 'bookly_delete_staff_schedule_break', id: $btn_group.data('entity-id'), csrf_token: obj.options.l10n.csrfToken},
                    success: function (response) {
                        if (response.success) {
                            $btn_group.remove();
                        }
                    },
                    complete: function () {
                        ladda.stop();
                    }
                });
            }
        }
    };

    Schedule.prototype.options = {
        get_staff_schedule: {
            action: 'bookly_get_staff_schedule',
            staff_id: -1,
            csrf_token: ''
        },
        saving: function (alerts) {
            $(document.body).trigger('staff.saving', [alerts]);
        },
        booklyAlert: function (alerts) {
            booklyAlert(alerts);
        },
        onLoad: function () {},
        l10n: {}
    };

    window.BooklyStaffSchedule = Schedule;
})(jQuery);