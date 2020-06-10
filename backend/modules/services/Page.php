<?php
namespace Bookly\Backend\Modules\Services;

use Bookly\Lib;
use Bookly\Backend\Modules\Services\Proxy;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Services
 */
class Page extends Lib\Base\Ajax
{
    /**
     * Render page.
     */
    public static function render()
    {
        wp_enqueue_media();
        self::enqueueStyles( array(
            'wp'       => array( 'wp-color-picker' ),
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap.min.css' ),
        ) );

        self::enqueueScripts( array(
            'wp'       => array( 'wp-color-picker' ),
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/dropdown.js'                => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
                'js/range-tools.js'             => array( 'jquery' ),
                'js/select2.min.js'             => array( 'jquery' ),
                'js/sortable.min.js',
            ),
            'module'   => array( 'js/services-list.js' => array( 'bookly-sortable.min.js', 'bookly-dropdown.js' ) ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'bookly-spin.min.js', 'jquery' ),
            ),
        ) );

        $staff = array();
        foreach ( self::getStaffDropDownData() as $category ) {
            foreach ( $category['items'] as $employee ) {
                $staff[ $employee['id'] ] = $employee['full_name'];
            }
        }

        $services = Lib\Entities\Service::query( 's' )
            ->whereIn( 's.type', array_keys( Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => Lib\Entities\Service::TYPE_SIMPLE ) ) ) )
            ->sortBy( 'position' )
            ->fetchArray();
        $categories = Lib\Entities\Category::query()->sortBy( 'position' )->fetchArray();

        $datatables = Lib\Utils\Tables::getSettings( 'services' );

        wp_localize_script( 'bookly-services-list.js', 'BooklyL10n', array(
            'csrfToken'        => Lib\Utils\Common::getCsrfToken(),
            'are_you_sure'     => esc_attr__( 'Are you sure?', 'bookly' ),
            'edit'             => esc_attr__( 'Edit', 'bookly' ),
            'reorder'          => esc_attr__( 'Reorder', 'bookly' ),
            'staff'            => $staff,
            'categories'       => $categories,
            'uncategorized'    => esc_attr__( 'Uncategorized', 'bookly' ),
            'services'         => $services,
            'capacity_error'   => esc_attr__( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            'recurrence_error' => esc_attr__( 'You must select at least one repeat option for recurring services.', 'bookly' ),
            'noResultFound'    => esc_attr__( 'No result found', 'bookly' ),
            'show_type'        => count( Proxy\Shared::prepareServiceTypes( array() ) ) > 0,
            'datatables'       => $datatables,
        ) );

        // Allow add-ons to enqueue their assets.
        Proxy\Shared::enqueueAssetsForServices();

        foreach ( $services as &$service ) {
            $service['colors'] = Proxy\Shared::prepareServiceColors( array_fill( 0, 3, $service['color'] ), $service['id'], $service['type'] );
            $service['sub_services'] = Lib\Entities\SubService::query()
                ->where( 'service_id', $service['id'] )
                ->sortBy( 'position' )
                ->fetchArray();
            $service['sub_services_count'] = array_sum( array_map( function ( $sub_service ) {
                return (int) ( $sub_service['type'] == Lib\Entities\SubService::TYPE_SERVICE );
            }, $service['sub_services'] ) );
        }
        $data['services'] = $services;
        $data['service_types'] = Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => __( 'Simple', 'bookly' ) ) );
        $data['categories'] = $categories;
        $data['datatables'] = $datatables;

        self::renderTemplate( 'index', $data );
    }

    /**
     * Get data for staff drop-down.
     *
     * @return array
     */
    public static function getStaffDropDownData()
    {
        if ( Lib\Config::proActive() ) {
            return Lib\Proxy\Pro::getStaffDataForDropDown();
        } else {
            $items = Lib\Entities\Staff::query()
                ->select( 'id, full_name' )
                ->whereNot( 'visibility', 'archive' )
                ->sortBy( 'position' )
                ->fetchArray();

            return array(
                0 => array(
                    'name'  => '',
                    'items' => $items,
                ),
            );
        }
    }
}