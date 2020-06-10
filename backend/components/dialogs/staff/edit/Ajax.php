<?php
namespace Bookly\Backend\Components\Dialogs\Staff\Edit;

use Bookly\Backend\Components\Schedule\BreakItem;
use Bookly\Backend\Components\Schedule\Component as ScheduleComponent;
use Bookly\Backend\Modules\Staff\Proxy as StaffProxy;
use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Components\Dialogs\Staff\Edit
 */
class Ajax extends Lib\Base\Ajax
{
    /** @var Lib\Entities\Staff */
    protected static $staff;

    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        $permissions = get_option( 'bookly_gen_allow_staff_edit_profile' )
            ? array( '_default' => 'staff' )
            : array();
        if ( Lib\Config::staffCabinetActive() ) {
            $permissions = array( '_default' => 'staff' );
        }

        return $permissions;
    }

    /**
     * Get data for staff
     */
    public static function getStaffData()
    {
        $data = StaffProxy\Shared::editStaff(
            array( 'alert' => array( 'error' => array() ), 'tpl' => array() ),
            self::$staff
        );
        $tpl_data = $data['tpl'];

        $users_for_staff = Lib\Utils\Common::isCurrentUserAdmin() ? self::_getUsersForStaff( self::$staff->getId() ) : array();

        $staff_fields = self::$staff->getFields();
        $src = false;
        if ( $staff_fields['attachment_id'] ) {
            $src = wp_get_attachment_image_src( $staff_fields['attachment_id'], 'thumbnail' );
        }
        $staff_fields['avatar'] = $src ? $src[0] : null;

        $response = array(
            'html' => array(
                'edit'    => self::renderTemplate( 'dialog_body', array( 'staff' => self::$staff ), false ),
                'details' => self::renderTemplate( 'details', array( 'staff' => self::$staff, 'users_for_staff' => $users_for_staff, 'tpl_data' => $tpl_data ), false ),
            ),
        );
        if ( self::$staff->getId() ) {
            $response['holidays'] = self::$staff->getHolidays();
            $response['html']['services']     = self::_getStaffServices( self::$staff->getId(), null );
            $response['html']['schedule']     = self::_getStaffSchedule( self::$staff->getId(), null );
            $response['html']['special_days'] = Proxy\SpecialDays::getStaffSpecialDaysHtml( '', self::$staff->getId() );
            $response['html']['holidays']     = self::renderTemplate( 'holidays', array( 'holidays' => $response['holidays'] ), false );
            $response['staff'] = $staff_fields;
            $response['alert'] = $data['alert'];
        }

        wp_send_json_success( $response );
    }

    /**
     * Update staff from POST request.
     */
    public static function updateStaff()
    {
        if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
            // Check permissions to prevent one staff member from updating profile of another staff member.
            do {
                if ( self::parameter( 'staff_cabinet' ) && Lib\Config::staffCabinetActive() ) {
                    $allow = true;
                } else {
                    $allow = get_option( 'bookly_gen_allow_staff_edit_profile' );
                }
                if ( $allow ) {
                    unset ( $_POST['wp_user_id'] );
                    break;
                }
                do_action( 'admin_page_access_denied' );
                wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
            } while ( 0 );
        } elseif ( self::parameter( 'id' ) == 0
                && ! Lib\Config::proActive()
                && Lib\Entities\Staff::query()->count() > 0
        ) {
            do_action( 'admin_page_access_denied' );
            wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $params = self::postParameters();
        if ( ! $params['category_id'] ) {
            $params['category_id'] = null;
        }
        if ( ! $params['working_time_limit'] ) {
            $params['working_time_limit'] = null;
        }

        self::$staff->setFields( $params );

        StaffProxy\Shared::preUpdateStaff( self::$staff, $params );
        self::$staff->save();
        StaffProxy\Shared::updateStaff( self::$staff, $params );

        wp_send_json_success( array( 'staff' => self::$staff->getFields() ) );
    }

    /**
     * Get staff services
     */
    public static function getStaffServices()
    {
        $form        = new Forms\StaffServices();
        $staff_id    = self::parameter( 'staff_id' );
        $location_id = self::parameter( 'location_id' );

        $form->load( $staff_id, $location_id );

        $html = self::_getStaffServices( $staff_id, $location_id );
        wp_send_json_success( compact( 'html' ) );
    }

    /**
     * Update staff services.
     */
    public static function staffServicesUpdate()
    {
        $form = new Forms\StaffServices();
        $form->bind( self::postParameters() );
        $form->save();

        StaffProxy\Shared::updateStaffServices( self::postParameters() );

        wp_send_json_success();
    }

    /**
     * Get staff schedule.
     */
    public static function getStaffSchedule()
    {
        $staff_id    = self::parameter( 'staff_id' );
        $location_id = self::parameter( 'location_id' );
        $html        = self::_getStaffSchedule( $staff_id, $location_id );
        wp_send_json_success( compact( 'html' ) );
    }

    /**
     * Update staff holidays.
     */
    public static function staffHolidaysUpdate()
    {
        $interval = self::parameter( 'range', array() );
        $range    = new Lib\Slots\Range( Lib\Slots\DatePoint::fromStr( $interval[0] ), Lib\Slots\DatePoint::fromStr( $interval[1] )->modify( 1 ) );
        if ( self::$staff ) {
            if ( self::parameter( 'holiday' ) == 'true' ) {
                $repeat   = (int) ( self::parameter( 'repeat' ) == 'true' );
                if ( ! $repeat ) {
                    Lib\Entities\Holiday::query( 'h' )
                        ->update()
                        ->set( 'h.repeat_event', 0 )
                        ->where( 'h.staff_id', self::$staff->getId() )
                        ->where( 'h.repeat_event', 1 )
                        ->whereRaw( 'CONVERT(DATE_FORMAT(h.date, \'1%%m%%d\'),UNSIGNED INTEGER) BETWEEN %d AND %d', array( $range->start()->value()->format( '1md' ), $range->end()->value()->format( '1md' ) ) )
                        ->execute();
                }

                $holidays = Lib\Entities\Holiday::query()
                    ->whereBetween( 'date', $range->start()->value()->format( 'Y-m-d' ), $range->end()->value()->format( 'Y-m-d' ) )
                    ->where( 'staff_id', self::$staff->getId() )
                    ->indexBy( 'date' )
                    ->find();
                foreach ( $range->split( DAY_IN_SECONDS ) as $r ) {
                    $day = $r->start()->value()->format( 'Y-m-d' );
                    if ( array_key_exists( $day, $holidays ) ) {
                        $holiday = $holidays[ $day ];
                    } else {
                        $holiday = new Lib\Entities\Holiday();
                    }
                    $holiday
                        ->setDate( $day )
                        ->setRepeatEvent( $repeat )
                        ->setStaffId( self::$staff->getId() )
                        ->save();
                }
            } else {
                Lib\Entities\Holiday::query( 'h' )
                    ->delete()
                    ->where( 'h.staff_id', self::$staff->getId() )
                    ->where( 'h.repeat_event', 1 )
                    ->whereRaw( 'CONVERT(DATE_FORMAT(h.date, \'1%%m%%d\'),UNSIGNED INTEGER) BETWEEN %d AND %d', array( $range->start()->value()->format( '1md' ), $range->end()->value()->format( '1md' ) ) )
                    ->execute();

                Lib\Entities\Holiday::query()
                    ->delete()
                    ->whereBetween( 'date', $range->start()->value()->format( 'Y-m-d' ), $range->end()->value()->format( 'Y-m-d' ) )
                    ->where( 'staff_id', self::$staff->getId() )
                    ->execute();
            }
            // And return refreshed events.
            wp_send_json_success( self::$staff->getHolidays() );
        }
        wp_send_json_error();
    }

    /**
     * Handle staff schedule break.
     */
    public static function staffScheduleHandleBreak()
    {
        $start_time    = self::parameter( 'start_time' );
        $end_time      = self::parameter( 'end_time' );
        $working_start = self::parameter( 'working_start' );
        $working_end   = self::parameter( 'working_end' );
        $break_id      = self::parameter( 'id', 0 );

        if ( Lib\Utils\DateTime::timeToSeconds( $start_time ) >= Lib\Utils\DateTime::timeToSeconds( $end_time ) ) {
            wp_send_json_error( array( 'message' => __( 'The start time must be less than the end one', 'bookly' ), ) );
        }

        $schedule_item = new Lib\Entities\StaffScheduleItem();
        $schedule_item->load( self::parameter( 'ss_id' ) );

        $in_working_time = $working_start <= $start_time && $start_time <= $working_end
            && $working_start <= $end_time && $end_time <= $working_end;
        if ( ! $in_working_time || ! $schedule_item->isBreakIntervalAvailable( $start_time, $end_time, $break_id ) ) {
            wp_send_json_error( array( 'message' => __( 'The requested interval is not available', 'bookly' ), ) );
        }

        $schedule_item_break = new Lib\Entities\ScheduleItemBreak();
        if ( $break_id ) {
            $schedule_item_break->load( $break_id );
        } else {
            $schedule_item_break->setStaffScheduleItemId( $schedule_item->getId() );
        }
        $schedule_item_break
            ->setStartTime( $start_time )
            ->setEndTime( $end_time )
            ->save();
        if ( $schedule_item_break ) {
            $break = new BreakItem( $schedule_item_break->getId(), $schedule_item_break->getStartTime(), $schedule_item_break->getEndTime() );
            wp_send_json_success( array(
                'html'     => $break->render( false ),
                'interval' => $break->getFormatedInterval(),
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error adding the break interval', 'bookly' ), ) );
        }
    }

    /**
     * Delete staff schedule break.
     */
    public static function deleteStaffScheduleBreak()
    {
        $break = new Lib\Entities\ScheduleItemBreak();
        $break->setId( self::parameter( 'id', 0 ) );
        $break->delete();

        wp_send_json_success();
    }

    /**
     * Get list of users available for particular staff.
     *
     * @param integer $staff_id If null then it means new staff
     * @return array
     */
    private static function _getUsersForStaff( $staff_id = null )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        if ( ! is_multisite() ) {
            $query = sprintf(
                'SELECT ID, user_email, display_name FROM ' . $wpdb->users . '
               WHERE ID NOT IN(SELECT DISTINCT IFNULL( wp_user_id, 0 ) FROM ' . Lib\Entities\Staff::getTableName() . ' %s)
               ORDER BY display_name',
                $staff_id !== null
                    ? 'WHERE ' . Lib\Entities\Staff::getTableName() . '.id <> ' . (int) $staff_id
                    : ''
            );
            $users = $wpdb->get_results( $query );
        } else {
            // In Multisite show users only for current blog.
            $query = Lib\Entities\Staff::query( 's' )->select( 'DISTINCT wp_user_id' )->whereNot( 'wp_user_id', null );
            if ( $staff_id != null ) {
                $query->whereNot( 'id', $staff_id );
            }
            $exclude_wp_users = array();
            foreach ( $query->fetchArray() as $staff ) {
                $exclude_wp_users[] = $staff['wp_user_id'];
            }
            $users = array_map(
                function ( \WP_User $wp_user ) {
                    $obj = new \stdClass();
                    $obj->ID = $wp_user->ID;
                    $obj->user_email = $wp_user->data->user_email;
                    $obj->display_name = $wp_user->data->display_name;

                    return $obj;
                },
                get_users( array( 'blog_id' => get_current_blog_id(), 'orderby' => 'display_name', 'exclude' => $exclude_wp_users ) )
            );
        }

        return $users;
    }

    /**
     * @param int      $staff_id
     * @param int|null $location_id
     * @return string
     */
    private static function _getStaffServices( $staff_id, $location_id )
    {
        $form = new Forms\StaffServices();
        $form->load( $staff_id, $location_id );
        $services_data = $form->getServicesData();

        return self::renderTemplate( 'services', compact( 'form', 'services_data', 'staff_id', 'location_id' ), false );
    }

    /**
     * Get staff schedule.
     *
     * @param int      $staff_id
     * @param int|null $location_id
     * @return string|void
     */
    private static function _getStaffSchedule( $staff_id, $location_id )
    {
        $staff = new Lib\Entities\Staff();
        $staff->load( $staff_id );

        $schedule = new ScheduleComponent( 'start_time[{index}]', 'end_time[{index}]' );

        $ss_ids = array();
        foreach ( $staff->getScheduleItems( $location_id ) as $item ) {
            $id = $item->getId();
            $schedule->addHours( $id, $item->getDayIndex(), $item->getStartTime(), $item->getEndTime() );
            $ss_ids[ $id ] = $item->getDayIndex();
        }

        foreach (
            Lib\Entities\ScheduleItemBreak::query()
                ->whereIn( 'staff_schedule_item_id', array_keys( $ss_ids) )
                ->sortBy( 'start_time, end_time' )
                ->fetchArray() as $break
        ) {
            $schedule->addBreak( $break['staff_schedule_item_id'], $break['id'], $break['start_time'], $break['end_time'] );
        }

        return self::renderTemplate( 'schedule', compact( 'schedule', 'staff_id', 'location_id', 'ss_ids' ), false );
    }

    /**
     * Extend parent method to control access on staff member level.
     *
     * @param string $action
     * @return bool
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            self::$staff = new Lib\Entities\Staff();
            if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
                self::$staff = Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->findOne();
                switch ( $action ) {
                    case 'getStaffData':
                    case 'updateStaff':
                        return self::$staff->isLoaded();
                    case 'getStaffSchedule':
                    case 'getStaffServices':
                    case 'staffHolidaysUpdate':
                    case 'staffServicesUpdate':
                        return self::$staff->isLoaded()
                            && ( self::$staff->getId() == self::parameter( 'staff_id' ) );
                    case 'staffScheduleHandleBreak':
                        $res_schedule = new Lib\Entities\StaffScheduleItem();
                        $res_schedule->load( self::parameter( 'ss_id' ) );
                        return self::$staff->isLoaded()
                            && ( self::$staff->getId() == $res_schedule->getStaffId() );
                    case 'deleteStaffScheduleBreak':
                        $break = new Lib\Entities\ScheduleItemBreak();
                        $break->load( self::parameter( 'id' ) );
                        $res_schedule = new Lib\Entities\StaffScheduleItem();
                        $res_schedule->load( $break->getStaffScheduleItemId() );
                        return self::$staff->isLoaded()
                            && ( self::$staff->getId() == $res_schedule->getStaffId() );
                    case 'staffScheduleUpdate':
                        if ( self::hasParameter( 'ssi' ) ) {
                            foreach ( self::parameter( 'ssi' ) as $id => $day_index ) {
                                $res_schedule = new Lib\Entities\StaffScheduleItem();
                                $res_schedule->load( $id );
                                $staff = new Lib\Entities\Staff();
                                $staff->load( $res_schedule->getStaffId() );
                                if ( $staff->getWpUserId() != get_current_user_id() ) {
                                    return false;
                                }
                            }
                        }
                        return true;
                    default:
                        return false;
                }
            } else {
                if ( in_array( $action, array( 'getStaffData', 'updateStaff' ) ) ) {
                    self::$staff->load( self::parameter( 'id' ) );
                } elseif ( in_array( $action, array( 'staffHolidaysUpdate' ) ) ) {
                    self::$staff->load( self::parameter( 'staff_id' ) );
                }
            }

            return true;
        }

        return false;
    }
}