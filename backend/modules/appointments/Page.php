<?php
namespace Bookly\Backend\Modules\Appointments;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Appointments
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array( 'bootstrap/css/bootstrap.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'         => array( 'jquery' ),
                'js/select2.min.js'             => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/appointments.js' => array( 'bookly-datatables.min.js' ), ),
        ) );

        $datatables = Lib\Utils\Tables::getSettings( 'appointments' );

        wp_localize_script( 'bookly-appointments.js', 'BooklyL10n', array(
            'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
            'datePicker'      => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'       => Lib\Utils\DateTime::dateRangeOptions( array( 'anyTime' => __( 'Any time', 'bookly' ), 'createdAtAnyTime' => __( 'Created at any time', 'bookly' ), ) ),
            'are_you_sure'    => __( 'Are you sure?', 'bookly' ),
            'zeroRecords'     => __( 'No appointments for selected period.', 'bookly' ),
            'processing'      => __( 'Processing...', 'bookly' ),
            'edit'            => __( 'Edit', 'bookly' ),
            'no_result_found' => __( 'No result found', 'bookly' ),
            'searching'       => __( 'Searching', 'bookly' ),
            'attachments'     => __( 'Attachments', 'bookly' ),
            'tasks'           => array(
                'enabled' => Lib\Config::tasksActive(),
                'title'   => Proxy\Tasks::getFilterText(),
            ),
            'datatables'      => $datatables,
        ) );

        // Filters data
        $staff_members = Lib\Entities\Staff::query( 's' )->select( 's.id, s.full_name' )->whereNot( 'visibility', 'archive' )->fetchArray();
        $customers     = Lib\Entities\Customer::query()->count() < Lib\Entities\Customer::REMOTE_LIMIT
            ? array_map( function ( $row ) {
                unset( $row['id'] );

                return $row;
            }, Lib\Entities\Customer::query( 'c' )->select( 'c.id, c.full_name, c.email, c.phone' )->indexBy( 'id' )->fetchArray() )
            : false;
        $services      = Lib\Entities\Service::query( 's' )->select( 's.id, s.title' )->where( 'type', Lib\Entities\Service::TYPE_SIMPLE )->fetchArray();

        self::renderTemplate( 'index', compact( 'staff_members', 'customers', 'services', 'datatables' ) );
    }
}