<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Modules\Appointments\Proxy;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
/** @var array $datatables */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Appointments', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-row justify-content-end">
                <?php Proxy\Pro::renderExportButton() ?>
                <?php Proxy\Pro::renderPrintButton() ?>
                <div class="col-12 col-sm-auto">
                    <?php Controls\Buttons::render( 'bookly-new-appointment', 'btn-success w-100 mb-3', __( 'New appointment', 'bookly' ), array(), '<i class="fas fa-fw fa-plus"></i> {caption}…' ) ?>
                </div>
                <?php Dialogs\TableSettings\Dialog::renderButton( 'appointments' ) ?>
            </div>
            <div class="form-row">
                <div class="col-md-1">
                    <div class="form-group">
                        <input class="form-control" type="text" id="bookly-filter-id" placeholder="<?php esc_attr_e( 'No.', 'bookly' ) ?>"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-default w-100 mb-3 text-truncate text-left" id="bookly-filter-date" data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>
                            <?php echo DateTime::formatDate( 'first day of this month' ) ?> - <?php echo DateTime::formatDate( 'last day of this month' ) ?>
                        </span>
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-default w-100 mb-3 text-truncate text-left" id="bookly-filter-creation-date" data-date="any">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>
                            <?php esc_html_e( 'Created at any time', 'bookly' ) ?>
                        </span>
                    </button>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select class="form-control bookly-js-select" id="bookly-filter-staff" data-placeholder="<?php echo esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ) ?>">
                            <?php foreach ( $staff_members as $staff ) : ?>
                                <option value="<?php echo $staff['id'] ?>"><?php echo esc_html( $staff['full_name'] ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select class="form-control <?php echo $customers === false ? 'bookly-js-select-ajax' : 'bookly-js-select' ?>" id="bookly-filter-customer"
                                data-placeholder="<?php esc_attr_e( 'Customer', 'bookly' ) ?>" <?php echo $customers === false ? 'data-ajax--action' : 'data-action' ?>="bookly_get_customers_list">
                        <?php if ( $customers !== false ) : ?>
                            <?php foreach ( $customers as $customer_id => $customer ) : ?>
                                <option value="<?php echo $customer_id ?>" data-search='<?php echo json_encode( array_values( $customer ) ) ?>'><?php echo esc_html( $customer['full_name'] ) ?></option>
                            <?php endforeach ?>
                        <?php endif ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select class="form-control bookly-js-select" id="bookly-filter-service" data-placeholder="<?php echo esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_service' ) ) ?>">
                            <option value="0"><?php esc_html_e( 'Custom', 'bookly' ) ?></option>
                            <?php foreach ( $services as $service ) : ?>
                                <option value="<?php echo $service['id'] ?>"><?php echo esc_html( $service['title'] ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <select class="form-control bookly-js-select" id="bookly-filter-status" data-placeholder="<?php esc_attr_e( 'Status', 'bookly' ) ?>">
                            <?php foreach ( CustomerAppointment::getStatuses() as $status ): ?>
                                <option value="<?php echo $status ?>"><?php echo esc_html( CustomerAppointment::statusToString( $status ) ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
            <table id="bookly-appointments-list" class="table table-striped w-100">
                <thead>
                <tr>
                    <?php foreach ( $datatables['appointments']['settings']['columns'] as $column => $show ) : ?>
                        <?php if ( $show ) : ?>
                            <th><?php echo $datatables['appointments']['titles'][ $column ] ?></th>
                        <?php endif ?>
                    <?php endforeach ?>
                    <th></th>
                    <th width="16"><?php Controls\Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-all' ) ) ?></th>
                </tr>
                </thead>
            </table>

            <div class="text-right mt-3">
                <?php Controls\Buttons::renderDelete( null, null, null, array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-delete-dialog' ) ) ?>
            </div>
        </div>
    </div>

    <?php Proxy\Pro::renderExportDialog( $datatables['appointments'] ) ?>
    <?php Proxy\Pro::renderPrintDialog( $datatables['appointments'] ) ?>

    <?php Dialogs\Appointment\Delete\Dialog::render() ?>
    <?php Dialogs\TableSettings\Dialog::render() ?>
    <?php Dialogs\Appointment\Edit\Dialog::render() ?>
    <?php Dialogs\Queue\Dialog::render() ?>
    <?php Proxy\Shared::renderAddOnsComponents() ?>
</div>
