<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Modules\Customers\Proxy;
/** @var array $datatables */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Customers', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input class="form-control" type="text" id="bookly-filter" placeholder="<?php esc_attr_e( 'Quick search services', 'bookly' ) ?>"/>
                    </div>
                </div>
                <div class="col-md-8 form-row justify-content-end pr-0">
                    <?php Proxy\Pro::renderExportButton() ?>
                    <?php Proxy\Pro::renderImportButton() ?>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success" data-toggle="bookly-modal" data-target="#bookly-customer-dialog"><i class="fas fa-fw fa-plus"></i> <?php esc_html_e( 'New customer', 'bookly' ) ?>…</button>
                    </div>
                    <?php Dialogs\TableSettings\Dialog::renderButton( 'customers' ) ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <table id="bookly-customers-list" class="table table-striped w-100">
                        <thead>
                        <tr>
                            <?php foreach ( $datatables['customers']['settings']['columns'] as $column => $show ) : ?>
                                <?php if ( $show ) : ?>
                                    <th><?php echo $datatables['customers']['titles'][ $column ] ?></th>
                                <?php endif ?>
                            <?php endforeach ?>
                            <th></th>
                            <th width="16"><?php Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-all' ) ) ?></th>
                        </tr>
                        </thead>
                    </table>

                    <div class="form-row justify-content-end mt-3">
                        <div class="col-auto">
                            <button type="button" id="bookly-merge-with" class="btn btn-default" data-toggle="bookly-modal" data-target="#bookly-merge-dialog" disabled="disabled" style="display:none"><i class="fas fa-fw fa-road mr-1"></i><?php esc_html_e( 'Merge with', 'bookly' ) ?>…</button>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="bookly-select-for-merge" class="btn btn-default"><i class="fas fa-fw fa-plus mr-1"></i><?php esc_html_e( 'Select for merge', 'bookly' ) ?>…</button>
                        </div>
                        <div class="col-auto pr-0">
                            <?php Dialogs\Customer\Delete\Dialog::renderDeleteButton() ?>
                        </div>
                    </div>

                    <div id="bookly-merge-list" class="mt-3" style="display:none">
                        <h4><?php esc_html_e( 'Merge list', 'bookly' ) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <?php Proxy\Pro::renderImportDialog() ?>
        <?php Proxy\Pro::renderExportDialog( $datatables['customers']['settings'], $datatables['customers']['titles'] ) ?>
        <?php Dialogs\Customer\Delete\Dialog::render() ?>

        <div id="bookly-merge-dialog" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php esc_html_e( 'Merge customers', 'bookly' ) ?></h5>
                        <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <?php esc_html_e( 'You are about to merge customers from the merge list with the selected one. This will result in losing the merged customers and moving all their appointments to the selected customer. Are you sure you want to continue?', 'bookly' ) ?>
                    </div>
                    <div class="modal-footer">
                        <?php Buttons::render( 'bookly-merge', 'btn-danger', __( 'Merge', 'bookly' ), array(), '<span class="ladda-label"><i class="fas fa-fw fa-road mr-1"></i>{caption}</span>' ) ?>
                        <?php Buttons::renderCancel() ?>
                    </div>
                </div>
            </div>
        </div>

        <div ng-bookly-app="customer" ng-controller="customerCtrl">
            <div customer-dialog=saveCustomer(customer) customer="customer"></div>
            <?php Dialogs\Customer\Edit\Dialog::render() ?>
        </div>
        <?php Dialogs\TableSettings\Dialog::render() ?>
    </div>

</div>