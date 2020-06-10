<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components;
use Bookly\Backend\Modules as Backend;
use Bookly\Backend\Modules\Calendar\Proxy;
/**
 * @var Bookly\Lib\Entities\Staff[] $staff_members
 * @var array $staff_dropdown_data
 */
?>
<style>
    .fc-slats tr { height: <?php echo max( 21, (int) ( 0.43 * get_option( 'bookly_gen_time_slot_length' ) ) ) ?>px; }
    .fc-time-grid-event.fc-short .fc-time::after { content: '' !important; }
</style>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Calendar', 'bookly' ) ?></h4>
        <?php if ( Common::isCurrentUserSupervisor() ) : ?>
            <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
        <?php endif ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-row justify-content-center justify-content-md-end">
                <?php if ( $staff_members ) : ?>
                    <ul class="col-auto nav nav-pills bookly-js-calendar-tabs">
                        <?php if ( Common::isCurrentUserSupervisor() ) : ?>
                            <li class="nav-item mr-2 mb-2">
                                <a class="nav-link" href="#" data-staff_id="0"><?php esc_html_e( 'All', 'bookly' ) ?></a>
                            </li>
                        <?php endif ?>
                        <?php foreach ( $staff_members as $staff ) : ?>
                            <li class="nav-item mr-2 mb-2" style="display: none">
                                <a class="nav-link" href="#" data-staff_id="<?php echo $staff->getId() ?>"><?php echo esc_html( $staff->getFullName() ) ?></a>
                            </li>
                        <?php endforeach ?>
                    </ul>
                    <div class="col-auto col-md"></div>
                    <?php Proxy\OutlookCalendar::renderSyncButton( $staff_members ) ?>
                    <?php Proxy\AdvancedGoogleCalendar::renderSyncButton( $staff_members ) ?>
                    <?php Proxy\Locations::renderCalendarLocationFilter() ?>
                    <?php if ( Common::isCurrentUserSupervisor() ) : ?>
                        <div class="col-auto mb-2">
                            <ul id="bookly-js-staff-filter"
                                data-align="right"
                                data-txt-select-all="<?php esc_attr_e( 'All staff', 'bookly' ) ?>"
                                data-txt-all-selected="<?php esc_attr_e( 'All staff', 'bookly' ) ?>"
                                data-txt-nothing-selected="<?php esc_attr_e( 'No staff selected', 'bookly' ) ?>"
                            >
                                <?php foreach ( $staff_dropdown_data as $category_id => $category ): ?>
                                    <li<?php if ( ! $category_id ) : ?> data-flatten-if-single<?php endif ?>><?php echo esc_html( $category['name'] ) ?>
                                        <ul>
                                            <?php foreach ( $category['items'] as $staff ) : ?>
                                                <li data-value="<?php echo $staff['id'] ?>">
                                                    <?php echo esc_html( $staff['full_name'] ) ?>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>
                <?php endif ?>
            </div>
            <div class="mt-3 position-relative">
                <?php if ( $staff_members ) : ?>
                    <div class="bookly-fc-loading" style="display: none">
                        <div class="bookly-fc-loading-icon"></div>
                    </div>
                    <div class="bookly-js-calendar"></div>
                    <?php Components\Dialogs\Appointment\Edit\Dialog::render() ?>
                    <?php Proxy\Shared::renderAddOnsComponents() ?>
                <?php elseif( Bookly\Lib\Config::proActive() ) : ?>
                    <?php Components\Notices\Proxy\Pro::renderWelcome() ?>
                <?php else : ?>
                    <div class="m-3">
                        <div class="h1"><?php esc_html_e( 'Welcome to Bookly and thank you for your choice!', 'bookly' ) ?></div>
                        <h4><?php esc_html_e( 'Bookly will simplify the booking process for your customers. This plugin creates another touchpoint to convert your visitors into customers. With Bookly your clients can see your availability, pick the services you provide, book them online and much more.', 'bookly' ) ?></h4>
                        <p><?php esc_html_e( 'To start using Bookly, you need to set up the services you provide and specify the staff members who will provide those services.', 'bookly' ) ?></p>
                        <ol>
                            <li><?php esc_html_e( 'Add a staff member (you can add only one service provider with a free version of Bookly).', 'bookly' ) ?></li>
                            <li><?php esc_html_e( 'Add services you provide (up to five with a free version of Bookly) and assign them to a staff member.', 'bookly' ) ?></li>
                            <li><?php esc_html_e( 'Go to Posts/Pages and click on the “Add Bookly booking form” button in the page editor to publish the booking form on your website.', 'bookly' ) ?></li>
                        </ol>
                        <p><?php printf( __( 'Bookly can boost your sales and scale together with your business. Get more features and remove the limits by upgrading to the paid version with the <a href="%s" target="_blank">Bookly Pro add-on</a>, which allows you to use a vast number of additional features and settings for booking services, install other add-ons for Bookly, and includes six months of customer support.', 'bookly' ), Common::prepareUrlReferrers( 'https://codecanyon.net/item/bookly/7226091?ref=ladela', 'welcome' ) ) ?></p>
                        <hr>
                        <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\Staff\Ajax::pageSlug() ) ?>">
                            <?php esc_html_e( 'Add Staff Members', 'bookly' ) ?>
                        </a>
                        <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\Services\Ajax::pageSlug() ) ?>">
                            <?php esc_html_e( 'Add Services', 'bookly' ) ?>
                        </a>
                        <a class="btn btn-success" href="<?php echo Common::prepareUrlReferrers( 'https://codecanyon.net/item/bookly/7226091?ref=ladela', 'welcome' ) ?>" target="_blank">
                            <?php esc_html_e( 'Try Bookly Pro add-on', 'bookly' ) ?>
                        </a>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <?php Components\Dialogs\Appointment\Delete\Dialog::render() ?>
    <?php Components\Dialogs\Queue\Dialog::render() ?>
</div>