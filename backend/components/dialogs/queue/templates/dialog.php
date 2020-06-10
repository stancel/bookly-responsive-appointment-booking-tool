<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<form id="bookly-queue-modal" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Send notifications', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <div id="bookly-queue"></div>
            </div>
            <div class="modal-footer">
                <?php Inputs::renderCsrf() ?>
                <?php Buttons::render( null, 'bookly-js-send btn-success', __( 'Send', 'bookly' ) ) ?>
                <?php Buttons::render( null, 'bookly-js-cancel btn-default', __( 'Close', 'bookly' ), array( 'data-dismiss' => 'bookly-modal' ) ) ?>
            </div>
        </div>
    </div>
</form>
<div id="bookly-notification-template" class="collapse">
    <div class="bookly-js-notification-queue">
        <div class="custom-control custom-checkbox">
            <input class="custom-control-input" id="bookly-nq-{{index}}" type="checkbox" data-index="{{index}}" checked/><label class="custom-control-label" for="bookly-nq-{{index}}"><i class="fa-fw {{icon}} mr-1"></i><b>{{recipient}}</b> ({{address}})</label><br/>
            {{description}}
        </div>
    </div>
</div>