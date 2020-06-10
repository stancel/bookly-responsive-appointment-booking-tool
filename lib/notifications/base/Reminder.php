<?php
namespace Bookly\Lib\Notifications\Base;

use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Notifications\Assets\Base\Attachments;
use Bookly\Lib\Notifications\Assets\Base\Codes;
use Bookly\Lib\Proxy;
use Bookly\Lib\SMS;
use Bookly\Lib\Utils;

/**
 * Class Reminder
 * @package Bookly\Lib\Notifications\Base
 */
abstract class Reminder
{
    const RECIPIENT_ADMINS = 'admins';
    const RECIPIENT_CLIENT = 'client';
    const RECIPIENT_STAFF  = 'staff';

    const SEND_AS_HTML = 'html';
    const SEND_AS_TEXT = 'text';

    /** @var SMS */
    protected static $sms;

    /**
     * Send notification to administrators.
     *
     * @param Notification $notification
     * @param Codes $codes
     * @param Attachments $attachments
     * @param array $reply_to
     * @param array|bool $queue
     * @return bool
     */
    public static function sendToAdmins(Notification $notification, Codes $codes, $attachments = null, $reply_to = null, &$queue = false )
    {
        if ( ! $notification->getToAdmin() ) {
            // No recipient.
            return false;
        }

        if ( $notification->getGateway() == 'sms' ) {
            return static::_sendSmsTo(
                self::RECIPIENT_ADMINS,
                get_option( 'bookly_sms_administrator_phone', '' ),
                $notification,
                $codes,
                array( 'name' => __( 'Admins', 'bookly' ) ),
                $queue
            );
        } else {
            return static::_sendEmailTo(
                self::RECIPIENT_ADMINS,
                Utils\Common::getAdminEmails(),
                $notification,
                $codes,
                $attachments,
                $reply_to,
                null,
                null,
                array( 'name' => __( 'Admins', 'bookly' ) ),
                $queue
            );
        }
    }

    /**
     * Send notification to client.
     *
     * @param Customer $customer
     * @param Notification $notification
     * @param Codes $codes
     * @param Attachments $attachments
     * @param bool|array $queue
     * @return bool
     */
    public static function sendToClient( Customer $customer, Notification $notification, Codes $codes, $attachments = null, &$queue = false )
    {
        if ( ! $notification->getToCustomer() ) {
            // No recipient.
            return false;
        }

        if ( $notification->getGateway() == 'sms' ) {
            return static::_sendSmsTo(
                self::RECIPIENT_CLIENT,
                $customer->getPhone(),
                $notification,
                $codes,
                array( 'name' => $customer->getFullName() ),
                $queue
            );
        } else {
            return static::_sendEmailTo(
                self::RECIPIENT_CLIENT,
                $customer->getEmail(),
                $notification,
                $codes,
                $attachments,
                null,
                null,
                null,
                array( 'name' => $customer->getFullName() ),
                $queue
            );
        }
    }

    /**
     * Send notification to staff.
     *
     * @param Staff $staff
     * @param Notification $notification
     * @param Codes $codes
     * @param Attachments $attachments
     * @param array $reply_to
     * @param array|bool $queue
     * @return bool
     */
    public static function sendToStaff( Staff $staff, Notification $notification, Codes $codes, $attachments = null, $reply_to = null, &$queue = false )
    {
        if ( ! $notification->getToStaff() || $staff->isArchived() ) {
            // No recipient.
            return false;
        }

        if ( $notification->getGateway() == 'sms' ) {
            return static::_sendSmsTo(
                self::RECIPIENT_STAFF,
                $staff->getPhone(),
                $notification,
                $codes,
                array( 'name' => $staff->getFullName() ),
                $queue
            );
        } else {
            return static::_sendEmailTo(
                self::RECIPIENT_STAFF,
                $staff->getEmail(),
                $notification,
                $codes,
                $attachments,
                $reply_to,
                null,
                null,
                array( 'name' => $staff->getFullName() ),
                $queue
            );
        }
    }

    /**
     * Send email.
     *
     * @param string $recipient
     * @param string|array $to_email
     * @param Notification $notification
     * @param Codes $codes,
     * @param Attachments $attachments
     * @param array $reply_to
     * @param string $force_send_as
     * @param array $force_from
     * @param array $queue_data
     * @param bool|array $queue
     * @return bool
     */
    protected static function _sendEmailTo(
        $recipient,
        $to_email,
        Notification $notification,
        Codes $codes,
        $attachments = null,
        $reply_to = null,
        $force_send_as = null,
        $force_from = null,
        $queue_data = array(),
        &$queue = false
    )
    {
        if ( empty ( $to_email ) ) {
            return false;
        }

        $send_as = $force_send_as ?: get_option( 'bookly_email_send_as', self::SEND_AS_HTML );
        $from    = $force_from    ?: array(
            'name'  => get_option( 'bookly_email_sender_name' ),
            'email' => get_option( 'bookly_email_sender' ),
        );

        // Subject & message.
        if ( $recipient == self::RECIPIENT_CLIENT ) {
            $subject = $notification->getTranslatedSubject();
            $message = $notification->getTranslatedMessage();
        } else {
            $subject = $notification->getSubject();
            $message = Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), $recipient, 'email' );
        }
        $subject = $codes->replace( $subject, 'text' );
        if ( $send_as == self::SEND_AS_HTML ) {
            $message = wpautop( $codes->replace( $message, 'html' ) );
        } else {
            $message = $codes->replace( $message, 'text' );
        }

        // Headers.
        $headers = array();
        $headers[] = strtr( 'Content-Type: content_type; charset=utf-8', array(
            'content_type' => $send_as == self::SEND_AS_HTML ? 'text/html' : 'text/plain'
        ) );
        $headers[] = strtr( 'From: name <email>', $from );
        if ( isset ( $reply_to ) ) {
            $headers[] = strtr( 'Reply-To: name <email>', $reply_to );
        }

        // Do send.
        if ( $queue !== false ) {
            $queue[] = array(
                'data'        => $queue_data,
                'gateway'     => $notification->getGateway(),
                'name'        => $notification->getName(),
                'address'     => $to_email,
                'subject'     => $subject,
                'message'     => $message,
                'headers'     => $headers,
                'attachments' => $attachments ? $attachments->createFor( $notification ) : array(),
            );

            return true;
        } else {
            return wp_mail( $to_email, $subject, $message, $headers, $attachments ? $attachments->createFor( $notification ) : array() );
        }
    }

    /**
     * Send SMS.
     *
     * @param string $recipient
     * @param string $phone
     * @param Notification $notification
     * @param Codes $codes
     * @param array $queue_data,
     * @param array|bool $queue
     * @return bool
     */
    protected static function _sendSmsTo( $recipient, $phone, $notification, Codes $codes, $queue_data = array(), &$queue = false )
    {
        if ( $phone == '' ) {
            return false;
        }

        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        // Message.
        if ( $recipient == self::RECIPIENT_CLIENT ) {
            $message = $notification->getTranslatedMessage();
        } else {
            $message = Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), $recipient, 'sms' );
        }
        $message = $codes->replaceForSms( $message );

        // Do send.
        if ( $queue !== false ) {
            $queue[] = array(
                'data'       => $queue_data,
                'gateway'    => $notification->getGateway(),
                'name'       => $notification->getName(),
                'address'    => $phone,
                'message'    => $message['personal'],
                'impersonal' => $message['impersonal'],
                'type_id'    => $notification->getTypeId(),
            );

            return true;
        } else {
            return self::$sms->sendSms( $phone, $message['personal'], $message['impersonal'], $notification->getTypeId() );
        }

    }
}