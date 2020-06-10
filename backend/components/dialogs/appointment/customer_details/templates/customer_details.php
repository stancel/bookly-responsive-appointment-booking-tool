<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Dialogs\Appointment\CustomerDetails\Proxy;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Config;
?>
<div id="bookly-customer-details-dialog" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Edit booking details', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-hidden="true" aria-label="Close">×</button>
            </div>
            <form ng-hide=loading style="z-index: 1050">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bookly-appointment-status"><?php esc_html_e( 'Status', 'bookly' ) ?></label>
                        <select class="bookly-custom-field form-control custom-select" id="bookly-appointment-status">
                            <?php foreach ( CustomerAppointment::getStatuses() as $status ): ?>
                                <option value="<?php echo $status ?>"><?php echo esc_html( CustomerAppointment::statusToString( $status ) ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="form-group" <?php if ( ! Config::groupBookingActive() ) echo ' style="display:none"' ?>>
                        <label for="bookly-number-of-persons"><?php esc_html_e( 'Number of persons', 'bookly' ) ?></label>
                        <select class="bookly-custom-field form-control custom-select" id="bookly-number-of-persons"></select>
                    </div>
                    <?php Proxy\Pro::renderTimeZoneSwitcher() ?>
                    <?php if ( Config::showNotes() ): ?>
                        <div class="form-group">
                            <label for="bookly-appointment-notes"><?php echo Common::getTranslatedOption( 'bookly_l10n_label_notes' ) ?></label>
                            <textarea class="bookly-custom-field form-control" id="bookly-appointment-notes"></textarea>
                        </div>
                    <?php endif ?>

                    <?php Proxy\Shared::renderDetails() ?>

                </div>
                <div class="modal-footer">
                    <?php Buttons::render( null, 'btn-success', __( 'Apply', 'bookly' ), array( 'ng-click' => 'saveCustomFields()' ) ) ?>
                    <?php Buttons::renderCancel() ?>
                </div>
            </form>
        </div>
    </div>
</div>