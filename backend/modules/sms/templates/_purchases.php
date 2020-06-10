<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils;
use Bookly\Backend\Components\Dialogs;
/** @var $datatables */
?>
<div class="row justify-content-between">
    <div class="col-md-4">
        <div class="form-group">
            <button type="button" id="purchases_date_range" class="btn btn-default text-truncate text-left" data-date="<?php echo date( 'Y-m-d', strtotime( '-30 days' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>">
                <i class="far fa-calendar-alt mr-1"></i>
                <input type="hidden" name="form-purchases">
                <span>
                    <?php echo Utils\DateTime::formatDate( '-30 days' ) ?> - <?php echo Utils\DateTime::formatDate( 'today' ) ?>
                </span>
            </button>
        </div>
    </div>
    <div class="col-md-4 form-row justify-content-end">
        <?php Dialogs\TableSettings\Dialog::renderButton( 'sms_purchases', 'BooklyL10n', esc_attr( add_query_arg( 'tab', 'purchases' ) ) ) ?>
    </div>
</div>

<table id="bookly-purchases" class="table table-striped w-100">
    <thead>
    <tr>
        <?php foreach ( $datatables['sms_purchases']['settings']['columns'] as $column => $show ) : ?>
            <?php if ( $show ) : ?>
                <th><?php echo $datatables['sms_purchases']['titles'][ $column ] ?></th>
            <?php endif ?>
        <?php endforeach ?>
        <th></th>
    </tr>
    </thead>
</table>