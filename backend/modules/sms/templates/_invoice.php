<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<form class="card bookly-collapse bookly-js-invoice mt-2 mb-3">
    <div class="card-header d-flex align-items-center" role="tab">
        <input name="invoice[send]" value="0" class="hidden" />
        <?php Inputs::renderCheckBox( '', 1, $invoice['send'], array( 'name' => 'invoice[send]' ) ) ?>
        <a href="#collapse_invoice" class="collapsed" role="button" data-toggle="collapse"><?php esc_html_e( 'Send invoice', 'bookly' ) ?></a>
    </div>
    <div id="collapse_invoice" class="collapse">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_name"><?php esc_html_e( 'Company name', 'bookly' ) ?>*</label>
                        <input name="invoice[company_name]" type="text" class="form-control" id="bookly_sms_invoice_company_name" required value="<?php echo esc_attr( $invoice['company_name'] ) ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle text-info mr-2"></i> <?php esc_html_e( 'Note: invoice will be sent to your PayPal email address', 'bookly' ) ?></i>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_address"><?php esc_html_e( 'Company address', 'bookly' ) ?>*</label>
                        <input name="invoice[company_address]" type="text" class="form-control" id="bookly_sms_invoice_company_address" required value="<?php echo esc_attr( $invoice['company_address'] ) ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mt-n2">
                        <?php Inputs::renderCheckBox( __( 'Copy invoice to another email(s)', 'bookly' ), 1, $invoice['send_copy'], array( 'name' => 'invoice[send_copy]' ) ) ?>
                        <input name="invoice[cc]" type="text" class="form-control" value="<?php echo esc_attr( $invoice['cc'] ) ?>">
                        <small class="form-text text-muted"><?php esc_html_e( 'Enter one or more email addresses separated by commas.', 'bookly' ) ?></small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_address_l2"><?php esc_html_e( 'Company address line 2', 'bookly' ) ?></label>
                        <input name="invoice[company_address_l2]" type="text" class="form-control" id="bookly_sms_invoice_company_address_l2" value="<?php echo esc_attr( $invoice['company_address_l2'] ) ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="bookly_sms_invoice_company_vat"><?php esc_html_e( 'VAT', 'bookly' ) ?></label>
                            <input name="invoice[company_vat]" type="text" class="form-control" id="bookly_sms_invoice_company_vat" value="<?php echo esc_attr( $invoice['company_vat'] ) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="bookly_sms_invoice_company_code"><?php esc_html_e( 'Company code', 'bookly' ) ?></label>
                            <input name="invoice[company_code]" type="text" class="form-control" id="bookly_sms_invoice_company_code" value="<?php echo esc_attr( $invoice['company_code'] ) ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bookly_sms_invoice_company_add_text"><?php esc_html_e( 'Additional text to include into invoice', 'bookly' ) ?></label>
                        <textarea name="invoice[company_add_text]" class="form-control" rows="4" style="height: 118px;" id="bookly_sms_invoice_company_add_text"><?php echo esc_textarea( $invoice['company_add_text'] ) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php Buttons::renderSubmit( null ) ?>
        </div>
    </div>
</form>