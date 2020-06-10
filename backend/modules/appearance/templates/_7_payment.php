<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Appearance\Codes;
use Bookly\Backend\Components\Appearance\Editable;
use Bookly\Backend\Modules\Appearance\Proxy;
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <?php Proxy\Coupons::renderCouponBlock() ?>
    <?php Proxy\DepositPayments::renderAppearance() ?>

    <div class="bookly-payment-nav">
        <div class="bookly-box bookly-js-payment-single-app">
            <?php Editable::renderText( 'bookly_l10n_info_payment_step_single_app', Codes::getHtml( 7 ) ) ?>
        </div>
        <?php Proxy\Pro::renderBookingStatesText() ?>
        <div class="bookly-js-payment-gateways">
            <div class="bookly-box bookly-list ">
                <label>
                    <input type="radio" name="payment" checked="checked"/>
                    <?php Editable::renderString( array( 'bookly_l10n_label_pay_locally', ), __( 'Local', 'bookly' ) ) ?>
                </label>
            </div>

            <?php Proxy\Pro::renderPayPalPaymentOption() ?>
            <?php Proxy\Shared::renderPaymentGatewaySelector() ?>
        </div>
    </div>

    <?php Proxy\RecurringAppointments::renderInfoMessage() ?>

    <div class="bookly-box bookly-nav-steps">
        <div class="bookly-back-step bookly-js-back-step bookly-btn">
            <?php Editable::renderString( array( 'bookly_l10n_button_back' ) ) ?>
        </div>
        <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
            <div class="bookly-next-step bookly-js-next-step bookly-btn">
                <?php Editable::renderString( array( 'bookly_l10n_step_payment_button_next' ) ) ?>
            </div>
        </div>
    </div>
</div>