<?php
namespace Bookly\Backend\Modules\Payments;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Payments
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
            'module'   => array( 'js/payments.js' => array( 'bookly-datatables.min.js', 'bookly-ng-payment_details.js', 'bookly-daterangepicker.js' ) ),
        ) );

        $datatables = Lib\Utils\Tables::getSettings( 'payments' );

        wp_localize_script( 'bookly-payments.js', 'BooklyL10n', array(
            'csrfToken'      => Lib\Utils\Common::getCsrfToken(),
            'datePicker'     => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'      => Lib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'bookly' ), ) ),
            'zeroRecords'    => __( 'No payments for selected period and criteria.', 'bookly' ),
            'processing'     => __( 'Processing...', 'bookly' ),
            'details'        => __( 'Details', 'bookly' ),
            'areYouSure'     => __( 'Are you sure?', 'bookly' ),
            'noResultFound'  => __( 'No result found', 'bookly' ),
            'searching'      => __( 'Searching', 'bookly' ),
            'datatables'     => $datatables,
            'invoice'        => array(
                'enabled' => (int) Lib\Config::invoicesActive(),
                'button'  => __( 'Invoice', 'bookly' ),
            ),
        ) );

        $types = array(
            Lib\Entities\Payment::TYPE_LOCAL,
            Lib\Entities\Payment::TYPE_2CHECKOUT,
            Lib\Entities\Payment::TYPE_PAYPAL,
            Lib\Entities\Payment::TYPE_AUTHORIZENET,
            Lib\Entities\Payment::TYPE_STRIPE,
            Lib\Entities\Payment::TYPE_PAYUBIZ,
            Lib\Entities\Payment::TYPE_PAYULATAM,
            Lib\Entities\Payment::TYPE_PAYSON,
            Lib\Entities\Payment::TYPE_MOLLIE,
            Lib\Entities\Payment::TYPE_FREE,
            Lib\Entities\Payment::TYPE_WOOCOMMERCE,
        );

        $providers = Lib\Entities\Staff::query()->select( 'id, full_name' )->sortBy( 'full_name' )->whereNot( 'visibility', 'archive' )->fetchArray();
        $services  = Lib\Entities\Service::query()->select( 'id, title' )->sortBy( 'title' )->fetchArray();
        $customers     = Lib\Entities\Customer::query()->count() < Lib\Entities\Customer::REMOTE_LIMIT
            ? array_map( function ( $row ) {
                unset( $row['id'] );

                return $row;
            }, Lib\Entities\Customer::query( 'c' )->select( 'c.id, c.full_name, c.email, c.phone' )->indexBy( 'id' )->fetchArray() )
            : false;

        self::renderTemplate( 'index', compact( 'types', 'providers', 'services', 'customers', 'datatables' ) );
    }
}