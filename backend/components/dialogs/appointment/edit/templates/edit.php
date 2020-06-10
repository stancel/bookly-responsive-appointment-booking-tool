<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy;
use Bookly\Backend\Components\Dialogs\Appointment\AttachPayment\Proxy as AttachPaymentProxy;
use Bookly\Lib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\CustomerAppointment;
?>
<div ng-bookly-app="appointmentDialog" ng-controller="appointmentDialogCtrl">
    <div id=bookly-appointment-dialog class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <h5 class="modal-title">{{ form.screen == 'queue' ? form.titles.queue : form.title }}</h5>
                        <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div ng-show=loading class="modal-body">
                        <div class="bookly-loading"></div>
                    </div>
                    <div ng-hide="loading || form.screen != 'main'" class="modal-body">
                        <div class=form-group>
                            <label for="bookly-provider"><?php esc_html_e( 'Provider', 'bookly' ) ?></label>
                            <select id="bookly-provider" class="form-control custom-select" ng-model="form.staff" ng-options="s.full_name + (form.staff_any == s ? ' (' + dataSource.l10n.staff_any + ')' : '') group by s.category for s in dataSource.data.staff | filter:filterStaff" ng-change="onStaffChange()"></select>
                        </div>

                        <div class=form-group>
                            <label for="bookly-service"><?php esc_html_e( 'Service', 'bookly' ) ?></label>
                            <select id="bookly-service" class="form-control custom-select" ng-model="form.service"
                                    ng-options="s.title group by s.category for s in form.staff.services" ng-change="onServiceChange()">
                                <option value=""><?php esc_html_e( '-- Select a service --', 'bookly' ) ?></option>
                            </select>
                            <p class="text-danger" my-slide-up="errors.service_required">
                                <?php esc_html_e( 'Please select a service', 'bookly' ) ?>
                            </p>
                        </div>

                        <?php Proxy\Pro::renderCustomServiceFields() ?>
                        <?php Proxy\Pro::renderOnlineMeetingField() ?>

                        <?php if ( Config::locationsActive() ): ?>
                            <div class="form-group">
                                <label for="bookly-appointment-location"><?php esc_html_e( 'Location', 'bookly' ) ?></label>
                                <select id="bookly-appointment-location" class="form-control custom-select" ng-model="form.location"
                                        ng-options="l.name for l in form.staff.locations" ng-change="onLocationChange()">
                                    <option value=""></option>
                                </select>
                            </div>
                        <?php endif ?>

                        <div class=form-group>
                            <?php Proxy\Tasks::renderSkipDate() ?>
                            <div ng-hide="form.skip_date">
                                <div class="form-row">
                                    <div class="col-sm-4">
                                        <label for="bookly-date"><?php esc_html_e( 'Date', 'bookly' ) ?></label>
                                        <input date-range-picker id="bookly-date" class="form-control" type="text" ng-model="form.date" options="{parentEl:'#bookly-appointment-dialog',singleDatePicker:true,showDropdowns:true, locale:datePickerOptions}" autocomplete="off">
                                    </div>
                                    <div class="col-sm-8">
                                        <div ng-hide="form.service.duration >= 86400 && form.service.units_max == 1">
                                            <label for="bookly-period"><?php esc_html_e( 'Period', 'bookly' ) ?></label>
                                            <div class="row">
                                                <div class="col">
                                                    <select id="bookly-period" class="form-control custom-select" ng-model=form.start_time
                                                            ng-options="t.title for t in dataSource.getDataForStartTime()"
                                                            ng-change=onStartTimeChange()></select>
                                                </div>
                                                <div class="mt-2">
                                                    <?php esc_html_e( 'to', 'bookly' ) ?>
                                                </div>
                                                <div class="col">
                                                    <select class="form-control custom-select" ng-model=form.end_time
                                                            ng-options="t.title for t in form.end_time_data"
                                                            ng-change=onEndTimeChange()></select>
                                                </div>
                                            </div>
                                            <p class="text-success" my-slide-up=errors.date_interval_warning id=date_interval_warning_msg>
                                                <?php esc_html_e( 'Selected period doesn\'t match service duration', 'bookly' ) ?>
                                            </p>
                                            <p class="text-success" my-slide-up="errors.time_interval" ng-bind="errors.time_interval"></p>
                                        </div>
                                    </div>
                                    <div class="text-success col-sm-12" my-slide-up=errors.date_interval_not_available id=date_interval_not_available_msg>
                                        <?php esc_html_e( 'The selected period is occupied by another appointment', 'bookly' ) ?>
                                    </div>
                                </div>
                                <p class="text-success" my-slide-up=errors.interval_not_in_staff_schedule id=interval_not_in_staff_schedule_msg>
                                    <?php esc_html_e( 'Selected period doesn\'t match provider\'s schedule', 'bookly' ) ?>
                                </p>
                                <p class="text-success" my-slide-up=errors.interval_not_in_service_schedule id=interval_not_in_service_schedule_msg>
                                    <?php esc_html_e( 'Selected period doesn\'t match service schedule', 'bookly' ) ?>
                                </p>
                                <p class="text-success" my-slide-up=errors.staff_reaches_working_time_limit id=staff_reaches_working_time_limit_msg>
                                    <?php is_admin()
                                        ? esc_html_e( 'Booking exceeds the working hours limit for staff member', 'bookly' )
                                        : esc_html_e( 'Booking exceeds your working hours limit', 'bookly' ) ?>
                                </p>

                                <?php Proxy\RecurringAppointments::renderSubForm() ?>
                            </div>
                        </div>
                        <div class=form-group>
                            <label for="bookly-select2"><?php esc_html_e( 'Customers', 'bookly' ) ?>
                                <span ng-show="form.service && form.service.id" title="<?php esc_attr_e( 'Selected / maximum', 'bookly' ) ?>">
                                ({{dataSource.getTotalNumberOfPersons()}}/{{form.service.capacity_max}})
                                </span>
                            </label>

                            <span ng-show="form.customers.length > 5" ng-click="form.expand_customers_list = !form.expand_customers_list" role="button">
                                <i class="far fa-fw" ng-class="{'fa-angle-down':!form.expand_customers_list, 'fa-angle-up':form.expand_customers_list}"></i>
                            </span>
                            <p class="text-success" ng-show=form.service my-slide-up="form.service.capacity_min > 1 && form.service.capacity_min > dataSource.getTotalNumberOfPersons()">
                                <?php esc_html_e( 'Minimum capacity', 'bookly' ) ?>: {{form.service.capacity_min}}
                            </p>
                            <ul class="list-unstyled pl-0 bookly-hide-empty mr-3" ng-class="{'my-0':form.customers.length == 0}">
                                <li class="row mb-1" ng-repeat="customer in form.customers" ng-hide="$index > 4 && !form.expand_customers_list">
                                    <div class="col mt-1">
                                        <a ng-click="editCustomerDetails(customer)" title="<?php esc_attr_e( 'Edit booking details', 'bookly' ) ?>" href>{{customer.name}}</a>
                                    </div>
                                    <div class="ml-auto">
                                        <?php Proxy\Shared::renderAppointmentDialogCustomersList() ?>
                                        <span class="dropdown">
                                            <button type="button" class="btn btn-default px-2 py-1 dropdown-toggle" data-toggle="dropdown" popover="<?php esc_attr_e( 'Status', 'bookly' ) ?>: {{statusToString(customer.status)}}" >
                                                <span ng-class="{'fa-fw': true, 'far fa-clock': customer.status == 'pending', 'fas fa-sm fa-check': customer.status == 'approved', 'fas fa-times': customer.status == 'cancelled', 'fas fa-ban': customer.status == 'rejected', 'fas fa-list-ol': customer.status == 'waitlisted', 'far fa-check-square': customer.status == 'done', 'far fa-times-circle': 0<?php foreach ( Lib\Proxy\CustomStatuses::prepareBusyStatuses( array() ) as $status ): ?> || customer.status == '<?php echo $status ?>'<?php endforeach ?>, 'far fa-check-circle': 0<?php foreach ( Lib\Proxy\CustomStatuses::prepareFreeStatuses( array() ) as $status ): ?> || customer.status == '<?php echo $status ?>'<?php endforeach ?>}"></span>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href class="dropdown-item pl-3" ng-click="customer.status = 'pending'">
                                                    <span class="far fa-fw fa-clock mr-2"></span><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_PENDING ) ) ?>
                                                </a>
                                                <a href class="dropdown-item pl-3" ng-click="customer.status = 'approved'">
                                                    <span class="fas fa-fw fa-check mr-2"></span><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_APPROVED ) ) ?>
                                                </a>
                                                <a href class="dropdown-item pl-3" ng-click="customer.status = 'cancelled'">
                                                    <span class="fas fa-fw fa-times mr-2"></span><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_CANCELLED ) ) ?>
                                                </a>
                                                <a href class="dropdown-item pl-3" ng-click="customer.status = 'rejected'">
                                                    <span class="fas fa-fw fa-ban mr-2"></span><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_REJECTED ) ) ?>
                                                </a>
                                                <?php if ( Config::waitingListActive() ) : ?>
                                                    <a href class="dropdown-item pl-3" ng-click="customer.status = 'waitlisted'">
                                                        <span class="fas fa-fw fa-list-ol mr-2"></span><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_WAITLISTED ) ) ?>
                                                    </a>
                                                <?php endif ?>
                                                <?php if ( Config::tasksActive() ) : ?>
                                                    <a href class="dropdown-item pl-3" ng-click="customer.status = 'done'">
                                                        <span class="far fa-fw fa-check-square mr-2"></span><?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_DONE ) ) ?>
                                                    </a>
                                                <?php endif ?>
                                                <?php foreach ( (array) Lib\Proxy\CustomStatuses::getAll() as $status ) : ?>
                                                    <a href class="dropdown-item pl-3" ng-click="customer.status = '<?php echo $status->getSlug() ?>'">
                                                        <span class="far fa-fw <?php if ( $status->getBusy() ): ?>fa-times-circle<?php else: ?>fa-check-circle<?php endif ?> mr-2"></span><?php echo esc_html( $status->getName() ) ?>
                                                    </a>
                                                <?php endforeach ?>
                                            </div>
                                        </span>
                                        <button type="button" class="btn btn-default px-2 py-1" data-action="show-payment" data-payment_id="{{customer.payment_id}}" ng-show="customer.payment_id || customer.payment_create" popover="<?php esc_attr_e( 'Payment', 'bookly' ) ?>: {{customer.payment_title}}" ng-disabled="customer.payment_create">
                                            <span ng-class="{'bookly-js-toggle-popover fas fa-fw': true, 'fa-dollar-sign': customer.payment_type == 'full', 'fa-hand-holding-usd': customer.payment_type == 'partial'}"></span>
                                        </button>

                                        <?php Proxy\Pro::renderAttachPaymentButton() ?>

                                        <span class="btn btn-default px-2 py-1 disabled" style="opacity:1;cursor:default;"><i class="far fa-fw fa-user"></i>&times;{{customer.number_of_persons}}</span>
                                        <?php if ( Config::packagesActive() ) : ?>
                                        <button type="button" class="btn btn-default px-2 py-1" ng-click="editPackageSchedule(customer)" ng-show="customer.package_id" popover="<?php esc_attr_e( 'Package schedule', 'bookly' ) ?>">
                                            <span class="far fa-fw fa-calendar-alt"></span>
                                        </button>
                                        <?php endif ?>
                                        <?php if ( Config::recurringAppointmentsActive() ) : ?>
                                        <button type="button" class="btn btn-default px-2 py-1" ng-click="schViewSeries(customer)" ng-show="customer.series_id" popover="<?php esc_attr_e( 'View series', 'bookly' ) ?>">
                                            <span class="fas fa-fw fa-link"></span>
                                        </button>
                                        <?php endif ?>
                                        <a ng-click="removeCustomer(customer)" class="far fa-fw fa-trash-alt text-danger" href="#"
                                           popover="<?php esc_attr_e( 'Remove customer', 'bookly' ) ?>"></a>
                                    </div>
                                </li>
                            </ul>
                            <span class="btn btn-default" ng-show="form.customers.length > 5 && !form.expand_customers_list" ng-click="form.expand_customers_list = !form.expand_customers_list" style="width: 100%; line-height: 0; padding-top: 0; padding-bottom: 8px; margin-bottom: 10px;" role="button">...</span>
                            <div <?php if ( ! Config::waitingListActive() ): ?>ng-show="!form.service || dataSource.getTotalNumberOfNotCancelledPersons() < form.service.capacity_max"<?php endif ?>>
                                <div class="form-group">
                                    <div class="input-group">
                                        <select id="bookly-appointment-dialog-select2" multiple data-placeholder="<?php esc_attr_e( '-- Search customers --', 'bookly' ) ?>"
                                                class="form-control"
                                        >
                                            <option ng-repeat="customer in dataSource.data.customers" value="{{customer.id}}">{{customer.name}}</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" ng-click="openNewCustomerDialog()">
                                                <i class="fas fa-fw fa-plus"></i>
                                                <?php esc_html_e( 'New customer', 'bookly' ) ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-danger" my-slide-up="errors.overflow_capacity" ng-bind="errors.overflow_capacity"></p>
                            <p class="text-success" my-slide-up="errors.customers_appointments_limit" ng-repeat="customer_error in errors.customers_appointments_limit">
                                {{customer_error}}
                            </p>
                        </div>

                        <div class=form-group>
                            <label for="bookly-internal-note"><?php esc_html_e( 'Internal note', 'bookly' ) ?></label>
                            <textarea class="form-control" ng-model=form.internal_note id="bookly-internal-note"></textarea>
                        </div>
                    </div>
                    <div ng-hide="loading || form.screen != 'queue'" class="modal-body">
                        <div class="form-group" ng-hide="!form.queue.all.length || !form.queue.changed_status.length">
                            <label for="bookly-notification"><?php esc_html_e( 'Send notifications', 'bookly' ) ?></label>
                            <?php Inputs::renderRadio( __( 'Send if new or status changed', 'bookly' ), 'changed_status', null, array( 'name' => 'queue_type', 'ng-model' => 'form.queue_type' ) ) ?>
                            <?php Inputs::renderRadio( __( 'Send as for new', 'bookly' ), 'all', null, array( 'name' => 'queue_type', 'ng-model' => 'form.queue_type' ) ) ?>
                            <small class="help-block"><?php esc_html_e( 'If you have added a new customer to this appointment or changed the appointment status for an existing customer, and for these records you want the corresponding email or SMS notifications to be sent to their recipients, select the "Send if new or status changed" option before clicking Send. You can also send notifications as if all customers were added as new by selecting "Send as for new".', 'bookly' ) ?></small>
                        </div>
                        <div ng-repeat="(key, value) in form.queue.all">
                            <div ng-hide="form.queue_type == 'changed_status'">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" id="bookly-ch-all-{{key}}" type="checkbox" ng-model=value.checked ng-true-value="1" ng-false-value="0" ng-init="value.checked=1">
                                    <label class="custom-control-label" for="bookly-ch-all-{{key}}"><i class="fa-fw" ng-class="{'fas fa-sms':value.gateway == 'sms', 'far fa-envelope':value.gateway != 'sms'}"></i> <b>{{value.data.name}}</b> ({{value.address}})<br/>
                                        {{ value.name }}</label>
                                </div>
                            </div>
                        </div>
                        <div ng-repeat="(key, value) in form.queue.changed_status">
                            <div ng-hide="form.queue_type != 'changed_status'">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" id="bookly-ch-sc-{{key}}" type="checkbox" ng-model=value.checked ng-true-value="1" ng-false-value="0" ng-init="value.checked=1"/>
                                    <label class="custom-control-label" for="bookly-ch-sc-{{key}}"><i class="fa-fw" ng-class="{'fas fa-sms':value.gateway == 'sms', 'far fa-envelope':value.gateway != 'sms'}"></i> <b>{{value.data.name}}</b> ({{value.address}})<br/>
                                        {{ value.name }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php Proxy\RecurringAppointments::renderSchedule() ?>
                    <div ng-hide="loading || form.screen != 'main'" class="modal-body" style="margin-top: -30px;">
                        <?php Inputs::renderCheckBox( __( 'Send notifications', 'bookly' ), null, null, array(
                             'ng-model' => 'form.notification',
                             'ng-true-value'  => '1',
                             'ng-false-value' => '0',
                             'ng-init'        => 'form.notification=' . ( get_user_meta( get_current_user_id(), 'bookly_appointment_form_send_notifications', true ) ?: 0 ) )
                        ) ?>
                    </div>
                    <div class="modal-footer">
                        <div ng-hide=loading>
                            <?php Proxy\Shared::renderAppointmentDialogFooter() ?>
                            <?php Buttons::render( null, 'btn-success', __( 'Save', 'bookly' ),
                                array(
                                    'ng-hide'        => 'form.screen == \'queue\' || (form.repeat.enabled && !form.skip_date && form.screen == \'main\')',
                                    'ng-disabled'    => '!form.skip_date && form.repeat.enabled && schIsScheduleEmpty() || (!form.date && !form.skip_date)',
                                    'formnovalidate' => '',
                                    'ng-click'       => 'processForm()',
                                ) ) ?>
                            <?php Buttons::render( null, 'bookly-js-queue-send btn-success', __( 'Send', 'bookly' ), array( 'ng-show' => 'form.screen == \'queue\'', 'ng-click' => 'processForm()' ) ) ?>
                            <?php Buttons::renderCancel( null, array( 'ng-click' => 'closeDialog()' ) ) ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div customer-dialog=createCustomer(customer)></div>
    <div payment-details-dialog="callbackPayment(payment_action, payment_id, payment_title, customer_id, customer_index, payment_type)"></div>

    <?php Dialogs\Appointment\CustomerDetails\Dialog::render() ?>
    <?php AttachPaymentProxy\Pro::renderAttachPaymentDialog() ?>
    <?php Dialogs\Customer\Edit\Dialog::render() ?>
    <?php Dialogs\Payment\Dialog::render() ?>
</div>