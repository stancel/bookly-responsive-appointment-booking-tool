<?php
namespace Bookly\Backend\Components\Dialogs\Service\Order;

use Bookly\Lib;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Service\Order
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create service dialog.
     */
    public static function render( array $services = array() )
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array( 'css/fontawesome-all.min.css' ),
        ) );

        self::enqueueScripts( array(
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery', ),
                'js/ladda.min.js' => array( 'jquery', ),
            ),
            'module'   => array( 'js/service-order-dialog.js' => array( 'jquery', ) ),
        ) );

        wp_localize_script( 'bookly-service-order-dialog.js', 'BooklyServiceOrderDialogL10n', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
            'services'  => $services,
        ) );

        self::renderTemplate( 'dialog' );
    }
}