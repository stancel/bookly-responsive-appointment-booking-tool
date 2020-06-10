<?php
namespace Bookly\Lib\Utils;

use Bookly\Lib;

/**
 * Class Tables
 * @package Bookly\Lib\Utils
 */
abstract class Tables
{
    const APPOINTMENTS        = 'appointments';
    const COUPONS             = 'coupons';
    const CUSTOM_STATUSES     = 'custom_statuses';
    const CUSTOMER_GROUPS     = 'customer_groups';
    const CUSTOMERS           = 'customers';
    const EMAIL_NOTIFICATIONS = 'email_notifications';
    const LOCATIONS           = 'locations';
    const PACKAGES            = 'packages';
    const PAYMENTS            = 'payments';
    const SERVICES            = 'services';
    const SMS_DETAILS         = 'sms_details';
    const SMS_NOTIFICATIONS   = 'sms_notifications';
    const SMS_PRICES          = 'sms_prices';
    const SMS_PURCHASES       = 'sms_purchases';
    const SMS_SENDER          = 'sms_sender';
    const STAFF_MEMBERS       = 'staff_members';
    const TAXES               = 'taxes';

    /**
     * Get columns for given table.
     *
     * @param string $table
     * @return array
     */
    public static function getColumns( $table )
    {
        $columns = array();
        switch ( $table ) {
            case self::APPOINTMENTS:
                $columns = array(
                    'id'                 => esc_attr__( 'No.', 'bookly' ),
                    'start_date'         => esc_attr__( 'Appointment date', 'bookly' ),
                    'staff_name'         => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ),
                    'customer_full_name' => esc_attr__( 'Customer name', 'bookly' ),
                    'customer_phone'     => esc_attr__( 'Customer phone', 'bookly' ),
                    'customer_email'     => esc_attr__( 'Customer email', 'bookly' ),
                    'service_title'      => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_service' ) ),
                    'service_duration'   => esc_attr__( 'Duration', 'bookly' ),
                    'status'             => esc_attr__( 'Status', 'bookly' ),
                    'payment'            => esc_attr__( 'Payment', 'bookly' ),
                    'notes'              => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_notes' ) ),
                    'created_date'       => esc_attr__( 'Created', 'bookly' ),
                );
                break;
            case self::CUSTOMERS:
                $columns = array(
                    'full_name'          => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_name' ) ),
                    'first_name'         => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_first_name' ) ),
                    'last_name'          => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_last_name' ) ),
                    'wp_user'            => esc_attr__( 'User', 'bookly' ),
                    'phone'              => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_phone' ) ),
                    'email'              => esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_email' ) ),
                    'notes'              => esc_attr__( 'Notes', 'bookly' ),
                    'last_appointment'   => esc_attr__( 'Last appointment', 'bookly' ),
                    'total_appointments' => esc_attr__( 'Total appointments', 'bookly' ),
                    'payments'           => esc_attr__( 'Payments', 'bookly' ),
                );
                break;
            case self::EMAIL_NOTIFICATIONS:
                $columns = array(
                    'type'   => esc_attr__( 'Type', 'bookly' ),
                    'name'   => esc_attr__( 'Name', 'bookly' ),
                    'active' => esc_attr__( 'State', 'bookly' ),
                );
                break;
            case self::PAYMENTS:
                $columns = array(
                    'id'         => esc_attr__( 'No.', 'bookly' ),
                    'created'    => esc_attr__( 'Date', 'bookly' ),
                    'type'       => esc_attr__( 'Type', 'bookly' ),
                    'customer'   => esc_attr__( 'Customer', 'bookly' ),
                    'provider'   => esc_attr__( 'Provider', 'bookly' ),
                    'service'    => esc_attr__( 'Service', 'bookly' ),
                    'start_date' => esc_attr__( 'Appointment date', 'bookly' ),
                    'paid'       => esc_attr__( 'Amount', 'bookly' ),
                    'status'     => esc_attr__( 'Status', 'bookly' ),
                );
                break;
            case self::SERVICES:
                $columns = array(
                    'title'         => esc_attr__( 'Title', 'bookly' ),
                    'category_name' => esc_attr__( 'Category', 'bookly' ),
                    'duration'      => esc_attr__( 'Duration', 'bookly' ),
                    'price'         => esc_attr__( 'Price', 'bookly' ),
                );
                break;
            case self::SMS_DETAILS:
                $columns = array(
                    'date'      => esc_attr__( 'Date', 'bookly' ),
                    'time'      => esc_attr__( 'Time', 'bookly' ),
                    'message'   => esc_attr__( 'Text', 'bookly' ),
                    'phone'     => esc_attr__( 'Phone', 'bookly' ),
                    'sender_id' => esc_attr__( 'Sender ID', 'bookly' ),
                    'charge'    => esc_attr__( 'Cost', 'bookly' ),
                    'status'    => esc_attr__( 'Status', 'bookly' ),
                    'info'      => esc_attr__( 'Info', 'bookly' ),
                );
                break;
            case self::SMS_NOTIFICATIONS:
                $columns = array(
                    'type'   => esc_attr__( 'Type', 'bookly' ),
                    'name'   => esc_attr__( 'Name', 'bookly' ),
                    'active' => esc_attr__( 'State', 'bookly' ),
                );
                break;
            case self::SMS_PRICES:
                $columns = array(
                    'country_iso_code' => esc_attr__( 'Flag', 'bookly' ),
                    'country_name'     => esc_attr__( 'Country', 'bookly' ),
                    'phone_code'       => esc_attr__( 'Code', 'bookly' ),
                    'price'            => esc_attr__( 'Regular price', 'bookly' ),
                    'price_alt'        => esc_attr__( 'Price with custom Sender ID', 'bookly' ),
                );
                break;
            case self::SMS_PURCHASES:
                $columns = array(
                    'date'   => esc_attr__( 'Date', 'bookly' ),
                    'time'   => esc_attr__( 'Time', 'bookly' ),
                    'type'   => esc_attr__( 'Type', 'bookly' ),
                    'order'  => esc_attr__( 'Order', 'bookly' ),
                    'status' => esc_attr__( 'Status', 'bookly' ),
                    'amount' => esc_attr__( 'Amount', 'bookly' ),
                );
                break;
            case self::SMS_SENDER:
                $columns = array(
                    'date'        => esc_attr__( 'Date', 'bookly' ),
                    'name'        => esc_attr__( 'Requested ID', 'bookly' ),
                    'status'      => esc_attr__( 'Status', 'bookly' ),
                    'status_date' => esc_attr__( 'Status date', 'bookly' ),
                );
                break;
            case self::STAFF_MEMBERS:
                $columns = array(
                    'full_name' => esc_attr__( 'Name', 'bookly' ),
                    'email'     => esc_attr__( 'Email', 'bookly' ),
                    'phone'     => esc_attr__( 'Phone', 'bookly' ),
                    'wp_user'   => esc_attr__( 'User', 'bookly' ),
                );
                break;
        }

        return Lib\Proxy\Shared::prepareTableColumns( $columns, $table );
    }

    /**
     * Get table settings.
     *
     * @param string|array $tables
     *
     * @return array
     */
    public static function getSettings( $tables )
    {
        if ( ! is_array( $tables ) ) {
            $tables = array( $tables );
        }
        $result = array();
        foreach ( $tables as $table ) {
            $columns = self::getColumns( $table );
            $meta    = get_user_meta( get_current_user_id(), 'bookly_' . $table . '_table_settings', true );

            $exist = true;
            if ( ! $meta ) {
                $exist = false;
                $meta  = array();
            }

            if ( ! isset ( $meta['columns'] ) ) {
                $meta['columns'] = array();
            }
            // Remove columns with no title.
            foreach ( $meta['columns'] as $key => $column ) {
                if ( ! isset( $columns[ $key ] ) ) {
                    unset( $meta['columns'][ $key ] );
                }
            }
            // Show all new columns, which not saved at meta.
            foreach ( $columns as $column => $title ) {
                if ( ! isset ( $meta['columns'][ $column ] ) ) {
                    $meta['columns'][ $column ] = true;
                }
            }

            $result[ $table ] = array(
                'settings' => array(
                    'columns' => $meta['columns'],
                    'filter'  => isset ( $meta['filter'] ) ? $meta['filter'] : array(),
                    'order'   => isset ( $meta['order'] ) ? $meta['order'] : array(),
                ),
                'titles'   => $columns,
                'exist'    => $exist,
            );
        }

        return $result;
    }

    /**
     * Update table settings.
     *
     * @param string $table
     * @param array  $columns
     * @param array  $order
     * @param array  $filter
     */
    public static function updateSettings( $table, $columns, $order, $filter )
    {
        $meta = get_user_meta( get_current_user_id(), 'bookly_' . $table . '_table_settings', true ) ?: array();
        if ( $columns !== null && $order !== null ) {
            $order_columns = array();
            foreach ( $order as $sort_by ) {
                if ( isset( $columns[ $sort_by['column'] ] ) ) {
                    $order_columns[] = array(
                        'column' => $columns[ $sort_by['column'] ]['data'],
                        'order'  => $sort_by['dir'],
                    );
                }
            }
            $meta['order'] = $order_columns;
        }

        $meta['filter'] = $filter;

        update_user_meta( get_current_user_id(), 'bookly_' . $table . '_table_settings', $meta );
    }
}