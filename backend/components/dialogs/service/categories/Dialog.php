<?php
namespace Bookly\Backend\Components\Dialogs\Service\Categories;

use Bookly\Lib;
use Bookly\Backend\Modules\Services\Proxy;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Service\Categories
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create service dialog.
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
                'js/sortable.min.js' => array( 'jquery' ),
                'js/select2.min.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/service-categories-dialog.js' => array( 'jquery', 'bookly-sortable.min.js') ),
        ) );

        wp_localize_script( 'bookly-service-categories-dialog.js', 'BooklyServiceCreateDialogL10n', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
        ) );

        self::renderTemplate( 'dialog' );
    }
}