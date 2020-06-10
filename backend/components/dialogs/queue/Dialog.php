<?php
namespace Bookly\Backend\Components\Dialogs\Queue;

use Bookly\Lib as BooklyLib;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Queue
 */
class Dialog extends BooklyLib\Base\Component
{
    /**
     * Render notifications queue dialog.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array( 'css/fontawesome-all.min.css', ),
        ) );

        self::enqueueScripts( array(
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery', ),
                'js/ladda.min.js' => array( 'jquery', ),
            ),
            'module'   => array( 'js/queue-dialog.js' => array( 'jquery' ), ),
        ) );

        wp_localize_script( 'bookly-queue-dialog.js', 'BooklyNotificationQueueDialogL10n', array(
            'csrfToken'       => BooklyLib\Utils\Common::getCsrfToken(),
        ) );

        self::renderTemplate( 'dialog' );
    }
}