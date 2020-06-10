<?php
namespace Bookly\Backend\Modules\Notifications;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Notifications
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js' => array( 'jquery' ),
                'js/alert.js'    => array( 'bookly-datatables.min.js' ),
                'js/dropdown.js' => array( 'jquery' ),
            ),
            'bookly'   => array( 'backend/modules/sms/resources/js/notifications-list.js' => array( 'jquery' ), ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );

        $datatables = Lib\Utils\Tables::getSettings( 'email_notifications' );

        wp_localize_script( 'bookly-alert.js', 'BooklyL10n', array(
            'csrfToken'        => Lib\Utils\Common::getCsrfToken(),
            'sentSuccessfully' => __( 'Sent successfully.', 'bookly' ),
            'settingsSaved'    => __( 'Settings saved.', 'bookly' ),
            'areYouSure'       => __( 'Are you sure?', 'bookly' ),
            'zeroRecords'      => __( 'No records.', 'bookly' ),
            'processing'       => __( 'Processing...', 'bookly' ),
            'state'            => array( __( 'Disabled', 'bookly' ), __( 'Enabled', 'bookly' ) ),
            'action'           => array( __( 'enable', 'bookly' ), __( 'disable', 'bookly' ) ),
            'edit'             => __( 'Edit', 'bookly' ),
            'gateway'          => 'email',
            'datatables'      => $datatables,
        ) );

        self::renderTemplate( 'index', compact( 'datatables' ) );
    }
}