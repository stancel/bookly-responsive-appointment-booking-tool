<?php
namespace Bookly\Backend\Components\Dialogs\Appointment\Edit;

use Bookly\Lib;

/**
 * Class Edit
 * @package Bookly\Backend\Components\Dialogs\Appointment\Edit
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create/edit appointment dialog.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'backend'  => array( 'css/fontawesome-all.min.css' ),
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'js/alert.js'                   => array( 'jquery' ),
                'js/angular.min.js'             => array( 'jquery' ),
                'js/moment.min.js'              => array( 'jquery' ),
                'js/daterangepicker.js'         => array( 'bookly-moment.min.js' ),
                'js/angular-daterangepicker.js' => array( 'bookly-angular.min.js', 'bookly-daterangepicker.js' ),
                'js/select2.min.js'             => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module'   => array(
                'js/ng-appointment.js' => array( 'bookly-angular-daterangepicker.js', 'bookly-alert.js' ),
            ),
        ) );

        wp_localize_script( 'bookly-ng-appointment.js', 'BooklyL10nAppDialog', array(
            'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
            'datePicker'      => Lib\Utils\DateTime::datePickerOptions(),
            'cf_per_service'  => (int) Lib\Config::customFieldsPerService(),
            'no_result_found' => __( 'No result found', 'bookly' ),
            'searching'       => __( 'Searching', 'bookly' ),
            'staff_any'       => get_option( 'bookly_l10n_option_employee' ),
            'title'           => array(
                'edit_appointment'   => __( 'Edit appointment', 'bookly' ),
                'new_appointment'    => __( 'New appointment', 'bookly' ),
                'send_notifications' => __( 'Send notifications', 'bookly' ),
            ),
        ) );

        Proxy\Shared::enqueueAssets();

        self::renderTemplate( 'edit' );
    }
}