<?php
namespace Bookly\Backend\Components\Dialogs\Staff\Order;

use Bookly\Lib;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Staff\Order
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create service dialog.
     */
    public static function render()
    {
        global $wpdb;

        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array( 'css/fontawesome-all.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array( 'js/sortable.min.js' ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery', ),
                'js/ladda.min.js' => array( 'jquery', ),
            ),
            'module'   => array( 'js/staff-order-dialog.js' => array( 'jquery', 'bookly-sortable.min.js' ) ),
        ) );

        $query = Lib\Entities\Staff::query( 's' )
            ->select( 's.id, s.full_name' )
            ->tableJoin( $wpdb->users, 'wpu', 'wpu.ID = s.wp_user_id' );

        if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
            $query->where( 's.wp_user_id', get_current_user_id() );
        }

        wp_localize_script( 'bookly-staff-order-dialog.js', 'BooklyStaffOrderDialogL10n', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
            'staff' => $query->sortBy( 'position' )->fetchArray()
        ) );

        self::renderTemplate( 'dialog' );
    }
}