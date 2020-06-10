<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<script type="text/ng-template" id="bookly-payment-details-dialog.tpl">
<div class="bookly-modal bookly-fade" id="bookly-payment-details-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Payment', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="bookly-loading"></div>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCancel( __( 'Close', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</div>
</script>