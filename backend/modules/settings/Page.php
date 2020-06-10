<?php
namespace Bookly\Backend\Modules\Settings;

use Bookly\Lib;
use Bookly\Backend\Components\Schedule\Component as ScheduleComponent;
/**
 * Class Page
 * @package Bookly\Backend\Modules\Settings
 */
class Page extends Lib\Base\Ajax
{
    /**
     * Render page.
     */
    public static function render()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        wp_enqueue_media();
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap.min.css', )
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/sortable.min.js' => array( 'jquery' ),
                'js/jCal.js'  => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
                'js/range-tools.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/settings.js' => array( 'jquery', 'bookly-intlTelInput.min.js', 'bookly-sortable.min.js', 'bookly-range-tools.js' ) ),
            'frontend' => array(
                'js/intlTelInput.min.js' => array( 'jquery' ),
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );

        $current_tab = self::hasParameter( 'tab' ) ? self::parameter( 'tab' ) : 'general';
        $alert = array( 'success' => array(), 'error' => array() );

        // Save the settings.
        if ( ! empty ( $_POST ) ) {
            if ( self::csrfTokenValid() ) {
                switch ( self::parameter( 'tab' ) ) {
                    case 'calendar':  // Calendar form.
                        update_option( 'bookly_cal_one_participant',   self::parameter( 'bookly_cal_one_participant' ) );
                        update_option( 'bookly_cal_many_participants', self::parameter( 'bookly_cal_many_participants' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'payments':  // Payments form.
                        update_option( 'bookly_pmt_order', self::parameter( 'bookly_pmt_order' ) );
                        update_option( 'bookly_pmt_currency', self::parameter( 'bookly_pmt_currency' ) );
                        update_option( 'bookly_pmt_price_format', self::parameter( 'bookly_pmt_price_format' ) );
                        update_option( 'bookly_pmt_local', self::parameter( 'bookly_pmt_local' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'business_hours':  // Business hours form.
                        foreach ( array( 'bookly_bh_monday_start', 'bookly_bh_monday_end', 'bookly_bh_tuesday_start', 'bookly_bh_tuesday_end', 'bookly_bh_wednesday_start', 'bookly_bh_wednesday_end', 'bookly_bh_thursday_start', 'bookly_bh_thursday_end', 'bookly_bh_friday_start', 'bookly_bh_friday_end', 'bookly_bh_saturday_start', 'bookly_bh_saturday_end', 'bookly_bh_sunday_start', 'bookly_bh_sunday_end', ) as $option_name ) {
                            update_option( $option_name, self::parameter( $option_name ) );
                        }
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'general':  // General form.
                        $bookly_gen_time_slot_length = self::parameter( 'bookly_gen_time_slot_length' );
                        if ( in_array( $bookly_gen_time_slot_length, array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) ) ) {
                            update_option( 'bookly_gen_time_slot_length', $bookly_gen_time_slot_length );
                        }
                        update_option( 'bookly_gen_service_duration_as_slot_length', (int) self::parameter( 'bookly_gen_service_duration_as_slot_length' ) );
                        update_option( 'bookly_gen_allow_staff_edit_profile', (int) self::parameter( 'bookly_gen_allow_staff_edit_profile' ) );
                        update_option( 'bookly_gen_default_appointment_status', self::parameter( 'bookly_gen_default_appointment_status' ) );
                        update_option( 'bookly_gen_link_assets_method', self::parameter( 'bookly_gen_link_assets_method' ) );
                        update_option( 'bookly_gen_max_days_for_booking', (int) self::parameter( 'bookly_gen_max_days_for_booking' ) );
                        update_option( 'bookly_gen_use_client_time_zone', (int) self::parameter( 'bookly_gen_use_client_time_zone' ) );
                        update_option( 'bookly_gen_collect_stats', self::parameter( 'bookly_gen_collect_stats' ) );
                        update_option( 'bookly_app_show_powered_by', self::parameter( 'bookly_app_show_powered_by' ) );
                        update_option( 'bookly_app_prevent_caching', (int) self::parameter( 'bookly_app_prevent_caching' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'url': // URL settings form.
                        update_option( 'bookly_url_approve_page_url', self::parameter( 'bookly_url_approve_page_url' ) );
                        update_option( 'bookly_url_approve_denied_page_url', self::parameter( 'bookly_url_approve_denied_page_url' ) );
                        update_option( 'bookly_url_cancel_page_url', self::parameter( 'bookly_url_cancel_page_url' ) );
                        update_option( 'bookly_url_cancel_denied_page_url', self::parameter( 'bookly_url_cancel_denied_page_url' ) );
                        update_option( 'bookly_url_reject_denied_page_url', self::parameter( 'bookly_url_reject_denied_page_url' ) );
                        update_option( 'bookly_url_reject_page_url', self::parameter( 'bookly_url_reject_page_url' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'customers':  // Customers form.
                        update_option( 'bookly_cst_allow_duplicates',           self::parameter( 'bookly_cst_allow_duplicates' ) );
                        update_option( 'bookly_cst_default_country_code',       self::parameter( 'bookly_cst_default_country_code' ) );
                        update_option( 'bookly_cst_phone_default_country',      self::parameter( 'bookly_cst_phone_default_country' ) );
                        update_option( 'bookly_cst_remember_in_cookie',         self::parameter( 'bookly_cst_remember_in_cookie' ) );
                        update_option( 'bookly_cst_show_update_details_dialog', self::parameter( 'bookly_cst_show_update_details_dialog' ) );
                        // Update email required option if creating wordpress account for customers
                        $bookly_cst_required_details = get_option( 'bookly_cst_required_details', array() );
                        if ( self::parameter( 'bookly_cst_create_account' ) && ! in_array( 'email', $bookly_cst_required_details ) ) {
                            $bookly_cst_required_details[] = 'email';
                            update_option( 'bookly_cst_required_details', $bookly_cst_required_details );
                        }
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                    case 'company':  // Company form.
                        update_option( 'bookly_co_address', self::parameter( 'bookly_co_address' ) );
                        update_option( 'bookly_co_logo_attachment_id', self::parameter( 'bookly_co_logo_attachment_id' ) );
                        update_option( 'bookly_co_name',    self::parameter( 'bookly_co_name' ) );
                        update_option( 'bookly_co_phone',   self::parameter( 'bookly_co_phone' ) );
                        update_option( 'bookly_co_website', self::parameter( 'bookly_co_website' ) );
                        $alert['success'][] = __( 'Settings saved.', 'bookly' );
                        break;
                }

                // Let Add-ons save their settings.
                $alert = Proxy\Shared::saveSettings( $alert, self::parameter( 'tab' ), self::postParameters() );
            }
        }

        // Check if WooCommerce cart exists.
        if ( get_option( 'bookly_wc_enabled' ) && class_exists( 'WooCommerce', false ) ) {
            $post = get_post( wc_get_page_id( 'cart' ) );
            if ( $post === null || $post->post_status != 'publish' ) {
                $alert['error'][] = sprintf(
                    __( 'WooCommerce cart is not set up. Follow the <a href="%s">link</a> to correct this problem.', 'bookly' ),
                    Lib\Utils\Common::escAdminUrl( 'wc-status', array( 'tab' => 'tools' ) )
                );
            }
        }

        Proxy\Shared::enqueueAssets();

        wp_localize_script( 'bookly-settings.js', 'BooklyL10n', array(
            'alert'              => $alert,
            'current_tab'        => $current_tab,
            'csrf_token'         => Lib\Utils\Common::getCsrfToken(),
            'default_country'    => get_option( 'bookly_cst_phone_default_country' ),
            'holidays'           => self::_getHolidays(),
            'loading_img'        => plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ),
            'firstDay'           => get_option( 'start_of_week' ),
            'days'               => array_values( $wp_locale->weekday_abbrev ),
            'months'             => array_values( $wp_locale->month ),
            'close'              => __( 'Close', 'bookly' ),
            'repeat'             => __( 'Repeat every year', 'bookly' ),
            'we_are_not_working' => __( 'We are not working on this day', 'bookly' ),
            'sample_price'       => number_format_i18n( 10, 3 ),
        ) );
        $values = array();
        foreach ( array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) as $duration ) {
            $values['bookly_gen_time_slot_length'][] = array( $duration, Lib\Utils\DateTime::secondsToInterval( $duration * MINUTE_IN_SECONDS ) );
        }

        $payments = self::_getPayments();
        $business_hours = self::_getBusinessHours();

        self::renderTemplate( 'index', compact( 'values', 'payments', 'business_hours' ) );
    }

    /**
     * Get holidays.
     *
     * @return array
     */
    protected static function _getHolidays()
    {
        $collection = Lib\Entities\Holiday::query()->where( 'staff_id', null )->fetchArray();
        $holidays = array();
        if ( count( $collection ) ) {
            foreach ( $collection as $holiday ) {
                $holidays[ $holiday['id'] ] = array(
                    'm' => (int) date( 'm', strtotime( $holiday['date'] ) ),
                    'd' => (int) date( 'd', strtotime( $holiday['date'] ) ),
                );
                // If not repeated holiday, add the year
                if ( ! $holiday['repeat_event'] ) {
                    $holidays[ $holiday['id'] ]['y'] = (int) date( 'Y', strtotime( $holiday['date'] ) );
                }
            }
        }

        return $holidays;
    }

    /**
     * @return ScheduleComponent
     */
    protected static function _getBusinessHours()
    {
        $business_hours = new ScheduleComponent( 'bookly_bh_{index}_start', 'bookly_bh_{index}_end', false );
        $week_days = array(
            1 => 'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        );
        $start_of_week = (int) get_option( 'start_of_week' );
        for ( $i = 1; $i <= 7; $i ++ ) {
            $index = ( $start_of_week + $i ) < 8 ? $start_of_week + $i : $start_of_week + $i - 7;
            $day = $week_days[ $index ];
            $business_hours->addHours( $day, $index, get_option( 'bookly_bh_' . $day . '_start' ), get_option( 'bookly_bh_' . $day . '_end' ) );
        }

        return $business_hours;
    }

    /**
     * @return array
     */
    protected static function _getPayments()
    {
        $payments     = array();
        $payment_data = array(
            'local' => self::renderTemplate( '_payment_local', array(), false ),
        );
        $payment_data = Proxy\Shared::preparePaymentGatewaySettings( $payment_data );
        $order        = explode( ',', get_option( 'bookly_pmt_order' ) );
        foreach ( $order as $payment_system ) {
            if ( array_key_exists( $payment_system, $payment_data ) ) {
                $payments[] = $payment_data[ $payment_system ];
            }
        }
        foreach ( $payment_data as $slug => $data ) {
            if ( ! $order || ! in_array( $slug, $order ) ) {
                $payments[] = $data;
            }
        }

        return $payments;
    }
}