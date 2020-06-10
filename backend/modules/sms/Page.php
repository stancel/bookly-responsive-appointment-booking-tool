<?php
namespace Bookly\Backend\Modules\Sms;

use Bookly\Lib;

/**
 * Class Controller
 * @package Bookly\Backend\Modules\Sms
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array_merge(
                array( 'css/ladda.min.css', ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' )
            ),
            'backend'  => array( 'bootstrap/css/bootstrap.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'         => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
            ),
            'frontend' => array_merge(
                array(
                    'js/spin.min.js'  => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'module' => array(
                'js/sms.js' => array( 'bookly-notifications-list.js', ),
                'js/notifications-list.js' => array( 'jquery', ),
            ),
        ) );

        $alert  = array( 'success' => array(), 'error' => array() );
        $prices = array();
        $sms    = new Lib\SMS();

        $show_form  = get_option( 'bookly_sms_unverified_token' ) ? 'confirm' : 'registration';
        $user_login = null;
        if ( self::hasParameter( 'form-login' ) ) {
            if ( $sms->login( self::parameter( 'username' ), self::parameter( 'password' ) ) === 'ERROR_EMAIL_CONFIRM_REQUIRED' ) {
                $user_login = self::parameter( 'username' );
                $show_form  = 'confirm';
            }
        } elseif ( self::hasParameter( 'form-logout' ) ) {
            $sms->logout();
        } elseif ( self::hasParameter( 'form-registration' ) ) {
            if ( self::parameter( 'accept_tos', false ) ) {
                $success = $sms->register(
                    self::parameter( 'username' ),
                    self::parameter( 'password' ),
                    self::parameter( 'password_repeat' )
                );
                if ( $success ) {
                    $user_login = self::parameter( 'username' );
                    $show_form  = 'confirm';
                    update_option( 'bookly_sms_unverified_token', $success->token );
                    update_option( 'bookly_sms_unverified_username', $user_login );
                } else {
                    $show_form  = 'registration';
                }
            } else {
                $alert['error'][] = __( 'Please accept terms and conditions.', 'bookly' );
            }
        }
        if ( $user_login !== null || self::hasParameter( 'form-registration' ) ) {
            $is_logged_in = false;
        } else {
            $is_logged_in = $sms->loadProfile();
        }

        if ( ! $is_logged_in ) {
            if ( $response = $sms->getPriceList() ) {
                $prices = $response->list;
            }
            if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                // Hide authentication errors on auto login.
                $sms->clearErrors();
            }
        } else {
            switch ( self::parameter( 'paypal_result' ) ) {
                case 'success':
                    $alert['success'][] = __( 'Your payment has been accepted for processing.', 'bookly' );
                    break;
                case 'cancel':
                    $alert['error'][] = __( 'Your payment has been interrupted.', 'bookly' );
                    break;
            }
            if ( self::hasParameter( 'tab' ) ) {
                switch ( self::parameter( 'auto-recharge' ) ) {
                    case 'approved':
                        $alert['success'][] = __( 'Auto-Recharge enabled.', 'bookly' );
                        break;
                    case 'declined':
                        $alert['error'][] = __( 'You declined the Auto-Recharge of your balance.', 'bookly' );
                        break;
                }
            }
        }
        $current_tab    = self::hasParameter( 'tab' ) ? self::parameter( 'tab' ) : 'notifications';
        $alert['error'] = array_merge( $alert['error'], array_values( $sms->getErrors() ) );
        // Services in custom notifications where the recipient is client only.
        $only_client = Lib\Entities\Service::query()->whereIn( 'type', array( Lib\Entities\Service::TYPE_COMPOUND, Lib\Entities\Service::TYPE_COLLABORATIVE ) )->fetchCol( 'id' );

        // Prepare tables settings.
        $datatables = Lib\Utils\Tables::getSettings( array(
            'sms_notifications',
            'sms_purchases',
            'sms_details',
            'sms_prices',
            'sms_sender',
        ) );

        wp_localize_script( 'bookly-daterangepicker.js', 'BooklyL10n',
            array(
                'csrfToken'          => Lib\Utils\Common::getCsrfToken(),
                'smsAlert'           => $alert,
                'areYouSure'         => __( 'Are you sure?', 'bookly' ),
                'cancel'             => __( 'Cancel', 'bookly' ),
                'country'            => get_option( 'bookly_cst_phone_default_country' ),
                'current_tab'        => $current_tab,
                'mjsDateFormat'      => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
                'input_old_password' => __( 'Please enter old password.', 'bookly' ),
                'passwords_no_same'  => __( 'Passwords must be the same.', 'bookly' ),
                'intlTelInput'       => array(
                    'country' => get_option( 'bookly_cst_phone_default_country' ),
                    'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                    'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                ),
                'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
                'dateRange'  => Lib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'bookly' ), ) ),
                'sender_id'  => array(
                    'sent'        => __( 'Sender ID request is sent.', 'bookly' ),
                    'set_default' => __( 'Sender ID is reset to default.', 'bookly' ),
                ),
                'zeroRecords'  => __( 'No records for selected period.', 'bookly' ),
                'zeroRecords2' => __( 'No records.', 'bookly' ),
                'processing'   => __( 'Processing...', 'bookly' ),
                'onlyClient'   => $only_client,
                'invoice'      => array(
                    'button' => __( 'Invoice', 'bookly' ),
                    'alert'  => __( 'To generate an invoice you should fill in company information in Bookly > SMS Notifications > Send invoice.', 'bookly' ),
                    'link'   => $sms->getInvoiceLink(),
                ),
                'state'          => array( __( 'Disabled', 'bookly' ), __( 'Enabled', 'bookly' ) ),
                'action'         => array( __( 'enable', 'bookly' ), __( 'disable', 'bookly' ) ),
                'edit'           => __( 'Edit', 'bookly' ),
                'settingsSaved'  => __( 'Settings saved.', 'bookly' ),
                'gateway'        => 'sms',
                'datatables'     => $datatables
            )
        );
        foreach ( range( 1, 23 ) as $hours ) {
            $bookly_ntf_processing_interval_values[] = array( $hours, Lib\Utils\DateTime::secondsToInterval( $hours * HOUR_IN_SECONDS ) );
        }

        // Number of undelivered sms.
        $undelivered_count = Lib\SMS::getUndeliveredSmsCount();

        self::renderTemplate( 'index', compact( 'sms', 'is_logged_in', 'prices', 'bookly_ntf_processing_interval_values', 'undelivered_count', 'user_login', 'show_form', 'datatables' ) );
    }

    /**
     * Show 'SMS Notifications' submenu with counter inside Bookly main menu.
     */
    public static function addBooklyMenuItem()
    {
        $sms   = __( 'SMS Notifications', 'bookly' );
        $count = Lib\SMS::getUndeliveredSmsCount();

        add_submenu_page(
            'bookly-menu',
            $sms,
            $count ? sprintf( '%s <span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $sms, $count, $count ) : $sms,
            Lib\Utils\Common::getRequiredCapability(),
            self::pageSlug(),
            function () { Page::render(); }
        );
    }
}