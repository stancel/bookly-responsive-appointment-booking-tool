<?php
namespace Bookly\Backend\Modules\Staff;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Staff
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'backend'  => array( 'bootstrap/css/bootstrap.min.css', 'css/fontawesome-all.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/select2.min.js'             => array( 'jquery' ),
            ),
            'module'   => array(
                'js/staff-list.js' => array( 'jquery' ),
            ),
        ) );

        // Allow add-ons to enqueue their assets.
        Proxy\Shared::enqueueStaffProfileStyles();
        Proxy\Shared::enqueueStaffProfileScripts();
        Proxy\Shared::renderStaffPage( self::parameters() );

        $categories = (array) Proxy\Pro::getCategoriesList();

        $datatables = Lib\Utils\Tables::getSettings( 'staff_members' );

        wp_localize_script( 'bookly-staff-list.js', 'BooklyL10n', array(
            'csrfToken'      => Lib\Utils\Common::getCsrfToken(),
            'proRequired'    => (int) ! Lib\Config::proActive(),
            'areYouSure'     => esc_attr__( 'Are you sure?', 'bookly' ),
            'categories'     => $categories,
            'uncategorized'  => esc_attr__( 'Uncategorized', 'bookly' ),
            'edit'           => esc_attr__( 'Edit', 'bookly' ),
            'reorder'        => esc_attr__( 'Reorder', 'bookly' ),
            'noResultFound'  => esc_attr__( 'No result found', 'bookly' ),
            'datatables'     => $datatables,
        ) );

        self::renderTemplate( 'index', compact( 'categories', 'datatables' ) );
    }
}