/**
 * jQuery booklyEditable.
 */
(function ($) {
    let methods = {
        init     : function (options) {
            let opts = $.extend({}, $.fn.booklyEditable.defaults, options);

            return this.each(function () {
                if ($(this).data('booklyEditable')) {
                    return;
                }
                let obj = {
                    $container: $(this),
                    opts      : opts,
                    values    : {},
                    option    : '',
                    type      : '',
                    content   : function () {
                        let $content = $('<div class="mt-2">');
                        switch (obj.type) {
                            case 'textarea':
                                $.each(obj.values, function (index, value) {
                                    $content.append('<div class="form-group mb-2"><textarea class="form-control bookly-js-editable-control" name="' + index + '" rows="5">' + value + '</textarea></div>');
                                });
                                break;
                            default:
                                $.each(obj.values, function (index, value) {
                                    $content.append('<div class="form-group mb-2"><input type="text" class="form-control bookly-js-editable-control" name="' + index + '" value="' + value + '"/></div>');
                                });
                                break;
                        }
                        $content.append('<hr/>');
                        $content.append('<div class="text-right"><div class="btn-group btn-group-sm" role="group"><button type="button" class="btn btn-success bookly-js-editable-save"><i class="fas fa-fw fa-check"></i></button><button type="button" class="btn btn-default" data-dismiss="bookly-popover"><i class="fas fa-fw fa-times"></i></button></div></div>');
                        let codes = obj.$container.data('codes');
                        if (codes !== undefined) {
                            $content.append(codes);
                        }
                        // Click on "Close" button.
                        $content.find('button[data-dismiss="bookly-popover"]').click(function () {
                            close();
                        });
                        // Click on "Save" button.
                        $content.find('button.bookly-js-editable-save').click(function () {
                            save();
                        });
                        // Process keypress.
                        $content.find('.bookly-js-editable-control').on('keyup', function (e) {
                            if (e.keyCode === 27) {
                                close();
                            }
                        });

                        function close() {
                            obj.$container.booklyPopover('hide');
                        }

                        function save() {
                            $content.find('.bookly-js-editable-control').each(function () {
                                obj.values[this.name] = this.value;
                            });
                            // Update values for all editable fields with same data-option
                            $('[data-option="' + obj.option + '"]').each(function () {
                                $(this).booklyEditable('setValue', obj.values);
                            });
                            obj.$container.booklyPopover('hide');
                        }

                        return $content;
                    },
                    title     : function () {
                        let title = obj.$container.data('title');
                        return title === undefined ? '' : title;
                    }
                }
                $.each(methods.parseJson(obj.$container.data('values')), function (index, value) {
                    obj.values[index] = value;
                });
                obj.type = obj.$container.data('fieldtype') || 'input';
                obj.option = obj.$container.data('option');
                obj.$container.booklyPopover({
                    html             : true,
                    placement        : obj.$container.data('placement') !== undefined ? obj.$container.data('placement') : opts.placement,
                    fallbackPlacement: obj.$container.data('fallbackPlacement') !== undefined ? obj.$container.data('fallbackPlacement') : opts.fallbackPlacement,
                    container        : opts.container,
                    template         : '<div class="bookly-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
                    trigger          : 'manual',
                    title            : obj.title,
                    content          : obj.content
                });

                // Click on editable field.
                obj.$container.on('click', function (e) {
                    e.preventDefault();
                    if (!obj.$container.attr('aria-describedby')) {
                        $('.bookly-popover').each(function () {
                            $('[aria-describedby="' + $(this).attr('id') + '"]').booklyPopover('hide');
                        });

                        obj.$container.booklyPopover('show');
                        obj.$container.off('shown.bs.popover').on('shown.bs.popover', function () {
                            if (obj.$container.attr('aria-describedby') !== undefined) {
                                $(obj.$container.data('bs.popover').tip).find('.bookly-js-editable-control:first').focus();
                            }
                        });
                    } else {
                        obj.$container.booklyPopover('hide');
                    }
                });

                // Set text for empty field.
                if (obj.$container.text() === '') {
                    obj.$container.text(opts.empty);
                }

                obj.$container.data('booklyEditable', obj);
            });
        },
        setValue : function (values) {
            var obj = this.data('booklyEditable');
            if (!obj) {
                return;
            }

            obj.values = values;

            // Update field text.
            obj.$container.text(obj.values[obj.option] === '' ? obj.opts.empty : obj.values[obj.option]);
        },
        getValue : function () {
            var obj = this.data('booklyEditable');
            if (!obj) {
                return;
            }

            return obj.values;
        },
        parseJson: function (s) {
            if (typeof s === 'string' && s.length && s.match(/^[\{\[].*[\}\]]$/)) {
                s = (new Function('return ' + s))();
            }
            return s;
        },
    };

    // Process click outside popover to hide it.
    $(document).on('click', function (e) {
        if (!$(e.target).hasClass('bookly-js-editable')) {
            let $activators = $('.bookly-js-editable[aria-describedby]');
            if ($activators.length > 0 && $(e.target).parents('.bookly-popover').length === 0) {
                $activators.booklyPopover('hide');
            }
        }
    });

    $.fn.booklyEditable = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('No method ' + method + ' for jQuery.booklyEditable');
        }
    };

    $.fn.booklyEditable.defaults = {
        placement        : 'auto',
        fallbackPlacement: ['bottom'],
        container        : '#bookly-appearance',
        empty            : 'Empty',
    };
})(jQuery);