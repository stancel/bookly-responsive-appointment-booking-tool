<?php
namespace Bookly\Lib\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Lib\Proxy
 *
 * @method static array  adjustMinAndMaxTimes( array $times ) Prepare time_from & time_to for UserBookingData.
 * @method static Lib\CartInfo applyGateway( Lib\CartInfo $cart_info, string $gateway ) Set gateway.
 * @method static string buildOnlineMeetingUrl( string $default, Lib\Entities\Appointment $appointment ) Build online meeting URL for given appointment.
 * @method static void   deleteCustomerAppointment( Lib\Entities\CustomerAppointment $ca ) Deleting customer appointment
 * @method static void   doDailyRoutine() Execute daily routines.
 * @method static void   doHourlyRoutine() Execute hourly routines.
 * @method static array  getOutdatedUnpaidPayments( array $payments ) Get list of outdated unpaid payments in format [ 'id' => 'details' ].
 * @method static array  handleRequestAction( string $bookly_action ) Handle requests with given action.
 * @method static array  prepareCaSeSt( array $result ) Prepare Categories Services Staff data
 * @method static Lib\Query prepareCaSeStQuery( Lib\Query $query ) Prepare CaSeSt query
 * @method static array  prepareCategoryServiceStaffLocation( array $location_data, array $row ) Prepare Category Service Staff Location data by row
 * @method static array  prepareCategoryService( array $result, array $row ) Prepare Category Service data by row
 * @method static array  prepareNotificationTitles( array $titles ) Prepare notification titles.
 * @method static array  prepareNotificationTypes( array $types, string $gateway ) Prepare notification type IDs.
 * @method static array  preparePaymentDetails( array $details, Lib\DataHolders\Booking\Order $order, Lib\CartInfo $cart_info ) Add info about payment
 * @method static Lib\Query prepareStaffServiceQuery( Lib\Query $query ) Prepare StaffService query for Finder.
 * @method static array  prepareTableColumns( array $columns, $table ) Prepare table columns for given table.
 * @method static string prepareStatement( string $value, string $statement, string $table ) Prepare default value for sql statement.
 * @method static void   renderAdminNotices( bool $bookly_page ) Render admin notices from add-ons.
 * @method static bool   showPaymentSpecificPrices( bool $show ) Whether to show specific price for each payment system.
 * @method static array  syncOnlineMeeting( array $errors, Lib\Entities\Appointment $appointment, Lib\Entities\Service $service ) Synchronize online meeting data with appointment.
 */
abstract class Shared extends Lib\Base\Proxy
{
}
