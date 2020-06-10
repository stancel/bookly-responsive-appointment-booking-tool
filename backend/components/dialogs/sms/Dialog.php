<?php
namespace Bookly\Backend\Components\Dialogs\Sms;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Controls\Buttons;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Sms
 */
class Dialog extends BooklyLib\Base\Component
{
    /**
     * Render payment details dialog.
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
            'backend'  => array(
                'js/select2.min.js' => array( 'jquery' ),
                'js/dropdown.js'    => array( 'jquery' ),
            ),
            'module'   => array( 'js/notification-dialog.js' => array( 'bookly-dropdown.js', 'bookly-select2.min.js' ), ),
        ) );

        wp_localize_script( 'bookly-notification-dialog.js', 'BooklyNotificationDialogL10n', array(
            'csrfToken'       => BooklyLib\Utils\Common::getCsrfToken(),
            'recurringActive' => (int) BooklyLib\Config::recurringAppointmentsActive(),
            'defaultNotification' => self::getDefaultNotification(),
            'sms'                 => true,
            'title' => array(
                'container' => __( 'Sms', 'bookly' ),
                'new'       => __( 'New sms notification', 'bookly' ),
                'edit'      => __( 'Edit sms notification', 'bookly' ),
                'create'    => __( 'Create notification', 'bookly' ),
                'save'      => __( 'Save notification', 'bookly' ),
            ),
        ) );

        self::renderTemplate( 'dialog' );
    }

    public static function renderNewNotificationButton()
    {
        print '<div class="col-12 col-sm-auto">';
        Buttons::renderAdd( 'bookly-js-new-notification', 'btn-success', __( 'New notification', 'bookly' ) );
        print '</div>';
    }

    /**
     * @return array
     */
    protected static function getDefaultNotification()
    {
        return array(
            'type'           => BooklyLib\Entities\Notification::TYPE_NEW_BOOKING,
            'active'         => 1,
            'attach_ics'     => 0,
            'attach_invoice' => 0,
            'message'        => '',
            'name'           => '',
            'subject'        => '',
            'to_admin'       => 0,
            'to_customer'    => 1,
            'to_staff'       => 0,
            'settings'       => BooklyLib\DataHolders\Notification\Settings::getDefault(),
        );
    }
}