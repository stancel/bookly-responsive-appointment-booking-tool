<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<p class="alert alert-info">
    <?php esc_html_e( 'We will only charge your PayPal account when your balance falls below $10.', 'bookly' ) ?>
</p>

<div class="form-group">
    <label for="bookly-recharge-amount"><?php esc_html_e( 'Amount', 'bookly' ) ?></label>
    <select id="bookly-recharge-amount" class="form-control custom-select"<?php disabled( $sms->autoRechargeEnabled() ) ?>>
        <?php foreach ( array( 10, 25, 50, 100 ) as $amount ) : ?>
            <?php printf( '<option value="%1$s" %2$s>$%1$s</option>', $amount, selected( $sms->getAutoRechargeAmount() == $amount, true, false ) ) ?>
        <?php endforeach ?>
    </select>
</div>

<div class="text-right mt-3">
    <?php Buttons::render( 'bookly-auto-recharge-init', 'btn-success', __( 'Enable Auto-Recharge', 'bookly' ) . '…', $sms->autoRechargeEnabled() ? array( 'disabled' => 'disabled' ) : array() ) ?>
    <?php Buttons::renderDefault( 'bookly-auto-recharge-decline', null, __( 'Disable Auto-Recharge', 'bookly' ) . '…', $sms->autoRechargeEnabled() ? array() : array( 'disabled' => 'disabled' ) ) ?>
</div>