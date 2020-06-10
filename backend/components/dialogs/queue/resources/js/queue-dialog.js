jQuery(function ($) {
    $(document.body).on('bookly.queue_dialog', {},
        function (event, queue, callback) {
            var $dialog   = $('#bookly-queue-modal'),
                $queue    = $('#bookly-queue', $dialog),
                $template = $('#bookly-notification-template')
            ;

            $queue.html('');
            queue.forEach(function (notification, index) {
                $queue.append(
                    $template.clone().show().html()
                        .replace(/{{icon}}/g, notification.gateway == 'sms' ? 'fas fa-sms' : 'far fa-envelope')
                        .replace(/{{recipient}}/g, notification.data.name)
                        .replace(/{{address}}/g, notification.address)
                        .replace(/{{description}}/g, notification.name)
                        .replace(/{{index}}/g, index)
                );
            });
            $dialog.off().on('click', '.bookly-js-send', function (e) {
                e.preventDefault();
                var ladda      = Ladda.create(this),
                    send_queue = [];
                ladda.start();

                $queue.find('.bookly-js-notification-queue input[type="checkbox"]:checked').each(function () {
                    send_queue.push(queue[$(this).data('index')]);
                });
                $.post(
                    ajaxurl,
                    {
                        action    : 'bookly_send_queue',
                        csrf_token: BooklyNotificationQueueDialogL10n.csrfToken,
                        queue     : send_queue,
                        queue_full: queue
                    },
                    function (response) {
                        ladda.stop();
                        if (response.success) {
                            // Close the dialog.
                            $dialog.booklyModal('hide');
                        }
                        if (callback) {
                            // Call callback.
                            callback();
                        }
                    },
                    'json'
                );
            }).on('click', '.bookly-js-cancel', function (e) {
                e.preventDefault();
                $.post(
                    ajaxurl,
                    {
                        action    : 'bookly_clear_attachments',
                        csrf_token: BooklyNotificationQueueDialogL10n.csrfToken,
                        queue     : queue
                    },
                    'json'
                );
                if (callback) {
                    // Call callback.
                    callback();
                }
            }).booklyModal('show');
        }
    );
});