<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Lib\Utils;

foreach ( range( 1, 23 ) as $hours ) {
    $bookly_ntf_processing_interval_values[] = array( $hours, Utils\DateTime::secondsToInterval( $hours * HOUR_IN_SECONDS ) );
}
?>
<form id="bookly-js-general-settings-modal" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'General settings', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <?php self::renderTemplate( '_common_settings', array( 'tail' => '_gen' ) ) ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php Selects::renderSingle( 'bookly_ntf_processing_interval', __( 'Scheduled notifications retry period', 'bookly' ), __( 'Set period of time when system will attempt to deliver notification to user. Notification will be discarded after period expiration.', 'bookly' ), $bookly_ntf_processing_interval_values ) ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php Inputs::renderCsrf() ?>
                <?php Buttons::renderSubmit( null, 'bookly-js-save', __( 'Save settings', 'bookly' ) ) ?>
                <?php Buttons::renderCancel( __( 'Close', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</form>